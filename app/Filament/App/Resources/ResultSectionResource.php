<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ResultSectionResource\Pages;
use App\Filament\App\Resources\ResultSectionResource\RelationManagers;
use App\Models\ResultSection;
use App\Models\StudentGroup;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResultSectionResource extends Resource
{
    protected static ?string $model = ResultSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('group_id')
                ->label('School Section')
                ->options(StudentGroup::all()->pluck('name', 'id'))
                ->preload()
                ->searchable(),
                TextInput::make('name')
                ->label('Name')
                ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name'),
                TextColumn::make('group.name')->label('School Section')
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
            'index' => Pages\ListResultSections::route('/'),
            'create' => Pages\CreateResultSection::route('/create'),
            'view' => Pages\ViewResultSection::route('/{record}'),
            'edit' => Pages\EditResultSection::route('/{record}/edit'),
        ];
    }
}
