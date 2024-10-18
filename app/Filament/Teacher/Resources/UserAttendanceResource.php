<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\UserAttendanceResource\Pages;
use App\Filament\Teacher\Resources\UserAttendanceResource\RelationManagers;
use App\Models\UserAttendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserAttendanceResource extends Resource
{
    protected static ?string $model = UserAttendance::class;

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
            'index' => Pages\ListUserAttendances::route('/'),
            'create' => Pages\CreateUserAttendance::route('/create'),
            'edit' => Pages\EditUserAttendance::route('/{record}/edit'),
        ];
    }
}
