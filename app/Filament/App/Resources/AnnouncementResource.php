<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\AnnouncementResource\Pages;
use App\Filament\App\Resources\AnnouncementResource\RelationManagers;
use App\Models\Announcement;
use Daothanh\Tinymce\Forms\Components\TinymceField;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    public static function form(Form $form): Form
    {
        // from_id
        return $form
            ->schema([
                Select::make('type_of_user_sent_to')
                ->label('Message for')
                ->required()
                ->options([
                    'all' => 'All',
                    'student' => 'Student',
                    'parent' => 'Parent',
                    'teacher' => 'Teacher',
                ]),
                TextInput::make('title'),
                TextInput::make('sub')->label('Short Description')->minLength(2)
                ->maxLength(255)->required(),
                FileUpload::make('file')
    ->disk('s3'),
                TextInput::make('link')->url()
                ->suffixIcon('heroicon-m-globe-alt'),
                RichEditor::make('text')
                ->fileAttachmentsDisk('s3')->columnSpanFull()
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type_of_user_sent_to'),
                TextColumn::make('owner.name'),
                TextColumn::make('title')->searchable()
                ->description(fn (Announcement $record): string => $record->sub)->searchable(),
                TextColumn::make('created_at')
    ->since()
            ])
            ->filters([
                //
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'view' => Pages\ViewAnnouncement::route('/{record}'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
