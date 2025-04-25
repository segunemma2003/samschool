<?php

namespace App\Filament\Hostel\Resources;

use App\Filament\Hostel\Resources\HostelMaintenanceRequestResource\Pages;
use App\Filament\Hostel\Resources\HostelMaintenanceRequestResource\RelationManagers;
use App\Models\HostelMaintenanceRequest;
use App\Models\HostelRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HostelMaintenanceRequestResource extends Resource
{
    protected static ?string $model = HostelMaintenanceRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    protected static ?string $navigationGroup = 'Hostel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('hostel_inventory_id')
                ->relationship('inventoryItem', 'item_name')
                ->searchable()
                ->nullable(),
            Forms\Components\Select::make('hostel_room_id')
                ->relationship('room', 'room_number', fn ($query) => $query->with('floor.building'))
                ->getOptionLabelFromRecordUsing(fn (HostelRoom $record) => "{$record->floor->building->name} - Floor {$record->floor->floor_number} - Room {$record->room_number}")
                ->required(),
            Forms\Components\Select::make('issue_type')
                ->options([
                    'electrical' => 'Electrical',
                    'plumbing' => 'Plumbing',
                    'furniture' => 'Furniture',
                    'structural' => 'Structural',
                    'other' => 'Other'
                ])
                ->required(),
            Forms\Components\Textarea::make('description')
                ->required()
                ->columnSpanFull(),
            Forms\Components\Select::make('priority')
                ->options([
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                    'critical' => 'Critical'
                ])
                ->required(),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'rejected' => 'Rejected'
                ])
                ->required(),
            Forms\Components\Select::make('assigned_to')
                ->relationship('assignedTo', 'name')
                ->searchable()
                ->nullable(),
            Forms\Components\DateTimePicker::make('completed_at')
                ->nullable(),
            Forms\Components\Textarea::make('resolution_notes')
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
            Tables\Columns\TextColumn::make('inventoryItem.item_name')
                ->label('Item')
                ->placeholder('General'),
            Tables\Columns\TextColumn::make('issue_type')
                ->badge(),
            Tables\Columns\TextColumn::make('priority')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'low' => 'gray',
                    'medium' => 'info',
                    'high' => 'warning',
                    'critical' => 'danger',
                }),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'in_progress' => 'primary',
                    'completed' => 'success',
                    'rejected' => 'danger',
                }),
            Tables\Columns\TextColumn::make('assignedTo.name')
                ->placeholder('Unassigned'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected'
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical'
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')
                ->form([
                    Forms\Components\Textarea::make('resolution_notes')
                        ->required(),
                ])
                ->action(function (HostelMaintenanceRequest $record, array $data): void {
                    $record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'resolution_notes' => $data['resolution_notes'],
                    ]);

                    Notification::make()
                        ->title('Maintenance request marked as completed')
                        ->success()
                        ->send();
                })
                ->visible(fn (HostelMaintenanceRequest $record): bool => $record->status !== 'completed')
                ->color('success'),
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
            'index' => Pages\ListHostelMaintenanceRequests::route('/'),
            'create' => Pages\CreateHostelMaintenanceRequest::route('/create'),
            'view' => Pages\ViewHostelMaintenanceRequest::route('/{record}'),
            'edit' => Pages\EditHostelMaintenanceRequest::route('/{record}/edit'),
        ];
    }
}
