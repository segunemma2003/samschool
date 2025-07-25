<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\SalaryTemplaeResource\Pages;
use App\Filament\Teacher\Resources\SalaryTemplaeResource\RelationManagers;
use App\Models\SalaryTemplae;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalaryTemplaeResource extends Resource
{
    protected static ?string $model = SalaryTemplae::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finance & Payroll';

    protected static ?string $label = "pay check";
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
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSalaryTemplaes::route('/'),
            'create' => Pages\CreateSalaryTemplae::route('/create'),
            'edit' => Pages\EditSalaryTemplae::route('/{record}/edit'),
        ];
    }
}
