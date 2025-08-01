<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\ExamResource\Pages;
use App\Filament\Teacher\Resources\ExamResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ResultSectionType;
use App\Models\SchoolSection;
use App\Models\Subject;
use App\Models\Term;
use App\Models\QuizScore;
use App\Models\QuizSubmission;
use App\Models\Teacher;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list'; // More relevant icon for exams
    protected static ?string $navigationGroup = 'Academic Management';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Select::make('subject_id')
                ->options(Subject::all()->pluck('code', 'id'))
                ->preload()
                ->live()
                ->label("Subject")
                ->searchable(),
                DatePicker::make('exam_date')
                ->required(),
                TextInput::make('duration')
                ->label('Duration (minutes)')
                ->numeric()
                ->minValue(0)
                ->suffix('minutes')
                ->required(),
                Select::make('is_set')
                ->options([
                    true => 'Yes',    // Label "Yes" for true
                    false => 'No',    // Label "No" for false
                ])
                ->preload()
                ->default(false)
                ->label('Exam is Set')
                ->searchable(),
                Select::make('term_id')
                ->options(Term::all()->pluck('name', 'id'))
                ->preload()
                ->searchable()
                ->label("Term")
                ->required(),
                Select::make('assessment_type')
                ->options([
                    "test"=>"Test",
                    "exam"=>"Exam"
                ])
                ->preload()
                ->label("Assessment Type")
                ->searchable(),

                Select::make('result_section_type_id')
                ->options(function (callable $get) {
                    $subjectId = $get('subject_id');

                    if (!$subjectId) {
                        return [];
                    }

                    // Fetch the subject and its associated class group
                    $subject = Subject::with('class.group')->find($subjectId);

                    if (!$subject || !$subject->class || !$subject->class->group) {
                        return [];
                    }

                    // Fetch result section types matching the class group
                    return ResultSectionType::whereHas('resultSection.group', function ($query) use ($subject) {
                        $query->where('name', $subject->class->group->name);
                    })->pluck('code', 'id');
                })
                ->preload()
                ->label("Assessment Type Details")
                ->searchable()
                ->live()
                ->required(),

                TextInput::make('total_score')
                ->required()
                ->integer(),
                RichEditor::make('instructions')
                ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        $academy = cache()->remember('current_academy', 300, function() {
            return AcademicYear::whereStatus('true')->first();
        });

        $term = cache()->remember('current_term', 300, function() {
            return Term::whereStatus('true')->first();
        });

        $userId = Auth::id();
        $teacher = cache()->remember("teacher_for_user_{$userId}", 300, function() use ($userId) {
            $user = User::whereId($userId)->first();
            return Teacher::where("email", $user->email)->first();
        });

        return $table
            ->query(
                Exam::query()
                    ->with(['academic', 'term', 'subject.class', 'resultType'])
                    ->whereHas('subject', function($q) use ($teacher) {
                        $q->whereHas('teacher', function($subQ) use ($teacher) {
                            $subQ->where('id', $teacher->id);
                        });
                    })
            )
            ->columns([
                TextColumn::make('academic.title')->label('Academic Year')->searchable(),
                TextColumn::make('term.name')->label('Term')->searchable()->default('Term 1'),
                TextColumn::make('subject.code')->label('Subject Code')->searchable(),
                TextColumn::make('subject.class.name')->label('Class')->searchable(),
                TextColumn::make('resultType.name')->label('Assessment Detail'),
                Tables\Columns\BadgeColumn::make('assessment_type')
                    ->label('Assessment Type')
                    ->colors([
                        'success' => fn($state) => $state === 'exam',
                        'warning' => fn($state) => $state === 'test',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state)),
                TextColumn::make('exam_date')->label('Exam Date')->date(),
                TextColumn::make('duration')->label('Duration (min)'),
                TextColumn::make('total_score')->label('Total Score'),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(AcademicYear::pluck('title', 'id'))
                    ->default($academy?->id),
                SelectFilter::make('term_id')
                    ->label('Term')
                    ->options(Term::pluck('name', 'id'))
                    ->default($term?->id),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view-students')
                    ->label('View Students')
                    ->url(fn ($record) => static::getUrl('view-students', ['record' => $record->getKey()]))
                    ->icon('heroicon-o-user-group'),
                Tables\Actions\Action::make('regenerate_results')
                    ->label('Regenerate Results')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function ($record) {
                        $quizScores = QuizScore::where('exam_id', $record->id)->get();
                        $updatedCount = 0;
                        foreach ($quizScores as $quizScore) {
                            $scores = QuizSubmission::where('quiz_score_id', $quizScore->id)->get();
                            $totalScore = $scores->sum('score');
                            $quizScore->update(['total_score' => $totalScore]);
                            $updatedCount++;
                        }
                        Notification::make()
                            ->title('Success')
                            ->body("Updated {$updatedCount} student results")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Regenerate Results')
                    ->modalDescription('This will recalculate and update the total scores for all students who have attempted this exam.')
                    ->modalSubmitActionLabel('Yes, regenerate results')
                    ->modalCancelActionLabel('No, cancel')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->striped(); // Zebra striping for readability
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
            'view' => Pages\ViewExam::route('/{record}'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
            'view-students'=>  Pages\ViewExamStudent::route('/students/{record}'),
            'exam-student-details' => Pages\ExamStudentDetails::route('/exam-student-details/{quizScoreId}'),
        ];
    }
}
