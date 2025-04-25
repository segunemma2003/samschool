<?php

namespace App\Filament\Hostel\Resources;

use App\Filament\Hostel\Pages\TakeMealAttendance;
use App\Filament\Hostel\Resources\HostelMealResource\Pages;
use App\Filament\Hostel\Resources\HostelMealResource\RelationManagers;
use App\Models\HostelMeal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HostelMealResource extends Resource
{
    protected static ?string $model = HostelMeal::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Hostel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('meal_date')
                    ->required(),
                Forms\Components\Select::make('meal_type')
                    ->options([
                        'breakfast' => 'Breakfast',
                        'lunch' => 'Lunch',
                        'dinner' => 'Dinner'
                    ])
                    ->required(),
                Forms\Components\TextInput::make('menu_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('menu_description')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('meal_date')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('meal_type')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'breakfast' => 'amber',
                    'lunch' => 'blue',
                    'dinner' => 'indigo',
                }),
            Tables\Columns\TextColumn::make('menu_name')
                ->searchable(),
            Tables\Columns\TextColumn::make('attendances_count')
                ->counts('attendances')
                ->label('Attendees'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('meal_type')
                    ->options([
                        'breakfast' => 'Breakfast',
                        'lunch' => 'Lunch',
                        'dinner' => 'Dinner'
                    ]),
                Tables\Filters\Filter::make('meal_date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from'),
                        Forms\Components\DatePicker::make('date_until'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['date_from'],
                                    fn (Builder $query, $date): Builder => $query->whereDate('meal_date', '>=', $date),
                                )
                                ->when(
                                    $data['date_until'],
                                    fn (Builder $query, $date): Builder => $query->whereDate('meal_date', '<=', $date),
                                );
                        }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('takeAttendance')
                ->url(fn (HostelMeal $record): string => route('filament.resources.hostel-meals.take-attendance', $record))
                ->icon('heroicon-o-clipboard-document-check')
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
            'index' => Pages\ListHostelMeals::route('/'),
            'create' => Pages\CreateHostelMeal::route('/create'),
            'view' => Pages\ViewHostelMeal::route('/{record}'),
            'edit' => Pages\EditHostelMeal::route('/{record}/edit'),
            // 'take-attendance' => TakeMealAttendance::route('/{record}/take-attendance'),
        ];
    }
}
