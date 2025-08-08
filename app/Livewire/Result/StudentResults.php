<?php

namespace App\Livewire\Result;

use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\ResultSection;
use App\Models\ResultSectionStudentType;
use App\Models\Subject;
use App\Models\Term;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class StudentResults extends Component
{
    public $record;
    public $resultSections;
    public Subject $subject;
    public $termId;
    public $terms;
    public $academic;
    public $academicYears;
    public $groupId;
    public $students = [];
    public $sectionValues = []; // Holds values for ResultSections
    public $studentValues = []; // Holds values for students by ResultSection
    public $liveResults = [];
    public $resTitle = [];
    protected $listeners = ['calculateTotal'];


    public function mount($record){
        $this->record = $record;
        $subject = Subject::whereId($record)->firstOrFail();
        $this->subject = $subject;
        $this->groupId = $subject->class->group->id;
        $this->terms = Term::all();
        $this->academicYears = AcademicYear::all();
        $this->termId = Term::query()->first()?->id; // Default to the first term
        $this->academic = AcademicYear::query()->where('status', "true")->first()?->id;
        $this->resultSections = ResultSection::where('group_id', $this->groupId)->first();
        $this->loadSubjects();

    }

    public function loadSubjects(){
        $this->resTitle =  $this->resultSections->resultDetails()->where('term_id', $this->termId)->get();
        // dd($this->resTitle);
        $this->students = CourseForm::where('subject_id', $this->subject->id)
        ->where('term_id', $this->termId)
        ->where('academic_year_id', $this->academic)
        ->get();

        // Initialize sectionValues and studentValues
        // foreach ($this->resultSections->resultDetails as $section) {
            foreach ($this->resTitle as $section) {
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

    public function updatedAcademic()
    {
        $this->loadSubjects();
    }

    public function updatedTermId()
    {
        $this->loadSubjects();
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

    public function calculateCumulativeScore($studentId, $sectionId)
    {
        try {
            // Get the current term and academic year
            $currentTerm = \App\Models\Term::find($this->termId);
            $currentAcademic = \App\Models\AcademicYear::find($this->academic);

            if (!$currentTerm || !$currentAcademic) {
                return 0;
            }

            // Get all terms in the academic year, ordered by their sequence
            $terms = \App\Models\Term::all(); // Get all terms since terms don't have academic_year_id

            // Find the current term's position
            $currentTermIndex = $terms->search(function ($term) {
                return $term->id === $this->termId;
            });

            if ($currentTermIndex === false || $currentTermIndex === 0) {
                // If this is the first term or term not found, return 0
                return 0;
            }

            // Get previous terms (all terms before the current one)
            $previousTerms = $terms->take($currentTermIndex);

            $totalScores = [];
            $totalCount = 0;

            foreach ($previousTerms as $term) {
                // Get the course form for this student, subject, and term
                $courseForm = \App\Models\CourseForm::where([
                    'student_id' => $studentId,
                    'subject_id' => $this->subject->id,
                    'term_id' => $term->id,
                    'academic_year_id' => $currentAcademic->id
                ])->first();

                if ($courseForm) {
                    // Get all input scores for this course form
                    $inputScores = \Illuminate\Support\Facades\DB::table('result_section_student_types')
                        ->join('result_section_types', 'result_section_student_types.result_section_type_id', '=', 'result_section_types.id')
                        ->where('result_section_student_types.course_form_id', $courseForm->id)
                        ->where('result_section_types.calc_pattern', 'input')
                        ->sum('result_section_student_types.score');

                    if ($inputScores > 0) {
                        $totalScores[] = (float) $inputScores;
                        $totalCount++;
                    }
                }
            }

            // Calculate average of all previous term totals
            if ($totalCount > 0) {
                $cumulativeAverage = array_sum($totalScores) / $totalCount;
                return round($cumulativeAverage, 2);
            }

            return 0;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error calculating cumulative score', [
                'student_id' => $studentId,
                'section_id' => $sectionId,
                'error' => $e->getMessage()
            ]);

            return 0;
        }
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
        if ($total >= 39) return 'FAIL';
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
            foreach ($this->resTitle as $section) {
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

        // Check if the result is a whole number
        $classAverage = fmod($classAverage, 1) === 0.0
            ? (int) $classAverage // Convert to an integer if it has no decimal part
            : round($classAverage, 1); // Otherwise, round to 1 decimal place
        $classHighestScore = count($validTotals) > 0 ? max($validTotals) : 0;
        $classLowestScore = count($validTotals) > 0 ? min($validTotals) : 0;

        // Step 5: Update student values for all relevant metrics
        foreach ($this->students as $student) {
            foreach ($this->resTitle as $section) {
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
                if ($section->calc_pattern == 'cumulative') {
                    $this->studentValues[$student->id][$section->id] = $this->calculateCumulativeScore($student->id, $section->id);
                }
            }
        }

        Log::info('Metrics updated for all students with valid totals');
    }




    public function calculateTotalsForAllStudents()
    {
        // Step 1: Calculate raw totals for all students first
        $studentTotals = [];
        foreach ($this->students as $student) {
            $studentTotal = 0;
            foreach ($this->resTitle as $section) {
                if ($section->calc_pattern == 'input' && isset($this->studentValues[$student->id][$section->id])) {
                    $studentTotal += (float) $this->studentValues[$student->id][$section->id];
                }
            }
            $studentTotals[$student->id] = $studentTotal;
        }

        // Filter, rank, and calculate class metrics in one pass
        $validTotals = array_filter($studentTotals, fn($total) => $total > 0);
        arsort($validTotals);

        // Calculate class metrics once
        $classAverage = count($validTotals) > 0 ? array_sum($validTotals) / count($validTotals) : 0;
        $classAverage = fmod($classAverage, 1) === 0.0 ? (int) $classAverage : round($classAverage, 1);
        $classHighestScore = count($validTotals) > 0 ? max($validTotals) : 0;
        $classLowestScore = count($validTotals) > 0 ? min($validTotals) : 0;

        // Assign positions
        $positions = [];
        $rank = 1;
        $previousScore = null;
        $tieCount = 0;

        foreach ($validTotals as $studentIdKey => $score) {
            if ($score !== $previousScore) {
                $rank += $tieCount;
                $tieCount = 0;
            } else {
                $tieCount++;
            }
            $positions[$studentIdKey] = $rank;
            $previousScore = $score;
        }

        // Update all students' calculated fields in one go
        foreach ($this->students as $student) {
            foreach ($this->resTitle as $section) {
                switch ($section->calc_pattern) {
                    case 'position':
                        $this->studentValues[$student->id][$section->id] = $positions[$student->id] ?? null;
                        break;
                    case 'total':
                        $this->studentValues[$student->id][$section->id] = $studentTotals[$student->id];
                        break;
                    case 'class_average':
                        $this->studentValues[$student->id][$section->id] = $classAverage;
                        break;
                    case 'class_highest_score':
                        $this->studentValues[$student->id][$section->id] = $classHighestScore;
                        break;
                    case 'class_lowest_score':
                        $this->studentValues[$student->id][$section->id] = $classLowestScore;
                        break;
                    case 'grade_level':
                        $this->studentValues[$student->id][$section->id] = $this->calculateGradeLevel($studentTotals[$student->id]);
                        break;
                    case 'remarks':
                        $this->studentValues[$student->id][$section->id] = $this->remarksStatement($studentTotals[$student->id]);
                        break;
                    case 'cumulative':
                        $this->studentValues[$student->id][$section->id] = $this->calculateCumulativeScore($student->id, $section->id);
                        break;
                }
            }
        }
    }

    public function saveResults()
    {

        // foreach ($this->students as $student) {
        //     $this->calculateTotal($student->id);
        // }

        $this->calculateTotalsForAllStudents();

        DB::beginTransaction();
        try {
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

        DB::commit();
        Notification::make()
            ->title('Success')
            ->body('Results saved successfully.')
            ->success()
            ->send();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving results: ' . $e->getMessage());
            Notification::make()
                ->title('Error')
                ->body('There was a problem saving the results. Please try again.')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.result.student-results');
    }
}
