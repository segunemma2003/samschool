<?php

namespace App\Filament\Library\Resources;

use App\Filament\Library\Resources\LibraryShelfResource\Pages;
use App\Filament\Library\Resources\LibraryShelfResource\RelationManagers;
use App\Models\LibraryShelf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LibraryShelfResource extends Resource
{
    protected static ?string $model = LibraryShelf::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Library';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('location_id')
                    ->relationship('location', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('row_count')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('position_count')
                    ->required()
                    ->numeric()
                    ->minValue(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('location.name'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('capacity')
                    ->state(fn (LibraryShelf $record) => $record->capacity),
                Tables\Columns\TextColumn::make('books_count')
                    ->counts('books')
                    ->label('Books'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                ->relationship('location', 'name'),
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
            RelationManagers\BooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLibraryShelves::route('/'),
            'create' => Pages\CreateLibraryShelf::route('/create'),
            'view' => Pages\ViewLibraryShelf::route('/{record}'),
            'edit' => Pages\EditLibraryShelf::route('/{record}/edit'),
        ];
    }
}
