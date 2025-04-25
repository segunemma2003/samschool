<?php

namespace App\Filament\Hostel\Resources;

use App\Filament\Hostel\Resources\HostelRoomResource\Pages;
use App\Filament\Hostel\Resources\HostelRoomResource\RelationManagers;
use App\Models\HostelFloor;
use App\Models\HostelRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HostelRoomResource extends Resource
{
    protected static ?string $model = HostelRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Hostel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('hostel_floor_id')
                    ->relationship('floor', 'floor_number', fn ($query) => $query->with('building'))
                    ->getOptionLabelFromRecordUsing(fn (HostelFloor $record) => "{$record->building->name} - Floor {$record->floor_number}")
                    ->required(),
                Forms\Components\TextInput::make('room_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('capacity')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('facilities')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('floor.building.name')
                ->sortable(),
            Tables\Columns\TextColumn::make('floor.floor_number')
                ->label('Floor')
                ->sortable(),
            Tables\Columns\TextColumn::make('room_number')
                ->searchable(),
            Tables\Columns\TextColumn::make('current_occupancy')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('capacity')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('occupancy_rate')
                ->state(fn (HostelRoom $record): string => "{$record->current_occupancy}/{$record->capacity}"),
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
            RelationManagers\CurrentAssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHostelRooms::route('/'),
            'create' => Pages\CreateHostelRoom::route('/create'),
            'view' => Pages\ViewHostelRoom::route('/{record}'),
            'edit' => Pages\EditHostelRoom::route('/{record}/edit'),
        ];
    }
}
