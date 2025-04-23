<?php

namespace App\Filament\Ourparent\Resources;

use App\Filament\Ourparent\Resources\CommunicationBookResource\Pages;
use App\Filament\Ourparent\Resources\CommunicationBookResource\RelationManagers;
use App\Models\CommunicationBook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommunicationBookResource extends Resource
{
    protected static ?string $model = CommunicationBook::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

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
            'index' => Pages\ListCommunicationBooks::route('/'),
            'create' => Pages\CreateCommunicationBook::route('/create'),
            'view' => Pages\ViewCommunicationBook::route('/{record}'),
            'edit' => Pages\EditCommunicationBook::route('/{record}/edit'),
        ];
    }
}
