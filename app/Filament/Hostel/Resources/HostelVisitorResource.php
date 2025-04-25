<?php

namespace App\Filament\Hostel\Resources;

use App\Filament\Hostel\Resources\HostelVisitorResource\Pages;
use App\Filament\Hostel\Resources\HostelVisitorResource\RelationManagers;
use App\Models\HostelVisitor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class HostelVisitorResource extends Resource
{
    protected static ?string $model = HostelVisitor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Hostel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('hostel_building_id')
                    ->relationship('building', 'name')
                    ->required(),
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('visitor_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('visitor_relation')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('visitor_phone')
                    ->tel()
                    ->required(),
                Forms\Components\Select::make('visitor_id_type')
                    ->options([
                        'national_id' => 'National ID',
                        'passport' => 'Passport',
                        'driving_license' => 'Driving License',
                        'other' => 'Other'
                    ])
                    ->required(),
                Forms\Components\TextInput::make('visitor_id_number')
                    ->required(),
                Forms\Components\DateTimePicker::make('visit_date')
                    ->required(),
                Forms\Components\DateTimePicker::make('expected_departure')
                    ->required(),
                Forms\Components\Textarea::make('purpose')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('building.name'),
                Tables\Columns\TextColumn::make('student.name'),
                Tables\Columns\TextColumn::make('visitor_name'),
                Tables\Columns\TextColumn::make('visitor_relation'),
                Tables\Columns\TextColumn::make('visit_date')
                    ->dateTime(),
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
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('hostel_building_id')
                    ->relationship('building', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                ->action(function (HostelVisitor $record): void {
                    $record->update([
                        'status' => 'approved',
                        'approved_by' => Auth::d(),
                    ]);

                    Notification::make()
                        ->title('Visitor approved successfully')
                        ->success()
                        ->send();
                })
                ->visible(fn (HostelVisitor $record): bool => $record->status === 'pending')
                ->color('success'),
            Tables\Actions\Action::make('complete')
                ->form([
                    Forms\Components\DateTimePicker::make('actual_departure')
                        ->required(),
                    Forms\Components\Textarea::make('admin_notes'),
                ])
                ->action(function (HostelVisitor $record, array $data): void {
                    $record->update([
                        'status' => 'completed',
                        'actual_departure' => $data['actual_departure'],
                        'admin_notes' => $data['admin_notes'],
                    ]);

                    Notification::make()
                        ->title('Visit marked as completed')
                        ->success()
                        ->send();
                })
                ->visible(fn (HostelVisitor $record): bool => $record->status === 'approved')
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
            'index' => Pages\ListHostelVisitors::route('/'),
            'create' => Pages\CreateHostelVisitor::route('/create'),
            'view' => Pages\ViewHostelVisitor::route('/{record}'),
            'edit' => Pages\EditHostelVisitor::route('/{record}/edit'),
        ];
    }
}
