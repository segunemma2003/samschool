<?php

namespace App\Filament\Finance\Resources\SalaryStructureResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AllowancesRelationManager extends RelationManager
{
    protected static string $relationship = 'allowances';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('amount')
                ->required()
                ->numeric()
                ->prefix('₦'),
            Forms\Components\Toggle::make('is_percentage')
                ->live()
                ->required(),
            Forms\Components\Select::make('percentage_of')
                ->options([
                    'basic' => 'Basic Salary',
                    'gross' => 'Gross Salary',
                ])
                ->visible(fn (Forms\Get $get) => $get('is_percentage')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(function ($record) {
                        if ($record->is_percentage) {
                            return $record->amount . '% of ' . $record->percentage_of;
                        }
                        return '₦' . number_format($record->amount, 2);
                    }),
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
