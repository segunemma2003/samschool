<?php

namespace App\Livewire;

use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use Filament\Notifications\Notification;
use Livewire\Component;

class CourseFormStudent extends Component
{
    public $classId;
    public $termId;
    public $subjects = [];
    public $selectedSubjects = [];
    public $record;
    public $terms;
    public $student;

    public function mount($record)
    {
        $this->record = $record;
        $this->classId = null;
        $this->termId = null;
        $this->terms = Term::all();
        $this->student = Student::whereId($record)->first();
        $this->loadSubjects($record);
        $academy = AcademicYear::whereStatus('true')->first();
        $academyId = $academy->id ?? 1;

         // Preload selected subjects for this student

    }


    public function loadSubjects($id)
    {

        $student = Student::where('id', $id)->first();

        if ($student) {
            $query = Subject::where('class_id', $student->class->id);
            // if ($this->termId) {
            //     $query->where('term_id', $this->termId);
            // }
            $this->subjects = $query->get();
        }
    }

    public function updatedClassId()
    {
        $this->loadSubjects($this->record);
    }

    public function updatedTermId()
    {
        $this->loadSubjects($this->record);
    }


    public function create()
    {
        $validated = $this->validate([
            'selectedSubjects' => 'required|array|min:1',
            'selectedSubjects.*' => 'exists:subjects,id',
        ]);


        $academy = AcademicYear::whereStatus('true')->first();
        $academyId = $academy->id ?? 1;

        foreach ($this->selectedSubjects as $subjectId) {
            // Check if the course is already registered for this student in the same term and academic year
            $existingCourse = CourseForm::where('student_id', $this->record)
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $academyId)
                ->where('term_id', $this->termId ?? 1)
                ->exists();

            if (!$existingCourse) {
                CourseForm::create([
                    'student_id' => $this->record,
                    'subject_id' => $subjectId,
                    'academic_year_id' => $academyId,
                    'term_id' => $this->termId ?? 1,
                ]);
            }
        }

        Notification::make()
            ->title('Success')
            ->body('Course form successfully created! Skipped already registered courses.')
            ->success()
            ->send();

        $this->reset(['selectedSubjects']);
        $this->loadSubjects($this->record);
    }


    public function render()
    {
        return view('livewire.course-form-student');
    }
}
