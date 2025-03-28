<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\ResultSectionType;
use App\Models\Student;
use App\Models\StudentComment;
use App\Models\Term;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Request;
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
    public $comment;
    public ?array $data = [];

    public function mount($record)
    {
        $this->record = $record;
        $this->terms = Term::all();
        $this->academicYears = AcademicYear::all();
        $termId = Term::where('status', "true")->first();

        $this->termId = $termId?->id; // Default to the first term
        // dd($this->termId);
        $this->academic = AcademicYear::query()->where('status', "true")->first()?->id; // Default to active academic year
        $this->student = Student::where('id', $record)->first();
        $this->classId = $this->student->class->group->id;

       $this->loadComment();
       $this->getDynamicScoreBoardColumns($this->termId);
    }

    public function loadComment($termId=null)
    {
        $termId = is_null($termId) ? $this->termId : $termId;
        $academicId = is_null($this->academic) ? AcademicYear::where('status', "true")->first()?->id : $this->academic;
        // dd($this->termId);
        $studentComment = StudentComment::query()
            ->where('student_id', $this->student->id)
            ->where('term_id', $termId)
            ->where('academic_id', $this->academic)
            ->first();

        // dd( $studentComment);
        $this->comment =$studentComment?->comment ?? '';
        $this->form->fill([
            'comment' => $studentComment?->comment ?? '', // Load the comment or set empty
        ]);
    }


    public function form(Form $form): Form
    {

        return $form
        ->schema([
            MarkdownEditor::make('comment'),

        ])
        ->statePath('data');
    }
    public function saveComment()
    {

        $validatedData = $this->form->getState();
        // dd($validatedData);
        // Check if a comment already exists
        $studentComment = StudentComment::query()
            ->where('student_id', $this->student->id)
            ->where('term_id', $this->termId)
            ->where('academic_id', $this->academic)
            ->first();

            StudentComment::updateOrCreate(
                [
                    'student_id' => $this->student->id,
                    'term_id' => $this->termId, // Replace with your term ID logic
                    'academic_id' => $this->academic, // Replace with your academic ID logic
                ],
                [
                    'comment' => $validatedData['comment'],
                ]
            );


        Notification::make()->title('Comment saved successfully!')->success()->send();

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
                    ->searchable()

              ,

                SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options($this->academicYears->pluck('title', 'id')->toArray())
                    ->default($this->academic)

                    ->searchable()
                    ,
            ])
            ->actions([])
            ->bulkActions([]);
    }


    public function updated($property)
    {


        // dd($this->termId);
        if (in_array($property, ['termId', 'academic'])) {

            $this->getDynamicScoreBoardColumns();
            $this->calculateTotals();
            $this->loadComment();
        }
    }

    public function updatedTableFilters($filters)
    {
        $updates = request('components.0.updates', []);
        // dd($updates);
        if (isset($updates['tableFilters.term_id.value'])) {
            $this->termId = $updates['tableFilters.term_id.value'];

        }

        if (isset($updates['tableFilters.academic_year_id.value'])) {
            $this->academic = $updates['tableFilters.academic_year_id.value'];
        }


        $this->getDynamicScoreBoardColumns($updates['tableFilters.term_id.value']);
        $this->calculateTotals($updates['tableFilters.term_id.value']);
        $this->loadComment($updates['tableFilters.term_id.value']); // Reload comment when filters change
    }

    public function calculateTotals($termId=null)
    {
        $termId = is_null($termId) ? $this->termId : $termId;
        $academicId = is_null($this->academic) ? AcademicYear::where('status', "true")->first()?->id : $this->academic;
        $courseForms = CourseForm::query()
            ->where('student_id', $this->record)
            ->where('term_id', $termId)
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

    protected function getDynamicScoreBoardColumns($termId=null): array
    {
        $termId = is_null($termId) ? $this->termId : $termId;
        $academicId = is_null($this->academic) ? AcademicYear::where('status', "true")->first()?->id : $this->academic;

        $dynamicFields = ResultSectionType::where('term_id', $termId)
            ->whereHas('resultSection', function ($query) {
                $query->where('group_id', $this->classId);
            })

            ->get(['id', 'name']);

        return $dynamicFields->map(function ($field) {
            return TextColumn::make("scoreBoard.{$field->id}")
                ->label($field->name)
                ->state(function ($record) use ($field) {
                    $score = $record->scoreBoard
                        ->where('result_section_type_id', $field->id)
                        ->pluck('score')
                        ->first();

                    return $score ?? 'N/A';
                });
        })->toArray();
    }

    public function render()
    {
        return view('livewire.teacher.student-result-details-page');
    }
}
