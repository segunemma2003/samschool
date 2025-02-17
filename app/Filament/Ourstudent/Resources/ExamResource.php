<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\ExamResource\Pages;
use App\Filament\Ourstudent\Resources\ExamResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Term;
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

        $term = Term::whereStatus('true')->first();
        $termId = $term ? $term->id : null; // Ensure $termId is always defined
        // dd($termId);
        if (!$user) {
            return $table;
        }

        $student = Student::whereEmail($user->email)->first();

        return $table
            ->recordUrl(null)
            ->defaultSort('created_at', 'desc')
            ->deferLoading()
            ->striped()
            ->recordAction(null)
            ->modifyQueryUsing(function (Builder $query) use ($termId, $student, $academicYearId) {
                if ($student && $academicYearId && $termId) { // Ensure termId is not null
                    $query->whereHas('subject.courseOffer', function ($subQuery) use ($student, $academicYearId, $termId) {
                        $subQuery->where('student_id', $student->id)
                            ->where('academic_year_id', $academicYearId)
                            ->where('term_id', $termId);
                    })->with([
                        'subject',
                        'term',
                        'examScore' => function ($scoreQuery) use ($student) {
                            $scoreQuery->where('student_id', $student->id);
                        },
                    ]);
                }
            })
            ->columns([
                TextColumn::make('academic.title')->searchable(),
                TextColumn::make('term.name')->searchable()->default('Term 1'),
                TextColumn::make('subject.code')->searchable(),
                TextColumn::make('subject.class.name')->searchable(),
                TextColumn::make('subject.class.name')->searchable(),
                TextColumn::make('score')
                    ->label('Exam Score')
                    ->badge()
                    ->colors([
                        'gray' => fn($record) => !$record->examScore($student->id)->exists(),
                        'yellow' => fn($record) => optional($record->examScore($student->id)->first())->approved !== 'yes',
                        'green' => fn($record) => optional($record->examScore($student->id)->first())->approved === 'yes',
                    ])
                    ->getStateUsing(function ($record) use ($student) {
                        $examScore = $record->examScore($student->id)->first();

                        if (!$examScore) {
                            return 'Not Submitted';
                        }

                        if ($examScore->approved !== 'yes') {
                            return 'Not Graded';
                        }

                        return $examScore->total_score ?? 'No Score';
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(function ($record) use ($student) {
                        return (!$record->examScore($student->id)->exists())
                            && ($record->is_set == "yes" || $record->is_set == true);
                    }),
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
