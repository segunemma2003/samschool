<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\PsychomotorCategoryResource\Pages;
use App\Filament\App\Resources\PsychomotorCategoryResource\RelationManagers;
use App\Models\PsychomotorCategory;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PsychomotorCategoryResource extends Resource
{
    protected static ?string $model = PsychomotorCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()
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
            'index' => Pages\ListPsychomotorCategories::route('/'),
            'create' => Pages\CreatePsychomotorCategory::route('/create'),
            'view' => Pages\ViewPsychomotorCategory::route('/{record}'),
            'edit' => Pages\EditPsychomotorCategory::route('/{record}/edit'),
        ];
    }
}
