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
        return $table
        ->recordUrl(null)
->recordAction(null)
        ->modifyQueryUsing(function (Builder $query) use ($student, $academicYearId) {
            if ($student && $academicYearId) {
                $query->whereHas('subject.courseOffer', function ($subQuery) use ($student, $academicYearId) {
                    $subQuery->where('student_id', $student->id)
                             ->where('academic_year_id', $academicYearId);
                })->with(['subject', 'term']);
            }
        })
            ->columns([
                TextColumn::make('academic.title')->searchable(),
                TextColumn::make('term.name')->searchable()->default('Term 1'),
                TextColumn::make('subject.code')->searchable(),
                TextColumn::make('subject.class.name')->searchable(),
                TextColumn::make('subject.class.name')->searchable(),
                TextColumn::make('score') // You can display specific attributes of examScore
                ->label('Exam Score') // Label the column
                ->formatStateUsing(function ($record) use ($student) {
                    // Pass student ID to the examScore method
                    $examScore = $record->examScore($student->id); // Call the relationship with the student ID

                    // Check if there's an examScore relationship
                    if (!$examScore) {
                        return 'Not Submitted'; // If no exam score exists
                    }

                    // If the examScore exists but is not graded
                    if ($examScore->approved !== 'yes') {
                        return 'Not Graded'; // If it's done but not graded yet
                    }

                    // If the examScore exists and is graded, return the total score
                    return $examScore->total_score ?? 'No Score'; // Return the total score or a fallback
                }),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                ->visible(function ($record) use ($student) {
                    return !$record->examScore($student->id)->exists();
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
