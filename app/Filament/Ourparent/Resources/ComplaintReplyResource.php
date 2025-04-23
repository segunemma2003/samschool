<?php

namespace App\Filament\Ourparent\Resources;

use App\Filament\Ourparent\Resources\ComplaintReplyResource\Pages;
use App\Filament\Ourparent\Resources\ComplaintReplyResource\RelationManagers;
use App\Models\ComplaintReply;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComplaintReplyResource extends Resource
{
    protected static ?string $model = ComplaintReply::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                //
            ])
            ->filters([
                //
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
            'index' => Pages\ListComplaintReplies::route('/'),
            'create' => Pages\CreateComplaintReply::route('/create'),
            'edit' => Pages\EditComplaintReply::route('/{record}/edit'),
        ];
    }
}
