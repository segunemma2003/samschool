<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\SubjectResource\Pages;
use App\Filament\Ourstudent\Resources\SubjectResource\RelationManagers;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

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
        ->modifyQueryUsing(function (Builder $query) {
            $user = User::whereId(request()->user()->id)->first();
            $student = Student::whereEmail($user->email)->first();

            if ($student) {
                $query->whereHas('subjectDepot', function ($q) use ($student) {
                    $q->where('class_id', $student->class_id);
                });
            }
        })
            ->columns([
                TextColumn::make('subjectDepot.name')
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'view' => Pages\ViewSubject::route('/{record}'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
