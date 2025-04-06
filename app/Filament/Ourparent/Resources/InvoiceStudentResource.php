<?php

namespace App\Filament\Ourparent\Resources;

use App\Filament\Ourparent\Resources\InvoiceStudentResource\Pages;
use App\Filament\Ourparent\Resources\InvoiceStudentResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Guardians;
use App\Models\InvoiceStudent;
use App\Models\SchoolInformation;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Unicodeveloper\Paystack\Facades\Paystack;

class InvoiceStudentResource extends Resource
{
    protected static ?string $model = InvoiceStudent::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        // ->modifyQueryUsing(function (Builder $query) {
        //     $userId = Auth::user()->email;
        //     $guardian = Guardians::whereEmail($userId)->first();
        //     // dd($guardian);
        //     $query->whereHas('student.parent', function ($query) use ($guardian) {
        //         return $query->where('id', $guardian->id);
        //     })->with('student.parent');

        // })
        ->modifyQueryUsing(function (Builder $query) {
            $user = Auth::user();

            if (!$user) {
                return $query->whereRaw('0 = 1'); // No user: return no data
            }

            $guardian = Guardians::whereEmail($user->email)->first();

            if (!$guardian) {
                return $query->whereRaw('0 = 1'); // No matching guardian: return no data
            }

            return $query
                ->whereHas('student.parent', function ($subQuery) use ($guardian) {
                    $subQuery->where('id', $guardian->id);
                })
                ->with('student.parent');
        })->emptyStateHeading('No invoice yet')
        ->emptyStateDescription('You currently have no invoices available.')
            ->columns([
                TextColumn::make('order_code')->searchable()->sortable(),
                TextColumn::make('term.name')->searchable()->sortable(),
                TextColumn::make('academy.title')->searchable()->sortable(),
                TextColumn::make('student.name')->searchable()->sortable(),
                IconColumn::make('status')
                ->icon(fn (string $state): string => match ($state) {
                    'owing' => 'heroicon-s-exclamation-circle', // Warning icon
                    'paid' => 'heroicon-s-check-circle',       // Success icon
                    default => 'heroicon-s-minus-circle',     // Default icon
                })
                ->color(fn (string $state): string => match ($state) {
                    'owing' => 'danger',
                    'paid' => 'success',
                    default => 'gray',
                }),
                TextColumn::make('total_amount')->prefix('₦')->searchable()->sortable() ->color('success')->formatStateUsing(fn ($state) => number_format(round($state, 2), 2)),
                TextColumn::make('amount_owed')->prefix('₦')->searchable()->sortable() ->color('danger')->formatStateUsing(fn ($state) => number_format(round($state, 2), 2)),
                TextColumn::make('amount_paid')->prefix('₦')->searchable()->sortable()->color('info')->formatStateUsing(fn ($state) => number_format(round($state, 2), 2)),

                Tables\Columns\TextColumn::make('updated_at')
                    // ->label(trans('filament-invoices::messages.invoices.columns.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([

                Tables\Actions\Action::make('pay')
                    // ->hidden(fn($record) => ($record->total === $record->paid) || $record->status === 'paid' || $record->status === 'estimate')
                    ->requiresConfirmation()
                    ->iconButton()
                    ->color('info')
                    ->fillForm(fn($record) => [
                        'total_amount' => $record->total_amount,
                        'amount_paid' => $record->amount_paid,
                        'amount_owed'=>$record->amount_owed,
                        'amount' => $record->total_amount - $record->amount_paid,
                    ])
                    ->form([
                        Forms\Components\TextInput::make('total_amount')
                            // ->label(trans('filament-invoices::messages.invoices.actions.total'))
                            ->numeric()
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                            ->disabled(),
                        Forms\Components\TextInput::make('amount_paid')
                            // ->label(trans('filament-invoices::messages.invoices.actions.paid'))
                            ->numeric()
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                            ->disabled(),
                        Forms\Components\TextInput::make('amount_owed')
                            // ->label(trans('filament-invoices::messages.invoices.actions.paid'))
                            ->numeric()
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ''))
                            ->disabled(),
                        Forms\Components\TextInput::make('amount')
                            // ->label(trans('filament-invoices::messages.invoices.actions.amount'))
                            ->required()
                            ->numeric(),
                    ])
                    ->action(function (array $data,  $record) {
                        // $record->update([
                        //     'amount_paid' => $record->amount_paid + $data['amount'],
                        //     'amount_owed'=> $record->amount_owed - $data['amount']
                        // ]);

                        // $record->invoicePaid()->create([
                        //     'transaction_code' => uniqid(),
                        //     'order_code'=>$record->order_code,
                        //     'amount' => $data['amount']
                        // ]);



                        if ($record->total_amount == $record->amount_paid) {
                            // $record->update([
                            //     'status' => 'paid'
                            // ]);
                        }

                        $school = SchoolInformation::where([
                            ['term_id', $record->term_id],
                            ['academic_id', $record->academic_id]
                        ])->first();
                        $term = Term::whereId($record->term_id)->first();
                        $academy = AcademicYear::whereId($record->academic_id)->first();
                        $data = [
                            'school'=>$school,
                            'record'=>$record,
                            'term'=> $term,
                            'academy'=>$academy,
                            'date'=>now(),
                            'amount'=>$data['amount']
                        ];


                    //     $data = array(
                    //         "amount" => floatval($data['amount']) * 100 ,
                    //         "reference" => uniqid(),
                    //         "email" => auth()->user()->email,
                    //         "currency" => "NGN",
                    //         "orderID" => $record->order_id,
                    //     );

                    // return Paystack::getAuthorizationUrl($data)->redirectNow();
                    return;
                        // $pdf = Pdf::loadView('template.receipt', $data);
                        // Notification::make()
                        // ->title("Invoice payment")
                        // ->body('Invoice generated')
                        // ->success()
                        // ->send();
                        // return response()->streamDownload(
                        //     fn () => print($pdf->output()),
                        //     "result-{$record->student->name}.pdf"
                        // );


                    })
                    ->icon('heroicon-s-credit-card')
                    ->label("Pay")
                    ->modalHeading("Make Payment"),
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoiceStudents::route('/'),
            'create' => Pages\CreateInvoiceStudent::route('/create'),
            'view' => Pages\ViewInvoiceStudent::route('/{record}'),
            'edit' => Pages\EditInvoiceStudent::route('/{record}/edit'),
        ];
    }
}
