<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\ExamResource\Pages;
use App\Filament\Teacher\Resources\ExamResource\RelationManagers;
use App\Models\Exam;
use App\Models\SchoolSection;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('school_section_id')
                ->options(SchoolSection::all()->pluck('section', 'id'))
                ->preload()
                ->label("School Section")
                ->required()
                ->searchable(),
                Select::make('subject_id')
                ->options(Subject::all()->pluck('code', 'id'))
                ->preload()
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
                Select::make('assessment_type')
                ->options([
                    "test"=>"Test",
                    "exam"=>"Exam"
                ])
                ->preload()
                ->label("Subject")
                ->searchable(),

                TextInput::make('total_score')
                ->required()
                ->integer(),
                RichEditor::make('instructions')
                ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('academic.title')->searchable(),
                TextColumn::make('section.section')->searchable(),
                TextColumn::make('subject.code')->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'view' => Pages\ViewExam::route('/{record}'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
        ];
    }
}
