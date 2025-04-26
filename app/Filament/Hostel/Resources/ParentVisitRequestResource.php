<?php

namespace App\Filament\Hostel\Resources;

use App\Filament\Hostel\Resources\ParentVisitRequestResource\Pages;
use App\Filament\Hostel\Resources\ParentVisitRequestResource\RelationManagers;
use App\Models\ParentVisitRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ParentVisitRequestResource extends Resource
{
    protected static ?string $model = ParentVisitRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Hostel Management';

    protected static ?string $modelLabel = 'Parent Visit Request';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('parent_id')
                ->relationship('parent', 'name')
                ->searchable()
                ->required(),
            Forms\Components\Select::make('student_id')
                ->relationship('student', 'name')
                ->searchable()
                ->required(),
            Forms\Components\Select::make('hostel_building_id')
                ->relationship('building', 'name')
                ->required(),
            Forms\Components\DateTimePicker::make('proposed_visit_date')
                ->required(),
            Forms\Components\Textarea::make('purpose')
                ->required()
                ->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'completed' => 'Completed'
                ])
                ->required(),
            Forms\Components\DateTimePicker::make('actual_visit_date')
                ->nullable(),
            Forms\Components\Textarea::make('admin_notes')
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('parent.name')
                ->searchable(),
            Tables\Columns\TextColumn::make('student.name')
                ->searchable(),
            Tables\Columns\TextColumn::make('building.name'),
            Tables\Columns\TextColumn::make('proposed_visit_date')
                ->dateTime()
                ->sortable(),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'completed' => 'primary',
                }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed'
                    ]),
                Tables\Filters\SelectFilter::make('hostel_building_id')
                    ->relationship('building', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                ->action(function (ParentVisitRequest $record): void {
                    $record->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Visit request approved')
                        ->success()
                        ->send();
                })
                ->visible(fn (ParentVisitRequest $record): bool => $record->status === 'pending')
                ->color('success'),
            Tables\Actions\Action::make('complete')
                ->form([
                    Forms\Components\DateTimePicker::make('actual_visit_date')
                        ->required(),
                    Forms\Components\Textarea::make('admin_notes'),
                ])
                ->action(function (ParentVisitRequest $record, array $data): void {
                    $record->update([
                        'status' => 'completed',
                        'actual_visit_date' => $data['actual_visit_date'],
                        'admin_notes' => $data['admin_notes'],
                    ]);

                    Notification::make()
                        ->title('Visit marked as completed')
                        ->success()
                        ->send();
                })
                ->visible(fn (ParentVisitRequest $record): bool => $record->status === 'approved')
                ->color('primary'),
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
            'index' => Pages\ListParentVisitRequests::route('/'),
            'create' => Pages\CreateParentVisitRequest::route('/create'),
            'view' => Pages\ViewParentVisitRequest::route('/{record}'),
            'edit' => Pages\EditParentVisitRequest::route('/{record}/edit'),
        ];
    }
}
