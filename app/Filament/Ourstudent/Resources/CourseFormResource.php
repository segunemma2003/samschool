<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\CourseFormResource\Pages;
use App\Filament\Ourstudent\Resources\CourseFormResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CourseFormResource extends Resource
{
    protected static ?string $model = CourseForm::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {

        // $userId = Auth::user()->id;
        // $user = User::where('id', $userId)->first();
        // $student = Student::where('email', $user->email)->first();
        // $subjects = Subject::where('class_id', $student->class->id)->get();
        // // dd(Subject::where('class_id', $student->class->id))

        return $form
            ->schema([
        //         Forms\Components\ViewField::make('custom_subjects_form')
        //         ->view('filament.forms.custom-subjects-form')
        //         ->viewData([
        //             'subjects' => Subject::where('class_id', $student->class->id)->get(), // Pass all subjects to the view
        //         ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
            $userId = Auth::user()->id;
            $user = User::where('id', $userId)->first();
            $student = Student::where('email', $user->email)->first();
            $academy = AcademicYear::whereStatus('true')->first();
            $query->where('student_id',  $student->id)
            ->where('academic_year_id', $academy->id)
            ->where('term_id', 1);
        })
            ->columns([

                Tables\Columns\TextColumn::make('index')
                ->rowIndex(),
            Tables\Columns\TextColumn::make('subject.subjectDepot.name')->label('Subject Name')->sortable(),
            Tables\Columns\TextColumn::make('academy.title')->label('Academic Year')->sortable(),
            Tables\Columns\TextColumn::make('term.name')->label('Term')->sortable(),
            Tables\Columns\TextColumn::make('student.name')->label('Student Name')->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                ->label('Academic Year')
                ->options(AcademicYear::pluck('title', 'id')->toArray()),

            Tables\Filters\SelectFilter::make('term_id')
                ->label('Term')
                ->options(Term::pluck('name', 'id')->toArray()),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
