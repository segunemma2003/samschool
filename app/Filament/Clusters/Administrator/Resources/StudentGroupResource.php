<?php

namespace App\Filament\Clusters\Administrator\Resources;

use App\Filament\Clusters\Administrator;
use App\Filament\Clusters\Administrator\Resources\StudentGroupResource\Pages;
use App\Filament\Clusters\Administrator\Resources\StudentGroupResource\RelationManagers;
use App\Models\StudentGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentGroupResource extends Resource
{
    protected static ?string $model = StudentGroup::class;

    protected static ?string $navigationIcon = 'heroicon-m-user-group';

    protected static ?string $cluster = Administrator::class;

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
            'index' => Pages\ListStudentGroups::route('/'),
            'create' => Pages\CreateStudentGroup::route('/create'),
            'view' => Pages\ViewStudentGroup::route('/{record}'),
            'edit' => Pages\EditStudentGroup::route('/{record}/edit'),
        ];
    }
}
