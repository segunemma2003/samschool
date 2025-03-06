<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\ExamResource\Pages;
use App\Filament\Teacher\Resources\ExamResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ResultSectionType;
use App\Models\SchoolSection;
use App\Models\Subject;
use App\Models\Term;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Select::make('subject_id')
                ->options(Subject::all()->pluck('code', 'id'))
                ->preload()
                ->live()
                ->label("Subject")
                ->searchable(),
                DatePicker::make('exam_date')
                ->required(),
                TextInput::make('duration')
                ->label('Duration (minutes)')
                ->numeric()
                ->minValue(0)
                ->suffix('minutes')
                ->required(),
                Select::make('is_set')
                ->options([
                    true => 'Yes',    // Label "Yes" for true
                    false => 'No',    // Label "No" for false
                ])
                ->preload()
                ->default(false)
                ->label('Exam is Set')
                ->searchable(),
                Select::make('term_id')
                ->options(Term::all()->pluck('name', 'id'))
                ->preload()
                ->searchable()
                ->label("Term")
                ->required(),
                Select::make('assessment_type')
                ->options([
                    "test"=>"Test",
                    "exam"=>"Exam"
                ])
                ->preload()
                ->label("Assessment Type")
                ->searchable(),

                Select::make('result_section_type_id')
                ->options(function (callable $get) {
                    $subjectId = $get('subject_id');

                    if (!$subjectId) {
                        return [];
                    }

                    // Fetch the subject and its associated class group
                    $subject = Subject::with('class.group')->find($subjectId);

                    if (!$subject || !$subject->class || !$subject->class->group) {
                        return [];
                    }

                    // Fetch result section types matching the class group
                    return ResultSectionType::whereHas('resultSection.group', function ($query) use ($subject) {
                        $query->where('name', $subject->class->group->name);
                    })->pluck('code', 'id');
                })
                ->preload()
                ->label("Assessment Type Details")
                ->searchable()
                ->live()
                ->required(),

                TextInput::make('total_score')
                ->required()
                ->integer(),
                RichEditor::make('instructions')
                ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        $academy = AcademicYear::whereStatus('true')->first();
        $term = Term::whereStatus('true')->first();

        // Start with the base query
        $query = Exam::query();

        // Apply filters separately based on whether $academy and $term are available
        // if ($academy) {
        //     $query->where('academic_year_id', $academy->id);
        // }

        // if ($term) {
        //     $query->where('term_id', $term->id);
        // }

        return $table
            ->query($query) // Apply the filtered query to the table
            ->columns([
                TextColumn::make('academic.title')->searchable(),
                TextColumn::make('term.name')->searchable()->default('Term 1'),
                TextColumn::make('subject.code')->searchable(),
                TextColumn::make('subject.class.name')->searchable(),
            ])
            ->filters([
                // Filter for Academic Year with default value
                SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(AcademicYear::pluck('title', 'id'))
                    ->default($academy?->id),// Set default if an active academic year exists
                //     ->query(function ($query, $value) {
                //         $query->where('academic_year_id', $value);
                //     }),

                // // Filter for Term with default value
                SelectFilter::make('term_id')
                    ->label('Term')
                    ->options(Term::pluck('name', 'id'))
                    ->default($term?->id) // Set default if an active term exists
                //     ->query(function ($query, $value) {
                //         $query->where('term_id', $value);
                //     }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view-students')
                    ->label('View Students')
                    ->url(fn ($record) => static::getUrl('view-students', ['record' => $record->getKey()]))
                    ->icon('heroicon-o-user-group'),
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
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'view' => Pages\ViewExam::route('/{record}'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
            'view-students'=>  Pages\ViewExamStudent::route('/students/{record}'),
            'exam-student-details' => Pages\ExamStudentDetails::route('/exam-student-details/{quizScoreId}'),
        ];
    }
}
