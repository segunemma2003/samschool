<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\QuestionBankResource\Pages;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\QuestionBank;
use App\Models\Teacher;
use App\Models\Term;
use App\Traits\OptimizedTeacherLookup;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;

class QuestionBankResource extends Resource
{
    use OptimizedTeacherLookup;

    protected static ?string $model = QuestionBank::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $label = 'Question Bank';
    protected static ?string $pluralLabel = 'Question Bank';
    protected static ?string $navigationGroup = 'Exam Management';
    protected static ?int $navigationSort = 2;

    // Cache duration in seconds
    private const CACHE_DURATION = 600; // 10 minutes

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Exam Selection')
                ->description('Select the exam for your questions')
                ->icon('heroicon-m-clipboard-document-list')
                ->schema([
                    self::getExamSelectField()
                ])
                ->columnSpan(['lg' => 1]),

            Section::make('Questions')
                ->description('Add your exam questions here')
                ->icon('heroicon-m-question-mark-circle')
                ->schema([
                    self::getQuestionsRepeater()
                ])
                ->columnSpan(['lg' => 2]),
        ])
        ->columns(['lg' => 3]);
    }

    private static function getExamSelectField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('exam_id')
            ->label('Select Exam')
            ->options(function () {
                try {
                    return self::getCachedExamOptions();
                } catch (\Exception $e) {
                    Log::error('Error loading exam options: ' . $e->getMessage());
                    return [];
                }
            })
            ->searchable()
            ->preload()
            ->required()
            ->live()
            ->placeholder('Choose an exam...')
            ->helperText('Select the exam you want to create questions for')
            ->suffixIcon('heroicon-m-clipboard-document-list');
    }

    private static function getCachedExamOptions(): array
    {
        $cacheKey = 'teacher_exam_options_' . auth()->id();

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () {
            $teacher = self::getCurrentTeacher();

            if (!$teacher) {
                return [];
            }

            // Get current academic year and term efficiently
            $currentData = self::getCurrentAcademicData();

            if (!$currentData['academy'] || !$currentData['term']) {
                return [];
            }

            return Exam::query()
                ->select(['id', 'subject_id', 'assessment_type', 'term_id', 'academic_year_id'])
                ->with([
                    'subject:id,code,teacher_id',
                    'term:id,name',
                    'academic:id,title'
                ])
                ->whereHas('subject', function (Builder $query) use ($teacher) {
                    $query->where('teacher_id', $teacher->id);
                })
                ->where('term_id', $currentData['term']->id)
                ->where('academic_year_id', $currentData['academy']->id)
                ->get()
                ->mapWithKeys(function ($exam) {
                    $label = sprintf(
                        '%s - %s (%s)',
                        $exam->subject?->code ?? 'Unknown',
                        $exam->assessment_type ?? 'Unknown',
                        $exam->term?->name ?? 'Unknown'
                    );

                    return [$exam->id => $label];
                })
                ->toArray();
        });
    }

    private static function getCurrentAcademicData(): array
    {
        return Cache::remember('current_academic_data', self::CACHE_DURATION, function () {
            return [
                'academy' => AcademicYear::where('status', 'true')->first(),
                'term' => Term::where('status', 'true')->first(),
            ];
        });
    }

    private static function getQuestionsRepeater(): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('questions')
            ->schema([
                Tabs::make('Question Details')
                    ->tabs([
                        Tabs\Tab::make('Basic Info')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                                Grid::make(2)->schema([
                                    Forms\Components\Textarea::make('question')
                                        ->label('Question Text')
                                        ->required()
                                        ->rows(3)
                                        ->placeholder('Enter your question here...')
                                        ->columnSpan(2),

                                    Forms\Components\Select::make('question_type')
                                        ->label('Question Type')
                                        ->options([
                                            'multiple_choice' => 'Multiple Choice',
                                            'true_false' => 'True/False',
                                            'open_ended' => 'Open-Ended',
                                        ])
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (callable $get, callable $set) {
                                            self::handleQuestionTypeChange($get, $set);
                                        })
                                        ->suffixIcon('heroicon-m-list-bullet'),

                                    Forms\Components\TextInput::make('mark')
                                        ->label('Points')
                                        ->numeric()
                                        ->default(1)

                                        ->suffixIcon('heroicon-m-star'),
                                ]),
                            ]),

                        Tabs\Tab::make('Options & Answers')
                            ->icon('heroicon-m-check-circle')
                            ->schema([
                                self::getOptionsRepeater(),
                                self::getOpenEndedAnswer(),
                            ]),

                        Tabs\Tab::make('Additional')
                            ->icon('heroicon-m-plus-circle')
                            ->schema([
                                Forms\Components\Textarea::make('hint')
                                    ->label('Hint (Optional)')
                                    ->placeholder('Provide a helpful hint for students...')
                                    ->rows(2),

                                FileUpload::make('image')
                                    ->label('Question Image (Optional)')
                                    ->disk('s3')
                                    ->directory('exam_images')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->maxSize(5120) // 5MB
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                                    ->helperText('Upload an image to accompany your question (max 5MB)'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->itemLabel(fn (array $state): ?string =>
                !empty($state['question'])
                    ? substr(strip_tags($state['question']), 0, 50) . '...'
                    : 'New Question'
            )
            ->addActionLabel('Add Question')
            ->reorderableWithButtons()
            ->collapsible()
            ->defaultItems(1)
            ->minItems(1)
            ->maxItems(50) // Reasonable limit
            ->grid(1);
    }

    private static function getOptionsRepeater(): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('options')
            ->label('Answer Options')
            ->schema([
                Grid::make(3)->schema([
                    Forms\Components\TextInput::make('option')
                        ->label('Option Text')
                        ->required()
                        ->placeholder('Enter option text...')
                        ->columnSpan(2),

                    Forms\Components\Toggle::make('is_correct')
                        ->label('Correct Answer')
                        ->inline(false)
                        ->columnSpan(1),
                ]),

                FileUpload::make('image')
                    ->label('Option Image (Optional)')
                    ->disk('s3')
                    ->directory('exam_images')
                    ->image()
                    ->maxSize(2048) // 2MB for options
                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                    ->columnSpanFull(),
            ])
            ->itemLabel(fn (array $state): ?string =>
                !empty($state['option'])
                    ? $state['option'] . ($state['is_correct'] ?? false ? ' ✓' : '')
                    : 'New Option'
            )
            ->addActionLabel('Add Option')
            ->hidden(fn (callable $get) => $get('question_type') === 'open_ended')
            ->minItems(fn (callable $get) => $get('question_type') === 'true_false' ? 2 : 1)
            ->maxItems(6) // Reasonable limit for multiple choice
            ->defaultItems(fn (callable $get) => $get('question_type') === 'true_false' ? 2 : 1)
            ->reorderableWithButtons()
            ->grid(1);
    }

    private static function getOpenEndedAnswer(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('answer')
            ->label('Model Answer')
            ->required()
            ->rows(4)
            ->placeholder('Provide the expected answer or key points...')
            ->hidden(fn (callable $get) => $get('question_type') !== 'open_ended')
            ->helperText('This will help in grading the open-ended responses');
    }

    private static function handleQuestionTypeChange(callable $get, callable $set): void
    {
        $questionType = $get('question_type');

        switch ($questionType) {
            case 'multiple_choice':
                $set('options', [['option' => '', 'is_correct' => false]]);
                $set('answer', null);
                break;

            case 'true_false':
                $set('options', [
                    ['option' => 'True', 'is_correct' => false],
                    ['option' => 'False', 'is_correct' => false],
                ]);
                $set('answer', null);
                break;

            case 'open_ended':
                $set('options', []);
                break;
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                try {
                    $teacher = self::getCurrentTeacher();

                    if (!$teacher) {
                        return $query->whereRaw('0 = 1');
                    }

                    return $query->with([
                            'exam:id,subject_id,assessment_type,term_id,academic_year_id',
                            'exam.subject:id,code,class_id,teacher_id',
                            'exam.subject.class:id,name',
                            'exam.term:id,name',
                            'exam.academic:id,title'
                        ])
                        ->whereHas('exam.subject', function (Builder $subQuery) use ($teacher) {
                            $subQuery->where('teacher_id', $teacher->id);
                        })
                        ->orderBy('created_at', 'desc');

                } catch (\Exception $e) {
                    Log::error('Error in QuestionBankResource table query: ' . $e->getMessage());
                    return $query->whereRaw('0 = 1');
                }
            })
            ->columns([
                TextColumn::make('question')
                    ->label('Question')
                    ->limit(60)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 60 ? $state : null;
                    })
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('question_type')
                    ->label('Type')
                    ->colors([
                        'success' => 'multiple_choice',
                        'warning' => 'true_false',
                        'primary' => 'open_ended',
                    ])
                    ->icons([
                        'heroicon-m-list-bullet' => 'multiple_choice',
                        'heroicon-m-check-circle' => 'true_false',
                        'heroicon-m-document-text' => 'open_ended',
                    ])
                    ->formatStateUsing(fn (string $state): string =>
                        match ($state) {
                            'multiple_choice' => 'Multiple Choice',
                            'true_false' => 'True/False',
                            'open_ended' => 'Open Ended',
                            default => $state,
                        }
                    ),

                TextColumn::make('exam.subject.code')
                    ->label('Subject')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('exam.assessment_type')
                    ->label('Assessment')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('marks')
                    ->label('Points')
                    ->numeric()
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->suffix(' pts'),

                TextColumn::make('exam.term.name')
                    ->label('Term')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('question_type')
                    ->label('Question Type')
                    ->options([
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True/False',
                        'open_ended' => 'Open Ended',
                    ])
                    ->multiple(),

                SelectFilter::make('exam')
                    ->label('Exam')
                    ->relationship('exam', 'assessment_type')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton(),
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->form(self::getEditForm()),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    private static function getEditForm(): array
    {
        return [
            Section::make('Edit Question')
                ->schema([
                    Forms\Components\Textarea::make('question')
                        ->label('Question Text')
                        ->required()
                        ->rows(3),

                    Grid::make(2)->schema([
                        Forms\Components\Select::make('question_type')
                            ->label('Question Type')
                            ->options([
                                'multiple_choice' => 'Multiple Choice',
                                'true_false' => 'True/False',
                                'open_ended' => 'Open-Ended',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                self::handleQuestionTypeChange($get, $set);
                            }),

                        Forms\Components\TextInput::make('mark')
                            ->label('Points')
                            ->numeric()
                            ->default(1)

                    ]),

                    self::getOptionsRepeater(),
                    self::getOpenEndedAnswer(),

                    Forms\Components\Textarea::make('hint')
                        ->label('Hint (Optional)')
                        ->rows(2),

                    FileUpload::make('image')
                        ->label('Question Image (Optional)')
                        ->disk('s3')
                        ->directory('exam_images')
                        ->image()
                        ->maxSize(5120),
                ]),
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestionBanks::route('/'),
            'create' => Pages\CreateQuestionBank::route('/create'),
            'view' => Pages\ViewQuestionBank::route('/{record}'),
            'edit' => Pages\EditQuestionBank::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $teacher = self::getCurrentTeacher();
            if (!$teacher) {
                return null;
            }

            $count = Cache::remember(
                "teacher_questions_count_{$teacher->id}",
                300, // 5 minutes
                function () use ($teacher) {
                    return static::getModel()::query()
                        ->whereHas('exam.subject', function (Builder $query) use ($teacher) {
                            $query->where('teacher_id', $teacher->id);
                        })
                        ->count();
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
                'exam:id,subject_id,assessment_type,term_id,academic_year_id',
                'exam.subject:id,code,teacher_id',
                'exam.term:id,name',
                'exam.academic:id,title'
            ]);
    }
}
