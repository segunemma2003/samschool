<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\LiveMeetingResource\Pages;
use App\Filament\App\Resources\LiveMeetingResource\RelationManagers;
use App\Models\LiveMeeting;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LiveMeetingResource extends Resource
{
    protected static ?string $model = LiveMeeting::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('purpose')
                ->required(),
            Forms\Components\DatePicker::make('date_of_meeting')
                ->required(),
            Forms\Components\TimePicker::make('time_of_meeting')
                ->required(),
            Forms\Components\Select::make('meeting_platform')
                ->label('Meeting Platform')
                ->options([
                    'google_meet' => 'Google Meet',
                    'ms_teams' => 'Microsoft Teams',
                    'zoom' => 'Zoom'
                ])
                ->required()
                ->helperText('A meeting will be created on this platform when you save'),
            Forms\Components\TextInput::make('url')
                ->label('Meeting Link')
                ->disabled()
                ->dehydrated()
                ->helperText('This will be generated automatically when the meeting is created')
                ->hidden(fn (string $operation): bool => $operation === 'create'),
        ])
        ->statePath('data');

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                ->searchable(),
            Tables\Columns\TextColumn::make('date_of_meeting')
                ->date(),
            Tables\Columns\TextColumn::make('time_of_meeting')
                ->time(),
            Tables\Columns\TextColumn::make('meeting_platform')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'google_meet' => 'success',
                    'ms_teams' => 'info',
                    'zoom' => 'warning',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'google_meet' => 'Google Meet',
                    'ms_teams' => 'Microsoft Teams',
                    'zoom' => 'Zoom',
                    default => $state,
                }),
            Tables\Columns\TextColumn::make('url')
                ->label('Meeting Link')
                ->url(fn (LiveMeeting $record): string => $record->url)
                ->openUrlInNewTab()
                ->copyable()
                ->copyMessage('Meeting link copied')
                ->copyable('Copy link')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('meeting_platform')
                ->options([
                    'google_meet' => 'Google Meet',
                    'ms_teams' => 'Microsoft Teams',
                    'zoom' => 'Zoom'
                ])
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLiveMeetings::route('/'),
            'create' => Pages\CreateLiveMeeting::route('/create'),
            'view' => Pages\ViewLiveMeeting::route('/{record}'),
            'edit' => Pages\EditLiveMeeting::route('/{record}/edit'),
        ];
    }
}
