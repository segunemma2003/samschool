<?php

namespace App\Filament\Hostel\Resources;

use App\Filament\Hostel\Resources\HostelFloorResource\Pages;
use App\Filament\Hostel\Resources\HostelFloorResource\RelationManagers;
use App\Models\HostelFloor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HostelFloorResource extends Resource
{
    protected static ?string $model = HostelFloor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Hostel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('hostel_building_id')
                ->relationship('building', 'name')
                ->required(),
                Forms\Components\TextInput::make('floor_number')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                ->sortable(),
            Tables\Columns\TextColumn::make('floor_number')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('rooms_count')
                ->counts('rooms')
                ->label('Rooms'),
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
            RelationManagers\RoomsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHostelFloors::route('/'),
            'create' => Pages\CreateHostelFloor::route('/create'),
            'view' => Pages\ViewHostelFloor::route('/{record}'),
            'edit' => Pages\EditHostelFloor::route('/{record}/edit'),
        ];
    }
}
