<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\StudentAttendanceSummaryResource\Pages;
use App\Filament\Teacher\Resources\StudentAttendanceSummaryResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentAttendanceSummary;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class StudentAttendanceSummaryResource extends Resource
{
    protected static ?string $model = StudentAttendanceSummary::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::whereEmail($user->email)->first();
        return $form
            ->schema([
                Select::make('student_id')
                ->label('Student Name')
                ->options(Student::query()
                ->whereHas('class', function ($query) use ($teacher) {
                    $query->where('teacher_id', $teacher->id);
                })->pluck('name', 'id'))
                ->searchable()
                ->preload(),
                Select::make('term_id')
                ->label("Terms")
                ->options(Term::all()->pluck('name', 'id'))
                ->preload()
                ->searchable(),
                Forms\Components\Select::make('academic_id')
                    ->label('Academy Year')
                    ->options(AcademicYear::all()->pluck('title', 'id'))
                    ->preload()
                    ->searchable(),
                TextInput::make('total_present')
                ->integer()
                ->required(),
                TextInput::make('total_absent')
                ->integer()
                ->required(),
                TextInput::make('expected_present')
                ->integer()
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::whereEmail($user->email)->first();
        return $table
        ->query(  StudentAttendanceSummary::query()
        ->whereHas('student.class', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        }))
            ->columns([
                TextColumn::make('student.name')->searchable(),
                TextColumn::make('term.name')->searchable(),
                TextColumn::make('academy.title')->searchable(),
                TextColumn::make('total_present')->searchable(),
                TextColumn::make('total_absent')->searchable(),
                TextColumn::make('expected_present')->searchable()
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
            'index' => Pages\ListStudentAttendanceSummaries::route('/'),
            'create' => Pages\CreateStudentAttendanceSummary::route('/create'),
            'view' => Pages\ViewStudentAttendanceSummary::route('/{record}'),
            'edit' => Pages\EditStudentAttendanceSummary::route('/{record}/edit'),
        ];
    }
}
