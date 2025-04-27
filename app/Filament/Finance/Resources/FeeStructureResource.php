<?php

namespace App\Filament\Finance\Resources;

use App\Filament\Finance\Resources\FeeStructureResource\Pages;
use App\Filament\Finance\Resources\FeeStructureResource\RelationManagers;
use App\Filament\Finance\Resources\FeeStructureResource\RelationManagers\FeeItemsRelationManager;
use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\Term;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FeeStructureResource extends Resource
{
    protected static ?string $model = FeeStructure::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Fee Structure Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('₦'),
                        Forms\Components\Toggle::make('is_active')
                            ->required(),
                        Forms\Components\Toggle::make('is_recurring')
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('recurrence_interval')
                            ->options([
                                'monthly' => 'Monthly',
                                'termly' => 'Termly',
                                'annually' => 'Annually',
                            ])
                            ->visible(fn (Forms\Get $get) => $get('is_recurring')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
            Tables\Columns\TextColumn::make('amount')
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
            FeeItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeeStructures::route('/'),
            'create' => Pages\CreateFeeStructure::route('/create'),
            'view' => Pages\ViewFeeStructure::route('/{record}'),
            'edit' => Pages\EditFeeStructure::route('/{record}/edit'),
        ];
    }
}
