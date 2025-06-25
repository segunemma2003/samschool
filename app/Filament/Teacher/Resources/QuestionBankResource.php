<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\QuestionBankResource\Pages;
use App\Filament\Teacher\Resources\QuestionBankResource\RelationManagers;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class QuestionBankResource extends Resource implements OptimizedTeacherLookup
{
     use OptimizedTeacherLookup;

    protected static ?string $model = QuestionBank::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $label = 'Questions';

    protected static ?string $navigationGroup = 'Exams';

    public static function form(Form $form): Form
{

    $academy = AcademicYear::whereStatus('true')->first();
    $term = Term::whereStatus('true')->first();
    $exams = Exam::where('term_id', $term->id)
                ->where('academic_year_id', $academy->id)
                ->get()
                ->mapWithKeys(function ($exam) {
                    return [$exam->id => "{$exam->subject->code} - {$exam->assessment_type} - ({$exam->term->name}) - {$exam->academic->title}"];
                });
                // ->pluck('subject.code', 'id');

            return  $form
            ->schema([
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
                                    ->label('Correct Answer')->fixIndistinctState(),
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

    // return $form
    //     ->schema([
    //         Forms\Components\Select::make('exam_id')
    //             ->label('Select Exam')
    //             ->options($exams)
    //             ->preload()
    //             ->searchable()
    //             ->required(),

    //         Forms\Components\Repeater::make('questions')
    //             ->schema([
    //                 Forms\Components\Textarea::make('question')
    //                     ->required()
    //                     ->label('Question')
    //                     ->rows(3)
    //                     ->columnSpan('full'),

    //                     Forms\Components\Select::make('question_type')
    //                     ->options([
    //                         'multiple_choice' => 'Multiple Choice',
    //                         'true_false' => 'True/False',
    //                         'open_ended' => 'Open-Ended',
    //                     ])
    //                     ->required()
    //                     ->reactive()
    //                     ->searchable()
    //                     ->afterStateUpdated(function (callable $get, callable $set) {
    //                         // Initialize options with A if multiple_choice type is selected
    //                         if ($get('type') === 'multiple_choice') {
    //                             // Set initial option with key A
    //                             $set('options', ['' => '']); // Correctly setting initial values
    //                         }else if($get('type') === 'true_false'){
    //                             $set('options', ['A' => 'True', 'B'=> 'False']);
    //                          } else {
    //                             $set('options', []); // Clear options if not multiple_choice
    //                         }
    //                     }),
    //                 // Use the KeyValue component for options
    //                 KeyValue::make('options')
    //                 ->keyLabel('Option Key')   // Label for the key
    //                 ->valueLabel('Option Text') // Label for the value
    //                 ->required()
    //                 ->reorderable()
    //                 ->hidden(fn (callable $get) => (($get('question_type') !== 'multiple_choice')&& ($get('question_type') !== 'true_false')))

    //                 ->columnSpan('full'), // Optional: to control column span


    //                 Forms\Components\Textarea::make('answer')
    //                     ->label('Answer')->required(),

    //                 Forms\Components\TextInput::make('mark')
    //                     ->numeric()
    //                     ->default(1)
    //                     ->label('Mark')
    //                     ->columnSpan('full'),

    //                 Forms\Components\Textarea::make('hint')
    //                     ->nullable()
    //                     ->label('Hint')
    //                     ->columnSpan('full'),

    //                 FileUpload::make('image')
    //                     ->label('Image')
    //                     ->disk('s3') // Specify Cloudinary as the storage disk
    //                     ->nullable()
    //                     ->image() // Restrict to image files
    //                     ->directory('exam_images') // Optional: specify a directory in Cloudinary
    //                     ->columnSpan('full'),
    //             ])
    //             ->columnSpanFull()
    //             ->minItems(1)
    //             ->collapsible()
    //             ->maxItems(100)
    //             ->required(),
    //     ]);
}

    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
                $teacher = static::getCurrentTeacher();

                if ($teacher) {
                    $query->with(['exam.subject.class', 'exam.subject.teacher'])
                          ->whereHas('exam.subject.teacher', function (Builder $subQuery) use ($teacher) {
                              $subQuery->where('id', $teacher->id);
                          });
                }
            })
            ->columns([
                TextColumn::make('exam.subject.code')
                ->label('Subject')
                ->searchable(),
                TextColumn::make('exam.subject.class.name')
                ->label('Class')
                ->searchable(),
                 TextColumn::make('exam.subject.teacher.name')
                ->label('Teacher')
                ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make()->form([
                //     Forms\Components\Textarea::make('question')
                //         ->required()
                //         ->label('Question')
                //         ->rows(3)
                //         ->columnSpan('full'),

                //         Forms\Components\Select::make('question_type')
                //         ->options([
                //             'multiple_choice' => 'Multiple Choice',
                //             'true_false' => 'True/False',
                //             'open_ended' => 'Open-Ended',
                //         ])
                //         ->required()
                //         ->reactive()
                //         ->searchable()
                //         ->afterStateUpdated(function (callable $get, callable $set, $record) {
                //             // Initialize options with A if multiple_choice type is selected
                //             if ($get('question_type') === 'multiple_choice') {
                //                 // Set initial option with key A
                //                 $set('options', $record->options? json_decode($record->options, true): [''=>'']); // Correctly setting initial values
                //             }else if($get('question_type') === 'true_false'){
                //                 $set('options', ['A' => 'True', 'B'=> 'False']);
                //             } else {
                //                 $set('options', []); // Clear options if not multiple_choice
                //             }
                //         }),
                //     // Use the KeyValue component for options
                //     KeyValue::make('options')
                //     ->keyLabel('Option Key')   // Label for the key
                //     ->valueLabel('Option Text') // Label for the value
                //     ->required()
                //     ->reorderable()
                //     ->hidden(fn (callable $get) => (($get('question_type') !== 'multiple_choice')&& ($get('question_type') !== 'true_false')))
                //     ->formatStateUsing(fn ($state) => is_array($state) ? $state : json_decode($state, true))
                //     ->columnSpan('full'), // Optional: to control column span


                //     Forms\Components\Textarea::make('answer')
                //         ->label('Answer'),

                //     Forms\Components\Textarea::make('hint')
                //         ->nullable()
                //         ->label('Hint')
                //         ->columnSpan('full'),

                //     FileUpload::make('image')
                //         ->label('Image')
                //         ->disk('s3') // Specify Cloudinary as the storage disk
                //         ->nullable()
                //         ->image() // Restrict to image files
                //         ->directory('exam_images') // Optional: specify a directory in Cloudinary
                //         ->columnSpan('full'),

                // ]) ->mutateRecordDataUsing(function (array $data, $record) {
                //     // Modify or process data before saving
                //     if (isset($data['options']) && is_array($data['options'])) {
                //         $data['options'] = json_encode($data['options']); // Convert options to JSON
                //     }

                //     return $data;

                // }),

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
                                    ->label('Correct Answer')->fixIndistinctState(),
                            ])
                            ->hidden(fn (callable $get) => $get('question_type') === 'open_ended')
                            ->columnSpan('full')
                            ->defaultItems(1)
                            ->maxItems(10)
                            ->collapsible()
                            ->formatStateUsing(function ($state) {
                                // dd($state);
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
                            $data['options'] = []; // Keep it as an array, not JSON
                        }

                        return $data; //
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

    // Hook to handle saving questions and options


}
