<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ExamResource\Pages;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ResultSectionType;
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
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid as InfoGrid;
use Filament\Support\Enums\FontWeight;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $label = 'Exam';
    protected static ?string $pluralLabel = 'Exams (All Teachers)';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Exam Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('subject_id')
                                    ->label('Subject')
                                    ->options(function () {
                                        return Subject::with(['subjectDepot', 'teacher', 'class'])
                                            ->get()
                                            ->mapWithKeys(function ($subject) {
                                                $label = sprintf(
                                                    '%s - %s (Teacher: %s)',
                                                    $subject->subjectDepot->name ?? $subject->code,
                                                    $subject->class->name ?? 'Unknown Class',
                                                    $subject->teacher->name ?? 'No Teacher'
                                                );
                                                return [$subject->id => $label];
                                            });
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required(),

                                Select::make('term_id')
                                    ->label('Term')
                                    ->options(Term::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                DatePicker::make('exam_date')
                                    ->label('Exam Date')
                                    ->required(),

                                TextInput::make('duration')
                                    ->label('Duration (minutes)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->suffix('minutes')
                                    ->required(),

                                Select::make('assessment_type')
                                    ->label('Assessment Type')
                                    ->options([
                                        'test' => 'Test',
                                        'exam' => 'Exam'
                                    ])
                                    ->required()
                                    ->searchable(),

                                Select::make('result_section_type_id')
                                    ->label('Assessment Type Details')
                                    ->options(function (callable $get) {
                                        $subjectId = $get('subject_id');

                                        if (!$subjectId) {
                                            return [];
                                        }

                                        $subject = Subject::with('class.group')->find($subjectId);

                                        if (!$subject || !$subject->class || !$subject->class->group) {
                                            return [];
                                        }

                                        return ResultSectionType::whereHas('resultSection.group', function ($query) use ($subject) {
                                            $query->where('name', $subject->class->group->name);
                                        })->pluck('code', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required(),

                                TextInput::make('total_score')
                                    ->label('Total Score')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),

                                Select::make('is_set')
                                    ->label('Exam is Set')
                                    ->options([
                                        true => 'Yes',
                                        false => 'No',
                                    ])
                                    ->default(false)
                                    ->required(),
                            ]),

                        RichEditor::make('instructions')
                            ->label('Instructions')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'undo',
                                'redo',
                            ]),
                    ]),
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

        return $table
            ->query(
                // Admin sees ALL exams without teacher filtering
                Exam::query()
                    ->with([
                        'academic',
                        'term',
                        'subject.class',
                        'subject.teacher',
                        'subject.subjectDepot',
                        'resultType'
                    ])
            )
            ->columns([
                TextColumn::make('subject.teacher.name')
                    ->label('Teacher')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('subject.subjectDepot.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('subject.code')
                    ->label('Subject Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject.class.name')
                    ->label('Class')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('assessment_type')
                    ->label('Assessment Type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('exam_date')
                    ->label('Date')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->suffix(' mins')
                    ->sortable(),

                TextColumn::make('total_score')
                    ->label('Total Score')
                    ->sortable(),

                TextColumn::make('is_set')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Set' : 'Not Set'),

                TextColumn::make('academic.title')
                    ->label('Academic Year')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('term.name')
                    ->label('Term')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('resultType.name')
                    ->label('Result Type')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Add submission count column for admin overview
                TextColumn::make('submissions_count')
                    ->label('Submissions')
                    ->getStateUsing(fn ($record) => QuizScore::where('exam_id', $record->id)->count())
                    ->badge()
                    ->color('warning'),
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

                // Admin can filter by any teacher
                SelectFilter::make('teacher')
                    ->label('Teacher')
                    ->options(function () {
                        return Teacher::pluck('name', 'id');
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $teacherId): Builder => $query->whereHas('subject.teacher', function ($q) use ($teacherId) {
                                $q->where('id', $teacherId);
                            }),
                        );
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('subject')
                    ->label('Subject')
                    ->relationship('subject.subjectDepot', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('assessment_type')
                    ->label('Assessment Type')
                    ->options([
                        'test' => 'Test',
                        'exam' => 'Exam'
                    ]),

                SelectFilter::make('is_set')
                    ->label('Status')
                    ->options([
                        true => 'Set',
                        false => 'Not Set'
                    ]),

                // Admin-specific filter for exam submissions
                SelectFilter::make('has_submissions')
                    ->label('Has Submissions')
                    ->options([
                        'yes' => 'Has Submissions',
                        'no' => 'No Submissions'
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] === 'yes',
                            fn (Builder $query): Builder => $query->whereHas('quizScores'),
                            fn (Builder $query): Builder => $query->when(
                                $data['value'] === 'no',
                                fn (Builder $query): Builder => $query->whereDoesntHave('quizScores')
                            )
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton(),

                Tables\Actions\EditAction::make()
                    ->iconButton(),

                Tables\Actions\Action::make('view-students')
                    ->label('View Students')
                    ->icon('heroicon-o-user-group')
                    ->color('info')
                    ->url(fn ($record) => static::getUrl('view-students', ['record' => $record->getKey()]))
                    ->iconButton(),

                // Admin can view exam questions
                Tables\Actions\Action::make('view-questions')
                    ->label('View Questions')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('warning')
                    ->url(fn ($record) => static::getUrl('view-exam-questions', ['record' => $record->getKey()]))
                    ->iconButton(),

                Tables\Actions\Action::make('regenerate_results')
                    ->label('Regenerate Results')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function ($record) {
                        // Get all students who have attempted the exam
                        $quizScores = QuizScore::where('exam_id', $record->id)->get();

                        $updatedCount = 0;

                        foreach ($quizScores as $quizScore) {
                            // Get all scores for this student and exam
                            $scores = QuizSubmission::where('quiz_score_id', $quizScore->id)
                                ->get();

                            // Calculate total score
                            $totalScore = $scores->sum('score');

                            // Update the total score
                            $quizScore->update([
                                'total_score' => $totalScore
                            ]);

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
                    ->iconButton(),

                // Admin can bulk approve/disapprove submissions
                Tables\Actions\Action::make('bulk_approve_submissions')
                    ->label('Bulk Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($record) {
                        $updated = QuizScore::where('exam_id', $record->id)
                            ->where('approved', 'no')
                            ->update(['approved' => 'yes']);

                        Notification::make()
                            ->title('Success')
                            ->body("Approved {$updated} submissions")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->iconButton(),

                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    // Admin bulk actions
                    Tables\Actions\BulkAction::make('bulk_set_status')
                        ->label('Set as Complete')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $updated = $records->count();
                            foreach ($records as $record) {
                                $record->update(['is_set' => true]);
                            }

                            Notification::make()
                                ->title('Success')
                                ->body("Updated {$updated} exams")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('bulk_approve_all_submissions')
                        ->label('Approve All Submissions')
                        ->icon('heroicon-o-check-badge')
                        ->color('warning')
                        ->action(function ($records) {
                            $totalUpdated = 0;
                            foreach ($records as $record) {
                                $updated = QuizScore::where('exam_id', $record->id)
                                    ->where('approved', 'no')
                                    ->update(['approved' => 'yes']);
                                $totalUpdated += $updated;
                            }

                            Notification::make()
                                ->title('Success')
                                ->body("Approved {$totalUpdated} submissions across all selected exams")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make('Exam Information')
                    ->schema([
                        InfoGrid::make(2)
                            ->schema([
                                TextEntry::make('subject.teacher.name')
                                    ->label('Teacher')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('subject.subjectDepot.name')
                                    ->label('Subject')
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('subject.code')
                                    ->label('Subject Code'),

                                TextEntry::make('subject.class.name')
                                    ->label('Class')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('assessment_type')
                                    ->label('Assessment Type')
                                    ->badge()
                                    ->color('gray')
                                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                                TextEntry::make('exam_date')
                                    ->label('Exam Date')
                                    ->date('M j, Y'),

                                TextEntry::make('duration')
                                    ->label('Duration')
                                    ->suffix(' minutes'),

                                TextEntry::make('total_score')
                                    ->label('Total Score'),

                                TextEntry::make('is_set')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Set' : 'Not Set'),

                                TextEntry::make('term.name')
                                    ->label('Term')
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('academic.title')
                                    ->label('Academic Year')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('resultType.name')
                                    ->label('Result Type'),
                            ]),
                    ]),

                InfoSection::make('Instructions')
                    ->schema([
                        TextEntry::make('instructions')
                            ->label('Exam Instructions')
                            ->html()
                            ->columnSpanFull()
                            ->placeholder('No instructions provided'),
                    ])
                    ->visible(fn ($record) => !empty($record->instructions)),

                InfoSection::make('Statistics')
                    ->schema([
                        InfoGrid::make(4)
                            ->schema([
                                TextEntry::make('questions_count')
                                    ->label('Total Questions')
                                    ->getStateUsing(fn ($record) => $record->questions()->count())
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('submissions_count')
                                    ->label('Student Submissions')
                                    ->getStateUsing(fn ($record) => QuizScore::where('exam_id', $record->id)->count())
                                    ->badge()
                                    ->color('warning'),

                                TextEntry::make('approved_submissions')
                                    ->label('Approved Submissions')
                                    ->getStateUsing(fn ($record) => QuizScore::where('exam_id', $record->id)->where('approved', 'yes')->count())
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('average_score')
                                    ->label('Average Score')
                                    ->getStateUsing(function ($record) {
                                        $average = QuizScore::where('exam_id', $record->id)
                                            ->avg('total_score');
                                        return $average ? round($average, 2) . '%' : 'N/A';
                                    })
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ]),

                InfoSection::make('Timestamps')
                    ->schema([
                        InfoGrid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('M j, Y g:i A'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('M j, Y g:i A'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $academy = AcademicYear::whereStatus('true')->first();
        $data['academic_year_id'] = $academy->id ?? 1;

        return $data;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'view' => Pages\ViewExam::route('/{record}'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
            'view-students' => Pages\ViewExamStudents::route('/students/{record}'),
            'exam-student-details' => Pages\ExamStudentDetails::route('/exam-student-details/{quizScoreId}'),
            'view-exam-questions' => Pages\ViewExamQuestions::route('/questions/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $count = Cache::remember(
                "admin_exams_count_all",
                300, // 5 minutes
                function () {
                    return static::getModel()::count();
                }
            );

            return $count > 0 ? (string) $count : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'subject:id,name,code,teacher_id',
                'subject.teacher:id,name',
                'term:id,name',
                'academic:id,title'
            ])
            ->select([
                'id', 'subject_id', 'term_id', 'academic_year_id', 'assessment_type', 'exam_date', 'created_at', 'updated_at'
            ])
            ->latest('created_at');
    }
}
