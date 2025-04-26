<?php

namespace App\Filament\Hostel\Resources;

use App\Filament\Hostel\Resources\HostelHouseMasterResource\Pages;
use App\Filament\Hostel\Resources\HostelHouseMasterResource\RelationManagers;
use App\Models\HostelHouseMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HostelHouseMasterResource extends Resource
{
    protected static ?string $model = HostelHouseMaster::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Hostel Management';
    protected static ?string $modelLabel = 'House Master Assignment';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('hostel_building_id')
                ->relationship('building', 'name')
                ->required(),
            Forms\Components\Select::make('teacher_id')
                ->relationship('teacher', 'name')
                ->searchable()
                ->required(),
            Forms\Components\Select::make('academic_year_id')
                ->relationship('academicYear', 'name')
                ->required(),
            Forms\Components\DatePicker::make('start_date')
                ->required(),
            Forms\Components\DatePicker::make('end_date')
                ->nullable(),
            Forms\Components\Toggle::make('is_current')
                ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                ->searchable(),
            Tables\Columns\TextColumn::make('teacher.name')
                ->searchable(),
            Tables\Columns\TextColumn::make('academicYear.name'),
            Tables\Columns\TextColumn::make('start_date')
                ->date(),
            Tables\Columns\TextColumn::make('end_date')
                ->date()
                ->placeholder('Current'),
            Tables\Columns\IconColumn::make('is_current')
                ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                ->relationship('academicYear', 'name'),
            Tables\Filters\SelectFilter::make('hostel_building_id')
                ->relationship('building', 'name'),
            Tables\Filters\Filter::make('current_only')
                ->query(fn (Builder $query): Builder => $query->where('is_current', true))
                ->label('Current Assignments Only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('endAssignment')
                    ->form([
                        Forms\Components\DatePicker::make('end_date')
                            ->required()
                            ->default(now()),
                    ])
                    ->action(function (HostelHouseMaster $record, array $data): void {
                        $record->update([
                            'end_date' => $data['end_date'],
                            'is_current' => false,
                        ]);

                        Notification::make()
                            ->title('House master assignment ended')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (HostelHouseMaster $record): bool => $record->is_current && !$record->end_date)
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHostelHouseMasters::route('/'),
            'create' => Pages\CreateHostelHouseMaster::route('/create'),
            'view' => Pages\ViewHostelHouseMaster::route('/{record}'),
            'edit' => Pages\EditHostelHouseMaster::route('/{record}/edit'),
        ];
    }
}
