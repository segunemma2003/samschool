<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\EBooksResource\Pages;
use App\Filament\Ourstudent\Resources\EBooksResource\RelationManagers;
use App\Models\EBooks;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EBooksResource extends Resource
{
    protected static ?string $model = EBooks::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
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
            'index' => Pages\ListEBooks::route('/'),
            'create' => Pages\CreateEBooks::route('/create'),
            'view' => Pages\ViewEBooks::route('/{record}'),
            'edit' => Pages\EditEBooks::route('/{record}/edit'),
        ];
    }
}
