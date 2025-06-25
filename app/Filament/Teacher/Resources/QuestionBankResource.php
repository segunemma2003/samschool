<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\QuestionBankResource\Pages;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\QuestionBank;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use App\Traits\OptimizedTeacherLookup;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QuestionBankResource extends Resource
{
    use OptimizedTeacherLookup;

    protected static ?string $model = QuestionBank::class;
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $label = 'Questions';
    protected static ?string $navigationGroup = 'Exams';

    public static function form(Form $form): Form
    {
        try {
            // Cache the academic year and term lookups
            $academy = Cache::remember('current_academy', 300, function() {
                return AcademicYear::where('status', 'true')->first();
            });

            $term = Cache::remember('current_term', 300, function() {
                return Term::where('status', 'true')->first();
            });

            // Early return if no academy or term
            if (!$academy || !$term) {
                Log::warning('No active academic year or term found');
                return $form->schema([
                    Forms\Components\Placeholder::make('no_data')
                        ->content('No active academic year or term found. Please contact administrator.')
                ]);
            }

            // Get exams with better error handling
            $exams = Cache::remember("exams_term_{$term->id}_academy_{$academy->id}", 300, function() use ($term, $academy) {
                return Exam::with(['subject:id,code', 'term:id,name', 'academic:id,title'])
                    ->where('term_id', $term->id)
                    ->where('academic_year_id', $academy->id)
                    ->get()
                    ->mapWithKeys(function ($exam) {
                        $subjectCode = $exam->subject?->code ?? 'Unknown Subject';
                        $assessmentType = $exam->assessment_type ?? 'Unknown';
                        $termName = $exam->term?->name ?? 'Unknown Term';
                        $academyTitle = $exam->academic?->title ?? 'Unknown Year';

                        return [$exam->id => "{$subjectCode} - {$assessmentType} - ({$termName}) - {$academyTitle}"];
                    })
                    ->toArray();
            });

            return $form->schema([
                Forms\Components\Select::make('exam_id')
                    ->label('Select Exam')
                    ->options($exams)
                    ->preload()
                    ->searchable()
                    ->required(),

                Forms\Components\Repeater::make('questions')
                    ->schema([
                        Forms\Components\Textarea::make('question')
                            ->required()
                            ->label('Question')
                            ->rows(3)
                            ->columnSpan('full'),

                        Forms\Components\Select::make('question_type')
                            ->options([
                                'multiple_choice' => 'Multiple Choice',
                                'true_false' => 'True/False',
                                'open_ended' => 'Open-Ended',
                            ])
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                if ($get('question_type') === 'multiple_choice') {
                                    $set('options', [['option' => '', 'is_correct' => false]]);
                                } elseif ($get('question_type') === 'true_false') {
                                    $set('options', [
                                        ['option' => 'True', 'is_correct' => false],
                                        ['option' => 'False', 'is_correct' => false],
                                    ]);
                                } else {
                                    $set('options', []);
                                }
                            }),

                        Forms\Components\Repeater::make('options')
                            ->label('Options')
                            ->schema([
                                Forms\Components\TextInput::make('option')
                                    ->label('Option')
                                    ->required(),

                                FileUpload::make('image')
                                    ->label('Option Image')
                                    ->disk('s3')
                                    ->nullable()
                                    ->image()
                                    ->directory('exam_images'),

                                Forms\Components\Checkbox::make('is_correct')
                                    ->label('Correct Answer')
                                    ->fixIndistinctState(),
                            ])
                            ->hidden(fn (callable $get) => $get('question_type') === 'open_ended')
                            ->columnSpan('full')
                            ->minItems(fn (callable $get) => $get('question_type') === 'true_false' ? 2 : 1)
                            ->maxItems(10)
                            ->collapsible(),

                        Forms\Components\Textarea::make('answer')
                            ->label('Answer')
                            ->required()
                            ->hidden(fn (callable $get) => $get('question_type') !== 'open_ended'),

                        Forms\Components\TextInput::make('mark')
                            ->numeric()
                            ->default(1)
                            ->label('Mark')
                            ->columnSpan('full'),

                        Forms\Components\Textarea::make('hint')
                            ->nullable()
                            ->label('Hint')
                            ->columnSpan('full'),

                        FileUpload::make('image')
                            ->label('Question Image')
                            ->disk('s3')
                            ->nullable()
                            ->image()
                            ->directory('exam_images')
                            ->columnSpan('full'),
                    ])
                    ->columnSpanFull()
                    ->minItems(1)
                    ->collapsible()
                    ->maxItems(100)
                    ->required(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in QuestionBankResource form: ' . $e->getMessage());

            return $form->schema([
                Forms\Components\Placeholder::make('error')
                    ->content('An error occurred while loading the form. Please try again.')
            ]);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                try {
                    $teacher = static::getCurrentTeacher();

                    if ($teacher) {
                        $query->with([
                            'exam:id,subject_id,assessment_type,term_id,academic_year_id',
                            'exam.subject:id,code,class_id,teacher_id',
                            'exam.subject.class:id,name',
                            'exam.subject.teacher:id,name'
                        ])
                        ->whereHas('exam.subject', function (Builder $subQuery) use ($teacher) {
                            $subQuery->where('teacher_id', $teacher->id);
                        });
                    } else {
                        // If no teacher found, return empty result
                        $query->whereRaw('0 = 1');
                    }
                } catch (\Exception $e) {
                    Log::error('Error in QuestionBankResource table query: ' . $e->getMessage());
                    // Return empty result on error
                    $query->whereRaw('0 = 1');
                }
            })
            ->columns([
                TextColumn::make('exam.subject.code')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('exam.subject.class.name')
                    ->label('Class')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('exam.subject.teacher.name')
                    ->label('Teacher')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('exam.assessment_type')
                    ->label('Assessment Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('question_type')
                    ->label('Question Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('marks')
                    ->label('Marks')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Textarea::make('question')
                            ->required()
                            ->label('Question')
                            ->rows(3)
                            ->columnSpan('full'),

                        Forms\Components\Select::make('question_type')
                            ->options([
                                'multiple_choice' => 'Multiple Choice',
                                'true_false' => 'True/False',
                                'open_ended' => 'Open-Ended',
                            ])
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $currentOptions = $get('options');

                                // Ensure we're working with an array
                                $existingOptions = is_string($currentOptions)
                                    ? json_decode($currentOptions, true)
                                    : ($currentOptions ?? []);

                                if ($get('question_type') === 'multiple_choice') {
                                    $set('options', $existingOptions ?: [['option' => '', 'is_correct' => false]]);
                                } elseif ($get('question_type') === 'true_false') {
                                    $set('options', [
                                        ['option' => 'True', 'is_correct' => false],
                                        ['option' => 'False', 'is_correct' => false],
                                    ]);
                                } else {
                                    $set('options', []);
                                }
                            }),

                        Forms\Components\Repeater::make('options')
                            ->label('Options')
                            ->schema([
                                Forms\Components\TextInput::make('option')
                                    ->label('Option')
                                    ->required(),

                                FileUpload::make('image')
                                    ->label('Option Image')
                                    ->disk('s3')
                                    ->nullable()
                                    ->image()
                                    ->directory('exam_images'),

                                Forms\Components\Checkbox::make('is_correct')
                                    ->label('Correct Answer')
                                    ->fixIndistinctState(),
                            ])
                            ->hidden(fn (callable $get) => $get('question_type') === 'open_ended')
                            ->columnSpan('full')
                            ->defaultItems(1)
                            ->maxItems(10)
                            ->collapsible()
                            ->formatStateUsing(function ($state) {
                                if (is_string($state)) {
                                    return json_decode($state, true) ?? [];
                                }
                                return $state ?? [];
                            }),

                        Forms\Components\Textarea::make('answer')
                            ->label('Answer')
                            ->required()
                            ->hidden(fn (callable $get) => $get('question_type') !== 'open_ended'),

                        Forms\Components\TextInput::make('mark')
                            ->numeric()
                            ->default(1)
                            ->label('Mark')
                            ->columnSpan('full'),

                        Forms\Components\Textarea::make('hint')
                            ->nullable()
                            ->label('Hint')
                            ->columnSpan('full'),

                        FileUpload::make('image')
                            ->label('Question Image')
                            ->disk('s3')
                            ->nullable()
                            ->image()
                            ->directory('exam_images')
                            ->columnSpan('full'),
                    ])
                    ->mutateRecordDataUsing(function (array $data) {
                        if (isset($data['options'])) {
                            // If it's a JSON string, decode it
                            if (is_string($data['options'])) {
                                $data['options'] = json_decode($data['options'], true) ?? [];
                            }

                            // Automatically set answer based on correct option
                            if (in_array($data['question_type'], ['multiple_choice', 'true_false'])) {
                                $answer = null;
                                foreach ($data['options'] as $option) {
                                    if (!empty($option['is_correct']) && $option['is_correct'] === true) {
                                        $answer = $option['option'];
                                        break;
                                    }
                                }

                                // If we found a correct answer, update it
                                if (!empty($answer)) {
                                    $data['answer'] = $answer;
                                }
                            }
                        } else {
                            $data['options'] = [];
                        }

                        return $data;
                    })
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
            'index' => Pages\ListQuestionBanks::route('/'),
            'create' => Pages\CreateQuestionBank::route('/create'),
            // 'edit' => Pages\EditQuestionBank::route('/{record}/edit'),
        ];
    }
}
