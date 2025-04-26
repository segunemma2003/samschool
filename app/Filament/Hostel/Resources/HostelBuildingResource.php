<?php

namespace App\Filament\Hostel\Resources;

use App\Filament\Hostel\Resources\HostelBuildingResource\Pages;
use App\Filament\Hostel\Resources\HostelBuildingResource\RelationManagers;
use App\Models\HostelBuilding;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HostelBuildingResource extends Resource
{
    protected static ?string $model = HostelBuilding::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Hostel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('gender_type')
                ->options([
                    'male' => 'Male',
                    'female' => 'Female',
                    'mixed' => 'Mixed'
                ])
                ->required(),
            Forms\Components\Textarea::make('description')
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'primary',
                        'female' => 'pink',
                        'mixed' => 'purple',
                    }),
                Tables\Columns\TextColumn::make('floors_count')
                    ->counts('floors')
                    ->label('Floors'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            RelationManagers\FloorsRelationManager::class,
            RelationManagers\HouseMastersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHostelBuildings::route('/'),
            'create' => Pages\CreateHostelBuilding::route('/create'),
            'view' => Pages\ViewHostelBuilding::route('/{record}'),
            'edit' => Pages\EditHostelBuilding::route('/{record}/edit'),
        ];
    }
}
