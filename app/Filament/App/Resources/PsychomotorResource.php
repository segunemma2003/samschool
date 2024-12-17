<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\PsychomotorResource\Pages;
use App\Filament\App\Resources\PsychomotorResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Psychomotor;
use App\Models\PsychomotorCategory;
use App\Models\SchoolClass;
use App\Models\StudentGroup;
use App\Models\Term;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PsychomotorResource extends Resource
{
    protected static ?string $model = Psychomotor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Academic';

    protected static ?string $label = "Psychomotor Skills";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('group_id')
                    ->label('Section')
                    ->options(StudentGroup::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),
                Forms\Components\Select::make('term_id')
                    ->label('Term')
                    ->options(Term::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),
                Forms\Components\Select::make('academic_id')
                    ->label('Academy Year')
                    ->options(AcademicYear::all()->pluck('title', 'id'))
                    ->preload()
                    ->searchable(),
                    Forms\Components\Select::make('psychomotor_category_id')
                    ->label('Category')
                    ->options(PsychomotorCategory::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('skill')
                    ->required()
                    ->maxLength(255),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group.name')
                ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('academy.title')
                ->searchable(),
                Tables\Columns\TextColumn::make('term.name')
                ->searchable(),
                Tables\Columns\TextColumn::make('skill')
                ->searchable(),
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
            'index' => Pages\ListPsychomotors::route('/'),
            'create' => Pages\CreatePsychomotor::route('/create'),
            'view' => Pages\ViewPsychomotor::route('/{record}'),
            'edit' => Pages\EditPsychomotor::route('/{record}/edit'),

        ];
    }
}
