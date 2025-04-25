<?php

namespace App\Filament\Hostel\Resources;

use App\Filament\Hostel\Resources\HostelInventoryResource\Pages;
use App\Filament\Hostel\Resources\HostelInventoryResource\RelationManagers;
use App\Models\HostelDamageReport;
use App\Models\HostelInventory;
use App\Models\HostelRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class HostelInventoryResource extends Resource
{
    protected static ?string $model = HostelInventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Hostel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('hostel_room_id')
                ->relationship('room', 'room_number', fn ($query) => $query->with('floor.building'))
                ->getOptionLabelFromRecordUsing(fn (HostelRoom $record) => "{$record->floor->building->name} - Floor {$record->floor->floor_number} - Room {$record->room_number}")
                ->required(),
            Forms\Components\TextInput::make('item_name')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('item_type')
                ->options([
                    'bed' => 'Bed',
                    'mattress' => 'Mattress',
                    'desk' => 'Desk',
                    'chair' => 'Chair',
                    'wardrobe' => 'Wardrobe',
                    'other' => 'Other Furniture'
                ])
                ->required(),
            Forms\Components\TextInput::make('serial_number'),
            Forms\Components\DatePicker::make('purchase_date')
                ->required(),
            Forms\Components\TextInput::make('purchase_cost')
                ->numeric()
                ->required(),
            Forms\Components\Select::make('condition')
                ->options([
                    'new' => 'New',
                    'good' => 'Good',
                    'fair' => 'Fair',
                    'poor' => 'Poor',
                    'damaged' => 'Damaged'
                ])
                ->required(),
            Forms\Components\Textarea::make('notes')
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('room.floor.building.name')
                ->label('Building'),
            Tables\Columns\TextColumn::make('room.room_number')
                ->label('Room'),
            Tables\Columns\TextColumn::make('item_name')
                ->searchable(),
            Tables\Columns\TextColumn::make('item_type')
                ->badge(),
            Tables\Columns\TextColumn::make('condition')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'new' => 'success',
                    'good' => 'primary',
                    'fair' => 'warning',
                    'poor', 'damaged' => 'danger',
                }),
            Tables\Columns\TextColumn::make('purchase_cost')
                ->money()
                ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('item_type')
                    ->options([
                        'bed' => 'Bed',
                        'mattress' => 'Mattress',
                        'desk' => 'Desk',
                        'chair' => 'Chair',
                        'wardrobe' => 'Wardrobe',
                        'other' => 'Other Furniture'
                    ]),
                Tables\Filters\SelectFilter::make('condition')
                    ->options([
                        'new' => 'New',
                        'good' => 'Good',
                        'fair' => 'Fair',
                        'poor' => 'Poor',
                        'damaged' => 'Damaged'
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reportDamage')
                ->form([
                    Forms\Components\Textarea::make('description')
                        ->required(),
                    Forms\Components\Select::make('severity')
                        ->options([
                            'minor' => 'Minor',
                            'moderate' => 'Moderate',
                            'severe' => 'Severe'
                        ])
                        ->required(),
                    Forms\Components\Textarea::make('notes'),
                ])
                ->action(function (HostelInventory $record, array $data): void {
                    HostelDamageReport::create([
                        'hostel_inventory_id' => $record->id,
                        'hostel_room_id' => $record->hostel_room_id,
                        'reported_by' => Auth::id(),
                        'damage_type' => $record->item_type,
                        'description' => $data['description'],
                        'severity' => $data['severity'],
                        'status' => 'reported',
                        'resolution_notes' => $data['notes'] ?? null,
                    ]);

                    $record->update(['condition' => 'damaged']);

                    Notification::make()
                        ->title('Damage reported successfully')
                        ->success()
                        ->send();
                })
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),
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
            RelationManagers\MaintenanceRequestsRelationManager::class,
            RelationManagers\DamageReportsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHostelInventories::route('/'),
            'create' => Pages\CreateHostelInventory::route('/create'),
            'view' => Pages\ViewHostelInventory::route('/{record}'),
            'edit' => Pages\EditHostelInventory::route('/{record}/edit'),
        ];
    }
}
