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
    public $subjects;
    public $selectedSubjects = [];
    public $record;
    public $terms;
    public $student;
    public $selectAll = false;





    public function mount($record)
    {
        $this->record = $record;
        // $this->classId = null;
        // $this->termId = null;
        $this->subjects = collect();
        $this->terms = Term::all();
        $this->student = Student::whereId($record)->first();
        $this->loadSubjects($record);
        $academy = AcademicYear::whereStatus('true')->first();
        $academyId = $academy->id;
        $this->selectedSubjects = CourseForm::where('student_id', $record)
        ->where('term_id', $this->termId )
        ->where('academic_year_id', $academyId)
        ->pluck('subject_id')
        ->toArray();
         // Preload selected subjects for this student

    }


    public function loadSubjects($id)
    {

        $student = Student::where('id', $id)->first();

        if ($student) {
            $query = Subject::where('class_id', $student->class->id);
            $academy = AcademicYear::whereStatus('true')->first();
        $academyId = $academy->id ?? 1;
            // if ($this->termId) {
            //     $query->where('term_id', $this->termId);
            // }
            $this->subjects = $query->get();
            $this->selectedSubjects = CourseForm::where('student_id', $student->id)
            ->where('academic_year_id', $academyId)
            ->where('term_id', $this->termId )
            ->pluck('subject_id')
            ->toArray();
        }else {
            $this->subjects = collect(); // Ensure it's a collection if no subjects are found
            $this->selectedSubjects = [];
        }
    }

    public function updatedSelectAll($value)
    {
        // dd($value);
        if ($value) {
            // Ensure array updates are detected by Livewire
            $this->selectedSubjects = $this->subjects->pluck('id')->toArray();
        } else {
            // Deselect only subjects not stored in DB
            $this->selectedSubjects = CourseForm::where('student_id', $this->record)
                ->where('academic_year_id', AcademicYear::whereStatus('true')->first()->id ?? 1)
                ->where('term_id', $this->termId ?? 1)
                ->pluck('subject_id')
                ->toArray();
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

        $existingSubjects = CourseForm::where('student_id', $this->record)
            ->where('academic_year_id', $academyId)
            ->where('term_id', $this->termId ?? 1)
            ->pluck('subject_id')
            ->toArray();
            $subjectsToAdd = array_diff($this->selectedSubjects, $existingSubjects);
            $subjectsToRemove = array_diff($existingSubjects, $this->selectedSubjects);
            foreach ($subjectsToAdd as $subjectId) {
                CourseForm::firstOrCreate([
                    'student_id' => $this->record,
                    'subject_id' => $subjectId,
                    'academic_year_id' => $academyId,
                    'term_id' => $this->termId,
                ]);
            }

            if(count($subjectsToRemove) > 0){
             // Remove unselected subjects
                CourseForm::where('student_id', $this->record)
                ->where('academic_year_id', $academyId)
                ->where('term_id', $this->termId ?? 1)
                ->whereIn('subject_id', $subjectsToRemove)
                ->delete();
            }
        // foreach ($this->selectedSubjects as $subjectId) {
        //     // Check if the course is already registered for this student in the same term and academic year
        //     $existingCourse = CourseForm::where('student_id', $this->record)
        //         ->where('subject_id', $subjectId)
        //         ->where('academic_year_id', $academyId)
        //         ->where('term_id', $this->termId ?? 1)
        //         ->exists();

        //     if (!$existingCourse) {
        //         CourseForm::create([
        //             'student_id' => $this->record,
        //             'subject_id' => $subjectId,
        //             'academic_year_id' => $academyId,
        //             'term_id' => $this->termId ?? 1,
        //         ]);
        //     }
        // }

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
