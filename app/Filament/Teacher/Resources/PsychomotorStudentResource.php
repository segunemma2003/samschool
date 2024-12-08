<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\PsychomotorStudentResource\Pages;
use App\Filament\Teacher\Resources\PsychomotorStudentResource\RelationManagers;
use App\Models\PyschomotorStudent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PsychomotorStudentResource extends Resource
{
    protected static ?string $model = PyschomotorStudent::class;

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
            'index' => Pages\ListPsychomotorStudents::route('/'),
            'create' => Pages\CreatePsychomotorStudent::route('/create'),
            'view' => Pages\ViewPsychomotorStudent::route('/{record}'),
            'edit' => Pages\EditPsychomotorStudent::route('/{record}/edit'),
            'view-student-psych' => Pages\PsychomotorStudentDetails::route('/student/{record}/psychom')
        ];
    }
}
