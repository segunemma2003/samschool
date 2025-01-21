<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\InvoiceStudentResource\Pages;
use App\Filament\App\Resources\InvoiceStudentResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\InvoiceGroup;
use App\Models\InvoiceStudent;
use App\Models\SchoolInformation;
use App\Models\Student;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use TomatoPHP\FilamentTypes\Components\TypeColumn;

class InvoiceStudentResource extends Resource
{
    protected static ?string $model = InvoiceStudent::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Fieldset::make('Invoice Parent')
                ->schema([
                    Forms\Components\TextInput::make('order_code')
                    ->label('Order Number')
                    ->unique(ignoreRecord: true)
                        ->disabled(fn(InvoiceStudent $student) => $student->exists)
                        ->default(fn() => 'ORD-' . random_int(100000000, 999999999))
                        ->required()
                        ->columnSpanFull()
                        ->required(),
                    Select::make('term_id')
                    ->label('Term')
                    ->options(Term::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),

                    Select::make('academic_id')
                    ->label('Academy')
                    ->options(AcademicYear::all()->pluck('title', 'id'))
                    ->preload()
                    ->searchable(),

                    Select::make('student_id')
                    ->label('Student Name')
                    ->options(Student::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),

                    TextInput::make('total_amount')
                    ->numeric()
                    ->live()
                    ->prefix('₦')
                    ->required(),

                    RichEditor::make('note')
                    ->required(),

                ]),

                Fieldset::make('Invoice Details')
                ->schema([
                    Repeater::make('invoice_details')
                    ->relationship('invoice_details')
                ->schema([
                    Select::make('invoice_group_id')
                    ->label('Name')
                    ->options(InvoiceGroup::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),

                    TextInput::make('amount')
                    ->numeric()
                    ->prefix('₦')
                    ->live('blur')
                    ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="w-5 h-5" wire:loading wire-target="data.invoice_student_id"/>')))
                    ->required()
    ])->columnSpanFull()
    ->hiddenLabel()
    ->collapsible()
    ->collapsed(fn($record) => $record)
    ->cloneable()
    ->afterStateUpdated(function (callable $get, callable $set) {
        // Calculate the total dynamically
        $details = $get('invoice_details');
        $total = collect($details)->sum('amount'); // Sum the 'amount' field
        $set('total_amount', $total); // Update the total_amount field
    }),
                ])


            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
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
                        $record->update([
                            'amount_paid' => $record->amount_paid + $data['amount'],
                            'amount_owed'=> $record->amount_owed - $data['amount']
                        ]);

                        $record->invoicePaid()->create([
                            'transaction_code' => uniqid(),
                            'order_code'=>$record->order_code,
                            'amount' => $data['amount']
                        ]);



                        if ($record->total_amount == $record->amount_paid) {
                            $record->update([
                                'status' => 'paid'
                            ]);
                        }

                        Notification::make()
                            ->title("Invoice payment")
                            ->body('Invoice Paid')
                            ->success()
                            ->send();
                    })
                    ->icon('heroicon-s-credit-card')
                    ->label("Pay")
                    ->modalHeading("Make Payment"),

                    Tables\Actions\Action::make('downloadPdf')
                    ->icon('heroicon-m-envelope')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-m-envelope')
                    ->action(function (array $data, $record) {
                        Log::info($record->id);
                        $school = SchoolInformation::where([
                            ['term_id', $record->term_id],
                            ['academic_id', $record->academic_id]
                        ])->first();
                        $data = [
                            'school'=>$school,
                            'record'=>$record
                        ];

                        $pdf = Pdf::loadView('template.invoice', $data);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "result-{$record->student->name}.pdf"
                        );
                        // return $pdf->download($record->id. '.pdf');

                        Log::info("infor done");
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
