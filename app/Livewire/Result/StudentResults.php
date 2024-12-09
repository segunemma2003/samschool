<?php

namespace App\Livewire\Result;

use App\Models\CourseForm;
use App\Models\ResultSection;
use App\Models\ResultSectionStudentType;
use App\Models\Subject;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class StudentResults extends Component
{
    public $record;
    public $resultSections;
    public Subject $subject;
    public $groupId;
    public $students = [];
    public $sectionValues = []; // Holds values for ResultSections
    public $studentValues = []; // Holds values for students by ResultSection
    public $liveResults = [];
    protected $listeners = ['calculateTotal'];


    public function mount($record){
        $this->record = $record;
        $subject = Subject::whereId($record)->firstOrFail();
        $this->groupId = $subject->class->group->id;
        $this->resultSections = ResultSection::where('group_id', $this->groupId)->first();
        $this->students = CourseForm::where('subject_id', $subject->id)->get();

        // Initialize sectionValues and studentValues
        foreach ($this->resultSections->resultDetails as $section) {
            $this->sectionValues[$section->id] = ''; // Default values for sections
            foreach ($this->students as $student) {
                // $this->studentValues[$student->id][$section->id] = ''; // Default values for students
                $existingResult = ResultSectionStudentType::where([
                    'result_section_type_id' => $section->id,
                    'course_form_id' => $student->id,
                ])->first();

                // Preload existing values or set default
                $this->studentValues[$student->id][$section->id] = $existingResult ? $existingResult->score : '';

            }
        }
    }

    public function calculateGradeLevel($total)
    {
        if ($total >= 75) return 'A1';
        if ($total >= 70) return 'B2';
        if ($total >= 65) return 'B3';
        if ($total >= 61) return 'C4';
        if ($total >= 55) return 'C5';
        if ($total >= 50) return 'C6';
        if ($total >= 45) return 'D7';
        if ($total >= 40) return 'E8';
        return 'F9';
    }


    public function remarksStatement($total)
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

    #[On('calculateTotal')]
    public function calculateTotal($studentId)
    {
        Log::info('Calculating total for student ID: ' . $studentId);

        // Step 1: Recalculate totals for all students
        $studentTotals = [];
        foreach ($this->students as $student) {
            $studentTotal = 0;
            foreach ($this->resultSections->resultDetails as $section) {
                if ($section->calc_pattern == 'input' && isset($this->studentValues[$student->id][$section->id])) {
                    $studentTotal += (float) $this->studentValues[$student->id][$section->id];
                }
            }
            $studentTotals[$student->id] = $studentTotal;
        }

        // Step 2: Filter totals to include only students with a total value
        $validTotals = array_filter($studentTotals, function ($total) {
            return $total > 0; // Consider only students with a total greater than zero
        });

        // Step 3: Sort valid totals in descending order and assign positions
        arsort($validTotals); // Sort scores in descending order while preserving keys
        $positions = [];
        $rank = 1;
        $previousScore = null;
        $tieCount = 0;

        foreach ($validTotals as $studentIdKey => $score) {
            if ($score !== $previousScore) {
                $rank += $tieCount; // Skip ranks for ties
                $tieCount = 0; // Reset tie count
            } else {
                $tieCount++;
            }
            $positions[$studentIdKey] = $rank;
            $previousScore = $score;
        }

        // Step 4: Calculate class metrics for students with valid totals
        $classAverage = count($validTotals) > 0 ? array_sum($validTotals) / count($validTotals) : 0;
        $classHighestScore = count($validTotals) > 0 ? max($validTotals) : 0;
        $classLowestScore = count($validTotals) > 0 ? min($validTotals) : 0;

        // Step 5: Update student values for all relevant metrics
        foreach ($this->students as $student) {
            foreach ($this->resultSections->resultDetails as $section) {
                if ($section->calc_pattern == 'position') {
                    $this->studentValues[$student->id][$section->id] = $positions[$student->id] ?? null;
                }
                if ($section->calc_pattern == 'total') {
                    $this->studentValues[$student->id][$section->id] = $studentTotals[$student->id];
                }
                if ($section->calc_pattern == 'class_average') {
                    $this->studentValues[$student->id][$section->id] = $classAverage;
                }
                if ($section->calc_pattern == 'class_highest_score') {
                    $this->studentValues[$student->id][$section->id] = $classHighestScore;
                }
                if ($section->calc_pattern == 'class_lowest_score') {
                    $this->studentValues[$student->id][$section->id] = $classLowestScore;
                }
                if ($section->calc_pattern == 'grade_level') {
                    $this->studentValues[$student->id][$section->id] = $this->calculateGradeLevel($studentTotals[$student->id]);
                }

                if ($section->calc_pattern == 'remarks') {
                    $this->studentValues[$student->id][$section->id] = $this->remarksStatement($studentTotals[$student->id]);
                }
            }
        }

        Log::info('Metrics updated for all students with valid totals');
    }




    public function saveResults()
    {
        foreach ($this->studentValues as $studentId => $sections) {
            foreach ($sections as $sectionId => $value) {
                // Use updateOrCreate to handle both update and create operations
                ResultSectionStudentType::updateOrCreate(
                    [
                        'result_section_type_id' => $sectionId,
                        'course_form_id' => $studentId,
                    ],
                    [
                        'score' => $value,
                    ]
                );
            }
        }

        Notification::make()
            ->title('Success')
            ->body('Results saved successfully.')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.result.student-results');
    }
}
