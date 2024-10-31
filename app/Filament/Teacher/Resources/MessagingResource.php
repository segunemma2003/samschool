<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\MessagingResource\Pages;
use App\Filament\Teacher\Resources\MessagingResource\RelationManagers;
use App\Models\Messaging;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MessagingResource extends Resource
{
    protected static ?string $model = \SevenSpan\Chat\Models\Channel::class;

    protected static ?string $label = "Message";

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Conversation Name'),
                TextColumn::make('last_message')->label('Last Message')->limit(50),
                TextColumn::make('updated_at')->label('Last Updated')->since(),
            ])
            ->filters([
                Filter::make('Inbox')
                    ->query(fn ($query) => $query->whereHas('messages', fn ($query) => $query->where('recipient_id', Auth::id()))),
                Filter::make('Sent')
                    ->query(fn ($query) => $query->whereHas('messages', fn ($query) => $query->where('sender_id', Auth::id()))),
                Filter::make('Unread')
                    ->query(fn ($query) => $query->whereHas('messages', fn ($query) => $query->where('read_at', null)->where('recipient_id', Auth::id()))),
                Filter::make('Group')
                    ->query(fn ($query) => $query->where('is_group', true)),
            ])
            ->actions([
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
            'index' => Pages\ListMessagings::route('/'),
            'create' => Pages\CreateMessaging::route('/create'),
            'edit' => Pages\EditMessaging::route('/{record}/edit'),
        ];
    }
}
