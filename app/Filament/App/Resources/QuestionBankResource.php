<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\QuestionBankResource\Pages;
use App\Filament\App\Resources\QuestionBankResource\RelationManagers;
use App\Models\Exam;
use App\Models\QuestionBank;
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

class QuestionBankResource extends Resource
{
    protected static ?string $model = QuestionBank::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $exams = Exam::all()->pluck('subject.code', 'id');
        return $form
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
                            // Initialize options with A if multiple_choice type is selected
                            if ($get('type') === 'multiple_choice') {
                                // Set initial option with key A
                                $set('options', ['' => '']); // Correctly setting initial values
                            }else if($get('type') === 'true_false'){
                                $set('options', ['A' => 'True', 'B'=> 'False']);
                             } else {
                                $set('options', []); // Clear options if not multiple_choice
                            }
                        }),
                    // Use the KeyValue component for options
                    KeyValue::make('options')
                    ->keyLabel('Option Key')   // Label for the key
                    ->valueLabel('Option Text') // Label for the value
                    ->required()
                    ->reorderable()
                    ->hidden(fn (callable $get) => (($get('question_type') !== 'multiple_choice')&& ($get('question_type') !== 'true_false')))

                    ->columnSpan('full'), // Optional: to control column span


                    Forms\Components\Textarea::make('answer')
                        ->label('Answer')->required(),
                        // ->hidden(fn (callable $get) => $get('type') !== 'open_ended'),

                        // Forms\Components\Textarea::make('correct_answer')
                        // ->required()
                        // ->label('Correct Answer')
                        // ->hidden(fn (callable $get) => $get('type') === 'open_ended'),
                        // Select::make('correct_answer')
                        // ->options(function (callable $get) {
                        //     $type = $get('type');
                        //     if ($type === 'multiple_choice') {
                        //         $options = $get('options');
                        //         // Ensure correct answer is selected based on the key-value structure of options
                        //         // Map the options array to get the values for the correct_answer dropdown
                        //         return collect($options)->pluck('value', 'key')->toArray();
                        //     } elseif ($type === 'true_false') {
                        //         return [
                        //             'True' => 'True',
                        //             'False' => 'False',
                        //         ];
                        //     }
                        //     return [];
                        // })
                        // ->required()
                        // ->label('Correct Answer')
                        // ->columnSpan('full')
                        // ->hidden(fn (callable $get) => $get('type') === 'open_ended'),
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
                        ->label('Image')
                        ->disk('s3') // Specify Cloudinary as the storage disk
                        ->nullable()
                        ->image() // Restrict to image files
                        ->directory('exam_images') // Optional: specify a directory in Cloudinary
                        ->columnSpan('full'),
                ])
                ->columnSpanFull()
                ->minItems(1)
                ->collapsible()
                ->maxItems(100)
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->form([
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
                        ->afterStateUpdated(function (callable $get, callable $set, $record) {
                            // Initialize options with A if multiple_choice type is selected
                            if ($get('question_type') === 'multiple_choice') {
                                // Set initial option with key A
                                $set('options', $record->options? json_decode($record->options, true): [''=>'']); // Correctly setting initial values
                            }else if($get('question_type') === 'true_false'){
                                $set('options', ['A' => 'True', 'B'=> 'False']);
                            } else {
                                $set('options', []); // Clear options if not multiple_choice
                            }
                        }),
                    // Use the KeyValue component for options
                    KeyValue::make('options')
                    ->keyLabel('Option Key')   // Label for the key
                    ->valueLabel('Option Text') // Label for the value
                    ->required()
                    ->reorderable()
                    ->hidden(fn (callable $get) => (($get('question_type') !== 'multiple_choice')&& ($get('question_type') !== 'true_false')))
                    ->formatStateUsing(fn ($state) => is_array($state) ? $state : json_decode($state, true))
                    ->columnSpan('full'), // Optional: to control column span


                    Forms\Components\Textarea::make('answer')
                        ->label('Answer'),

                    Forms\Components\Textarea::make('hint')
                        ->nullable()
                        ->label('Hint')
                        ->columnSpan('full'),

                    FileUpload::make('image')
                        ->label('Image')
                        ->disk('s3') // Specify Cloudinary as the storage disk
                        ->nullable()
                        ->image() // Restrict to image files
                        ->directory('exam_images') // Optional: specify a directory in Cloudinary
                        ->columnSpan('full'),

                ]) ->mutateRecordDataUsing(function (array $data, $record) {
                    // Modify or process data before saving
                    if (isset($data['options']) && is_array($data['options'])) {
                        $data['options'] = json_encode($data['options']); // Convert options to JSON
                    }

                    return $data;

                }),
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
            'index' => Pages\ListQuestionBanks::route('/'),
            'create' => Pages\CreateQuestionBank::route('/create'),
            'view' => Pages\ViewQuestionBank::route('/{record}'),
            'edit' => Pages\EditQuestionBank::route('/{record}/edit'),
        ];
    }
}
