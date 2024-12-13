<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\ResultSectionType;
use App\Models\Student;
use App\Models\Term;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Component;

class StudentResultDetailsPage extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $terms;
    public $termId;
    public $classId;
    public $academic;
    public $courses = [];
    public $record;
    public $academicYears;
    public $student;
    public $total;
    public $totalSubject;
    public $average;

    public function mount($record)
    {
        $this->record = $record;
        $this->terms = Term::all();
        $this->academicYears = AcademicYear::all();
        $this->termId = Term::query()->first()?->id; // Default to the first term
        $this->academic = AcademicYear::query()->where('status', "true")->first()?->id; // Default to active academic year
        $student = Student::where('id', $record)->first();
        $this->classId = $student->class->group->id;
    }

    public function table(Table $table): Table
    {
        // dd($this->record);
        return $table
            ->query(
                CourseForm::query()
                    ->where('student_id', $this->record)
            )
            ->columns([
                TextColumn::make('subject.subjectDepot.name')->label('Course Name'),
                ...$this->getDynamicScoreBoardColumns()

            ])
            ->filters([
                SelectFilter::make('term_id')
                    ->label('Term')
                    ->options($this->terms->pluck('name', 'id')->toArray())
                    ->default($this->termId)
                    ->searchable(),

                SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options($this->academicYears->pluck('title', 'id')->toArray())
                    ->default($this->academic)
                    ->searchable()
            ])
            ->actions([])
            ->bulkActions([]);
    }


    public function updated($property)
    {
        if (in_array($property, ['termId', 'academic'])) {
            $this->calculateTotals();
        }
    }

    public function calculateTotals()
    {
        $courseForms = CourseForm::query()
            ->where('student_id', $this->record)
            ->where('term_id', $this->termId)
            ->where('academic_year_id', $this->academic)
            ->get();

        $this->total = $courseForms->reduce(function ($carry, $courseForm) {
            return $carry + $courseForm->scoreBoard
                ->where('resultSectionType.calc_pattern', 'total')
                ->sum('score');
        }, 0);

        $this->totalSubject = $courseForms->count();

        $this->average = $this->totalSubject > 0
            ? round($this->total / $this->totalSubject, 2)
            : 0;
    }
    protected function remarksStatement($total)
    {
        if ($total >= 80) return 'EXCELLENT';
        if ($total >= 70) return 'VERY GOOD';
        if ($total >= 65) return 'GOOD';
        if ($total >= 61) return 'CREDIT';
        if ($total >= 55) return 'CREDIT';
        if ($total >= 50) return 'CREDIT';
        if ($total >= 45) return 'PASS';
        if ($total >= 40) return 'FAIL';
        return 'FAIL';
    }

    protected function getDynamicScoreBoardColumns(): array
{
    // Get the dynamic fields from the related ResultSectionTypeModel
        $dynamicFields = ResultSectionType::query()
        ->whereHas('resultSection', function ($query) {
            $query->where('group_id', $this->classId);
        })
        ->get(['id', 'name']);


// dd(count($dynamicFields));
    // Return mapped columns
    return $dynamicFields->map(function ($field) {
        return TextColumn::make("scoreBoard.{$field->id}")
            ->label($field->name)
            ->formatStateUsing(function ($record) use ($field) {
                // Extract score for the field
                $score = $record->scoreBoard
                    ->where('result_section_type_id', $field->id)
                    ->pluck('score')
                    ->join(', ');
                // $score = $field->id;
                return $score;
            });
    })->toArray();

}
    public function render()
    {
        return view('livewire.teacher.student-result-details-page');
    }
}
