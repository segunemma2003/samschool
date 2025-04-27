<?php

namespace App\Filament\Finance\Resources;

use App\Filament\Finance\Resources\SalaryStructureResource\Pages;
use App\Filament\Finance\Resources\SalaryStructureResource\RelationManagers;
use App\Models\SalaryStructure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalaryStructureResource extends Resource
{
    protected static ?string $model = SalaryStructure::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Salary Structure Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('basic_salary')
                            ->required()
                            ->numeric()
                            ->prefix('₦'),
                        Forms\Components\Toggle::make('is_active')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
            Tables\Columns\TextColumn::make('basic_salary')
                ->money('NGN')
                ->sortable(),
            Tables\Columns\IconColumn::make('is_active')
                ->boolean(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Only Active')
                    ->falseLabel('Only Inactive')
                    ->native(false),
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
            RelationManagers\AllowancesRelationManager::class,
            RelationManagers\DeductionsRelationManager::class,
            RelationManagers\StaffSalariesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalaryStructures::route('/'),
            'create' => Pages\CreateSalaryStructure::route('/create'),
            'view' => Pages\ViewSalaryStructure::route('/{record}'),
            'edit' => Pages\EditSalaryStructure::route('/{record}/edit'),
        ];
    }
}
