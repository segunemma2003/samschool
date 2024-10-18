<?php

namespace App\Filament\Ourparent\Resources;

use App\Filament\Ourparent\Resources\ASponsorResource\Pages;
use App\Filament\Ourparent\Resources\ASponsorResource\RelationManagers;
use App\Models\ASponsor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ASponsorResource extends Resource
{
    protected static ?string $model = ASponsor::class;

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
            'index' => Pages\ListASponsors::route('/'),
            'create' => Pages\CreateASponsor::route('/create'),
            'edit' => Pages\EditASponsor::route('/{record}/edit'),
        ];
    }
}
