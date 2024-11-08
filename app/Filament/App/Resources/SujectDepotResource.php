<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SujectDepotResource\Pages;
use App\Filament\App\Resources\SujectDepotResource\RelationManagers;
use App\Models\SubjectDepot;
use App\Models\SujectDepot;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SujectDepotResource extends Resource
{
    protected static ?string $model = SubjectDepot::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // protected static ?string  $label = "Section";
    protected static ?string $navigationGroup = 'Academic';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('code')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('code')->searchable()->sortable(),
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
            'index' => Pages\ListSujectDepots::route('/'),
            'create' => Pages\CreateSujectDepot::route('/create'),
            'view' => Pages\ViewSujectDepot::route('/{record}'),
            'edit' => Pages\EditSujectDepot::route('/{record}/edit'),
        ];
    }
}
