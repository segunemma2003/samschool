<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\SyllabusResource\Pages;
use App\Filament\Ourstudent\Resources\SyllabusResource\RelationManagers;
use App\Models\Student;
use App\Models\Syllabus;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SyllabusResource extends Resource
{
    protected static ?string $model = Syllabus::class;

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
                $query->where('class_id', $student->class_id);
            }
        })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
                TextColumn::make('subject.subjectDepot.name')->searchable(),
                TextColumn::make('class.name')->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSyllabi::route('/'),
            'create' => Pages\CreateSyllabus::route('/create'),
            'view' => Pages\ViewSyllabus::route('/{record}'),
            'edit' => Pages\EditSyllabus::route('/{record}/edit'),
        ];
    }
}
