<?php

namespace App\Filament\Library\Resources;

use App\Filament\Library\Resources\LibraryLocationResource\Pages;
use App\Filament\Library\Resources\LibraryLocationResource\RelationManagers;
use App\Models\LibraryLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LibraryLocationResource extends Resource
{
    protected static ?string $model = LibraryLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Library';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('code')
                ->required()
                ->maxLength(10)
                ->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('description')
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('shelves_count')
                    ->counts('shelves')
                    ->label('Shelves'),
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
            RelationManagers\ShelvesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLibraryLocations::route('/'),
            'create' => Pages\CreateLibraryLocation::route('/create'),
            'view' => Pages\ViewLibraryLocation::route('/{record}'),
            'edit' => Pages\EditLibraryLocation::route('/{record}/edit'),
        ];
    }
}
