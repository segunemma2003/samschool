<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\ExamResource\Pages;
use App\Filament\Ourstudent\Resources\ExamResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

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
    // dd($student);
        return $table
        ->recordUrl(null)
        ->recordAction(null)
        ->modifyQueryUsing(function (Builder $query) use ($student, $academicYearId) {
            if ($student && $academicYearId) {
                $query->whereHas('subject.courseOffer', function ($subQuery) use ($student, $academicYearId) {
                    $subQuery->where('student_id', $student->id)
                             ->where('academic_year_id', $academicYearId);
                })->with(['subject', 'term']);
                // , 'examScore' => function ($query) use ($student) {
                //     $query->where('student_id', $student->id); // Filter by the current student
                // }]
            // );
            }
        })
            ->columns([
                TextColumn::make('academic.title')->searchable(),
                TextColumn::make('term.name')->searchable()->default('Term 1'),
                TextColumn::make('subject.code')->searchable(),
                TextColumn::make('subject.class.name')->searchable(),
                TextColumn::make('subject.class.name')->searchable(),
                // TextColumn::make('is_set')->searchable(),
                TextColumn::make('score') // Display the exam score
                ->label('Exam Score')
                ->formatStateUsing(function ($record) use ($student) {
                    // Retrieve the student's examScore
                    $examScore = $record->examScore($student->id)->first();

                    // Determine the score state
                    if (!$examScore) {
                        return 'Not Submitted'; // No examScore exists
                    }

                    if ($examScore->approved !== 'yes') {
                        return 'Not Graded'; // Submitted but not yet graded
                    }

                    return $examScore->total_score ?? 'No Score'; // Display the total score
                }),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                ->visible(function ($record) use ($student) {
                    // Ensure $student is not null
                    // if (!$student) {
                    //     return false; // Return false to hide the action
                    // }

                    // Check conditions
                    return  (!$record->examScore($student->id)->exists())

                        && ($record->is_set == "yes" || $record->is_set == true);
                }),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
            'view' => Pages\TakeExam::route('/{record}')
        ];
    }
}
