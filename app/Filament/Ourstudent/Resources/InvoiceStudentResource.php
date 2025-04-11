<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\InvoiceStudentResource\Pages;
use App\Filament\Ourstudent\Resources\InvoiceStudentResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\InvoiceStudent;
use App\Models\SchoolInformation;
use App\Models\Student;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceStudentResource extends Resource
{
    protected static ?string $model = InvoiceStudent::class;

    protected static ?string $label = "Student Invoice";

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
        $user = Auth::user();
        $academicYear = AcademicYear::whereStatus('true')->first();
        $academicYearId = $academicYear->id ?? null;
    // dd($user);
        if (!$user) {
            return $table;
        }

        $student = Student::whereEmail($user->email)->first();

        return $table
        ->modifyQueryUsing(function (Builder $query) use ($student) {
            if ($student) {
                $query->whereHas('student', function ($subQuery) use ($student) {
                    $subQuery->where('student_id', $student->id);
                })->with(['student']);
            }
        })
            ->columns([
                TextColumn::make('No')
                ->rowIndex(),
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

                        $term = Term::whereId($record->term_id)->first();
                        $academy = AcademicYear::whereId($record->academic_id)->first();
                        $data = [
                            'school'=>$school,
                            'record'=>$record,
                            'term'=>$term,
                            'academy'=>$academy,
                            'date'=> \Carbon\Carbon::now()
                        ];

                        $pdf = Pdf::loadView('template.invoice', $data);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "result-{$record->student->name}.pdf"
                        );
                        // return $pdf->download($record->id. '.pdf');

                        Log::info("infor done");
                    }),

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
