<?php

namespace App\Livewire\Result;

use App\Models\CourseForm;
use App\Models\ResultSection;
use App\Models\Subject;
use Livewire\Component;

class StudentResults extends Component
{
    public $record;
    public $resultSections = [];
    public Subject $subject;
    public $groupId;
    public $students = [];
    public $sectionValues = []; // Holds values for ResultSections
    public $studentValues = []; // Holds values for students by ResultSection


    public function mount($record){
        $this->record = $record;
        $subject = Subject::whereId($record)->firstOrFail();
        $this->groupId = $subject->class->group->id;
        $this->resultSections = ResultSection::where('group_id', $this->groupId)->get();
        $this->students = CourseForm::where('subject_id', $subject->id)->get();

        // Initialize sectionValues and studentValues
        foreach ($this->resultSections as $section) {
            $this->sectionValues[$section->id] = ''; // Default values for sections
            foreach ($this->students as $student) {
                $this->studentValues[$student->id][$section->id] = ''; // Default values for students
            }
        }
    }

    public function saveResults()
    {
        // Save logic for result sections and student values
        // Example: Iterate and save each value
        foreach ($this->studentValues as $studentId => $sections) {
            foreach ($sections as $sectionId => $value) {
                // Save each student's value for each section
                // Replace with your logic to save results to the database
            }
        }
        session()->flash('success', 'Results saved successfully.');
    }

    public function render()
    {
        return view('livewire.result.student-results');
    }
}
