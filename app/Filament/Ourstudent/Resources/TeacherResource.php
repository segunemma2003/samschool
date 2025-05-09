<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\TeacherResource\Pages;
use App\Filament\Ourstudent\Resources\TeacherResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

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
        $user = Auth::user();
        $academicYear = AcademicYear::whereStatus('true')->first();
        $academicYearId = $academicYear->id ?? null;
    // dd($user);
        if (!$user) {
            return $table;
        }

        $student = Student::whereEmail($user->email)->first();

        return $table
        ->modifyQueryUsing(function (Builder $query) use ($student, $academicYearId) {
            if ($student && $academicYearId) {
                $query->whereHas('subject', function ($subQuery) use ($student, $academicYearId) {
                    $subQuery->where('class_id', $student->class_id);
                })->with(['subject']);
            }
        })
            ->columns([
                TextColumn::make('No')->rowIndex(),
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('phone'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'view' => Pages\ViewTeacher::route('/{record}'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
