<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ResultSectionTypeResource\Pages;
use App\Filament\App\Resources\ResultSectionTypeResource\RelationManagers;
use App\Models\ResultSection;
use App\Models\ResultSectionType;
use App\Models\Term;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResultSectionTypeResource extends Resource
{
    protected static ?string $model = ResultSectionType::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('result_section_id')
                ->label('Result Section')
                ->options(ResultSection::all()->pluck('name', 'id'))
                ->preload()
                ->searchable(),
                TextInput::make('name')
                ->label('Name')
                ->required(),
                TextInput::make('code')
                ->label('Code')
                ->required(),
                Select::make('type')->label('Type')
                ->options([
                    "numeric"=> "Numeric",
                    "text"=>"Text"
                ])->searchable()
                ->live(),

                Select::make('term_id')
                ->options(Term::all()->pluck('name', 'id')->toArray())
                ->live()
                ->label('Term')
                ->preload()
                ->searchable(),

                Select::make('calc_pattern')->label('Calculation Pattern')
                ->options([
                    "input"=> "Input",
                    "total"=>"Total",
                    "class_average"=> "Class Average",
                    "class_highest_score"=> "Class Highest Score",
                    "class_lowest_score"=> "Class Lowest Score",
                    "grade_level" => "Grade Level",
                    "remarks"=> "Remarks",
                    "position"=> "Position"

                ])->searchable(),
                // ->required(fn (callable $get) => $get('type') === 'numeric')
                // ->visible(fn (callable $get) => $get('type') === 'numeric'),

                TextInput::make('score_weight')
                ->label('Weighted Score')
                ->default(0)
                ->required(fn (callable $get) => $get('type') === 'numeric')
                ->visible(fn (callable $get) => $get('type') === 'numeric'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
        TextColumn::make('resultSection.name')
            ->label('Result Section')
            ->sortable()
            ->searchable(),

        TextColumn::make('name')
            ->label('Name')
            ->sortable()
            ->searchable(),

            TextColumn::make('term.name')
            ->label('Term')
            ->sortable()
            ->searchable(),

        TextColumn::make('code')
            ->label('Code')
            ->sortable()
            ->searchable(),

        TextColumn::make('type')
            ->label('Type')
            ->sortable(),

            TextColumn::make('calc_pattern')
            ->label('Calculation Pattern')
            ->sortable(),


        TextColumn::make('score_weight')
            ->label('Weighted Score')
            ->sortable()

            ])
            ->filters([
                SelectFilter::make('result_section_id')
                ->label('Result Section')
                ->options(ResultSection::all()->pluck('name', 'id'))
                ->searchable(),
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
            'index' => Pages\ListResultSectionTypes::route('/'),
            'create' => Pages\CreateResultSectionType::route('/create'),
            'view' => Pages\ViewResultSectionType::route('/{record}'),
            'edit' => Pages\EditResultSectionType::route('/{record}/edit'),
        ];
    }
}
