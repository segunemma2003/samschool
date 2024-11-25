<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\CourseFormResource\Pages;
use App\Filament\Teacher\Resources\CourseFormResource\RelationManagers;
use App\Models\CourseForm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseFormResource extends Resource
{
    protected static ?string $model = CourseForm::class;

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
            'index' => Pages\ListCourseForms::route('/'),
            'create' => Pages\CreateCourseForm::route('/create'),
            'view' => Pages\ViewCourseForm::route('/{record}'),
            'edit' => Pages\EditCourseForm::route('/{record}/edit'),
        ];
    }
}
