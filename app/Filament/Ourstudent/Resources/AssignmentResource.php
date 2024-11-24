<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\AssignmentResource\Pages;
use App\Filament\Ourstudent\Resources\AssignmentResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Student;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

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
            $query->whereHas('subject.courseOffer', function ($subQuery) use ($student, $academicYearId) {
                $subQuery->where('student_id', $student->id)
                         ->where('academic_year_id', $academicYearId);
            })->with(['subject', 'class', 'section', 'students']);
        }
    })
        ->columns([
            TextColumn::make('title'),
            TextColumn::make('class.name'),
            TextColumn::make('section.section'),
            TextColumn::make('deadline'),
            TextColumn::make('student_status')
                ->label('Total Score')
                ->formatStateUsing(function ($record) use ($student) {
                    $pivot = $record->students()->find($student->id)?->pivot;

                    if (!$pivot) {
                        return 'Not Answered';
                    }

                    return is_null($pivot->comments_score)
                        ? ($pivot->status === 'draft' ? 'Draft' : 'Awaiting Score')
                        : 'Marked';
                })
                ->sortable(),
            TextColumn::make('score_only')
                ->label('Score')
                ->formatStateUsing(function ($record) use ($student) {
                    $pivot = $record->students()->find($student->id)?->pivot;

                    return $pivot && !is_null($pivot->comments_score)
                        ? $pivot->total_score
                        : '-';
                }),
            TextColumn::make('created_at')->since(),
        ])
        ->filters([
            // Add filters if needed
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
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'view' => Pages\ViewAssignment::route('/{record}'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }
}
