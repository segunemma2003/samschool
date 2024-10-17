<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\QuestionBankResource\Pages;
use App\Filament\Teacher\Resources\QuestionBankResource\RelationManagers;
use App\Models\Exam;
use App\Models\QuestionBank;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionBankResource extends Resource
{
    protected static ?string $model = QuestionBank::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $label = 'Questions';

    protected static ?string $navigationGroup = 'Exams';

    public static function form(Form $form): Form
    {
        $exams = Exam::all()->pluck('subject.name', 'id');

            return $form
            ->schema([
                Forms\Components\Select::make('exam_id') // Select field for exam_id
                ->label('Select Exam')
                ->options($exams) // Populate options from exams
                ->required(),

                Forms\Components\Repeater::make('questions') // Repeater for questions
                ->schema([
                    Forms\Components\Textarea::make('question') // Change to Textarea
                        ->required()
                        ->label('Question')
                        ->rows(3) // Set initial number of rows
                        ->columnSpan('full'), // Make it full width

                        Forms\Components\Select::make('type')
                        ->options([
                            'multiple_choice' => 'Multiple Choice',
                            'true_false' => 'True/False',
                            'open_ended' => 'Open-Ended',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            // Reset options when the question type changes
                            $set('options', []);
                            // Reset answer when type changes to open-ended
                            if ($get('type') === 'open_ended') {
                                $set('answer', null); // Reset answer field if needed
                            }
                        }),
                        Forms\Components\Repeater::make('options')
                        ->schema([
                            Forms\Components\TextInput::make('option_text')
                                ->required()
                                ->label('Option')
                                ->columnSpan('full'), // Make it full width
                        ])
                        ->createItemButtonLabel('Add Answer Option')
                        ->hidden(fn (callable $get) => $get('type') !== 'multiple_choice'), // Show only for multiple choice

                    // Conditional display for the answer based on type
                    Forms\Components\Textarea::make('answer') // Change to Textarea for open-ended
                        ->label('Answer')
                        ->hidden(fn (callable $get) => $get('type') !== 'open_ended'), // Show only for open-ended

                    Forms\Components\Select::make('correct_answer')
                        ->options(function (callable $get) {
                            $type = $get('type');
                            if ($type === 'multiple_choice') {
                                // Get options for multiple choice questions
                                $options = $get('options');
                                return collect($options)->pluck('option_text', 'option_text')->toArray();
                            } elseif ($type === 'true_false') {
                                // Provide options for true/false questions
                                return [
                                    'True' => 'True',
                                    'False' => 'False',
                                ];
                            }
                            return [];
                        })
                        ->required()
                        ->label('Correct Answer')
                        ->columnSpan('full'), // Make it full width
                    Forms\Components\TextInput::make('mark')
                        ->numeric()
                        ->default(1)
                        ->label('Mark')
                        ->columnSpan('full'), // Make it full width

                    Forms\Components\Textarea::make('hint')
                        ->nullable()
                        ->label('Hint')
                        ->columnSpan('full'), // Make it full width

                    Forms\Components\TextInput::make('image')
                        ->nullable()
                        ->label('Image')
                        ->columnSpan('full'), // Make it full width
                ])
                ->minItems(1) // At least one question required
                ->maxItems(10) // Adjust as necessary
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'edit' => Pages\EditQuestionBank::route('/{record}/edit'),
        ];
    }
}
