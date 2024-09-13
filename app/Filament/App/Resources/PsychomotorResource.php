<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\PsychomotorResource\Pages;
use App\Filament\App\Resources\PsychomotorResource\RelationManagers;
use App\Models\Psychomotor;
use App\Models\SchoolClass;
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
                Forms\Components\Select::make('class_id')
                    ->label('Class Name')
                    ->options(SchoolClass::all()->pluck('name', 'id'))
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
                //
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
