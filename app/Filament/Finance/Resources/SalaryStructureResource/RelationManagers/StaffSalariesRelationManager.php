<?php

namespace App\Filament\Finance\Resources\SalaryStructureResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StaffSalariesRelationManager extends RelationManager
{
    protected static string $relationship = 'staffSalaries';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('staff_id')
                ->relationship('staff', 'name')
                ->required(),
            Forms\Components\DatePicker::make('effective_from')
                ->required(),
            Forms\Components\DatePicker::make('effective_to')
                ->nullable(),
            Forms\Components\Toggle::make('is_active')
                ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('staff_id')
            ->columns([
                Tables\Columns\TextColumn::make('staff.name'),
                Tables\Columns\TextColumn::make('effective_from')
                    ->date(),
                Tables\Columns\TextColumn::make('effective_to')
                    ->date(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
