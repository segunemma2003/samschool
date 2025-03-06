<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\DownloadStatusResource\Pages;
use App\Filament\Teacher\Resources\DownloadStatusResource\RelationManagers;
use App\Models\DownloadStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DownloadStatusResource extends Resource
{
    protected static ?string $model = DownloadStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('time')->searchable()->sortable(),
                TextColumn::make('data')->searchable()->sortable(),
                TextColumn::make('download_links')->searchable()->sortable() ->copyable(),
                TextColumn::make('status')->searchable()->sortable(),
                TextColumn::make('error')->searchable()->sortable(),
                TextColumn::make('created_at')->since()
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
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDownloadStatuses::route('/'),
            'create' => Pages\CreateDownloadStatus::route('/create'),
            'view' => Pages\ViewDownloadStatus::route('/{record}'),
            'edit' => Pages\EditDownloadStatus::route('/{record}/edit'),
        ];
    }
}
