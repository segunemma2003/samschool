<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\AnnouncementResource\Pages;
use App\Filament\Teacher\Resources\AnnouncementResource\RelationManagers;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
            $userId = Auth::user()->id;
            $query->where('type_of_user_sent_to', "teacher")
            ->orWhere('from_id', $userId)
            ->orWhere('type_of_user_sent_to', "all");
        })
            ->columns([
                // TextColumn::make('type_of_user_sent_to'),
                TextColumn::make('owner.name')->label("Sender"),
                // ImageColumn::make('file')->disk('cloudinary'),
                TextColumn::make('title')->searchable()
                ->description(fn (Announcement $record): string => $record->sub)->searchable(),
                TextColumn::make('created_at')
    ->since()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
            'view' => Pages\Announcement::route('/{record}')
        ];
    }
}
