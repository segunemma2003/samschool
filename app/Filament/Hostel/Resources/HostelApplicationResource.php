<?php

namespace App\Filament\Hostel\Resources;

use App\Filament\Hostel\Resources\HostelApplicationResource\Pages;
use App\Filament\Hostel\Resources\HostelApplicationResource\RelationManagers;
use App\Models\HostelApplication;
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

class HostelApplicationResource extends Resource
{
    protected static ?string $model = HostelApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Hostel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('term_id')
                    ->relationship('term', 'name')
                    ->required(),
                Forms\Components\Select::make('academic_year_id')
                    ->relationship('academicYear', 'title')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('admin_notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('term.name'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('term_id')
                    ->relationship('term', 'name'),
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->relationship('acaemicYear', 'title'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('assignRoom')
                ->form([
                    Forms\Components\Select::make('hostel_room_id')
                        ->relationship('room', 'room_number', fn ($query) => $query->whereColumn('current_occupancy', '<', 'capacity'))
                        ->getOptionLabelFromRecordUsing(fn (HostelRoom $record) => "{$record->floor->building->name} - Floor {$record->floor->floor_number} - Room {$record->room_number} ({$record->current_occupancy}/{$record->capacity})")
                        ->required(),
                ])
                ->action(function (HostelApplication $record, array $data): void {
                    $record->assignment()->create([
                        'hostel_room_id' => $data['hostel_room_id'],
                        'student_id' => $record->student_id,
                        'term_id' => $record->term_id,
                        'assignment_date' => now(),
                    ]);

                    HostelRoom::find($data['hostel_room_id'])->increment('current_occupancy');

                    $record->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Room assigned successfully')
                        ->success()
                        ->send();
                })
                ->visible(fn (HostelApplication $record): bool => $record->status === 'pending'),
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
            RelationManagers\AssignmentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHostelApplications::route('/'),
            'create' => Pages\CreateHostelApplication::route('/create'),
            'view' => Pages\ViewHostelApplication::route('/{record}'),
            'edit' => Pages\EditHostelApplication::route('/{record}/edit'),
        ];
    }
}
