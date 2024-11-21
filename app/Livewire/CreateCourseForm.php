<?php

namespace App\Livewire;

use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateCourseForm extends Component
{

    public $classId;
    public $termId;
    public $subjects = [];
    public $selectedSubjects = [];

    public function mount()
    {
        $this->classId = null;
        $this->termId = null;
        $this->loadSubjects();
    }

    public function loadSubjects()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

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
        $this->loadSubjects();
    }

    public function updatedTermId()
    {
        $this->loadSubjects();
    }

    public function create()
        {
            $validated = $this->validate([
                'selectedSubjects' => 'required|array|min:1',
                'selectedSubjects.*' => 'exists:subjects,id',
            ]);

            $user = Auth::user();
            $student = Student::where('email', $user->email)->first();
            $academy = AcademicYear::whereStatus('true')->first();
            $academyId = $academy->id ?? 1;

            foreach ($this->selectedSubjects as $subjectId) {
                // Check if the course is already registered for this student in the same term and academic year
                $existingCourse = CourseForm::where('student_id', $student->id)
                    ->where('subject_id', $subjectId)
                    ->where('academic_year_id', $academyId)
                    ->where('term_id', $this->termId ?? 1)
                    ->exists();

                if (!$existingCourse) {
                    CourseForm::create([
                        'student_id' => $student->id,
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
            $this->loadSubjects();
        }

    public function render()
    {
        return view('livewire.create-course-form',[
            'terms' => Term::all(),
        ]);
    }
}
