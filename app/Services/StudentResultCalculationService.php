<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentResult;
use App\Models\CourseForm;
use App\Models\ResultSectionType;
use App\Models\ResultSectionStudentType;
use App\Models\Term;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SchoolInformation;
use App\Models\StudentComment;
use Illuminate\Support\Facades\Storage;
use App\Models\PyschomotorStudent;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentResultCalculationService
{
    /**
     * Calculate and store student results when teacher comments
     */
    public function calculateAndStoreResults(int $studentId, int $termId, int $academicYearId, string $teacherComment, int $commentedBy): StudentResult
    {
        try {
            // Get student with class information
            $student = Student::with(['class'])->findOrFail($studentId);

            // Calculate all the result data with proper calc_pattern logic
            $calculatedData = $this->calculateResultDataWithPatterns($studentId, $termId, $academicYearId);

            // Calculate summary statistics
            $summary = $this->calculateSummary($calculatedData);

            // Determine grade and remarks
            $grade = $this->calculateGrade($summary['average']);
            $remarks = $this->calculateRemarks($summary['average']);

            // Get result section types for headings
            $resultSectionTypes = ResultSectionType::where('term_id', $termId)
                ->whereHas('resultSection', function ($query) use ($student) {
                    $query->where('group_id', $student->class->group->id);
                })
                ->orderBy('name')
                ->get();

            // Prepare headings data
            $headings = [];
            foreach ($resultSectionTypes as $sectionType) {
                $headings[] = [
                    'id' => $sectionType->id,
                    'name' => $sectionType->name,
                    'code' => $sectionType->code,
                    'calc_pattern' => $sectionType->calc_pattern,
                    'type' => $sectionType->type,
                    'score_weight' => $sectionType->score_weight
                ];
            }

            // Prepare the complete JSON data
            $jsonData = [
                'subjects' => $calculatedData['subjects'],
                'summary' => $summary,
                'headings' => $headings,
                'metadata' => [
                    'calculated_at' => now()->toISOString(),
                    'calculated_by' => 'system',
                    'student_id' => $studentId,
                    'term_id' => $termId,
                    'academic_year_id' => $academicYearId,
                ]
            ];

            // Create or update the student result
            $studentResult = StudentResult::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'term_id' => $termId,
                    'academic_year_id' => $academicYearId,
                ],
                [
                    'class_id' => $student->class_id,
                    'total_score' => $summary['total_score'],
                    'average_score' => $summary['average'],
                    'grade' => $grade,
                    'remarks' => $remarks,
                    'total_subjects' => $summary['total_subjects'],
                    'teacher_comment' => $teacherComment,
                    'commented_by' => $commentedBy,
                    'calculated_data' => $jsonData,
                    'calculated_at' => now(),
                    'calculation_status' => 'completed',
                ]
            );

            Log::info('Student result calculated and stored successfully', [
                'student_id' => $studentId,
                'term_id' => $termId,
                'academic_year_id' => $academicYearId,
                'result_id' => $studentResult->id
            ]);

            return $studentResult;

        } catch (\Exception $e) {
            Log::error('Failed to calculate student results', [
                'student_id' => $studentId,
                'term_id' => $termId,
                'academic_year_id' => $academicYearId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Calculate detailed result data for all subjects with proper calc_pattern logic
     */
    public function calculateResultDataWithPatterns(int $studentId, int $termId, int $academicYearId): array
    {
        // Get the student's class/group information
        $student = Student::with(['class'])->find($studentId);
        $classId = $student->class_id ?? $student->group_id;

        if (!$classId) {
            throw new \Exception('Student class or group is missing');
        }

        // Get all result section types for this term and class
        $resultSectionTypes = ResultSectionType::where('term_id', $termId)
            ->whereHas('resultSection', function ($query) use ($classId) {
                $query->where('group_id', $classId);
            })
            ->orderBy('name')
            ->get();

        // Get all students in the same class for class-wide calculations
        $classStudents = Student::where('class_id', $student->class_id)
            ->orWhere('group_id', $classId)
            ->pluck('id')
            ->toArray();

        // Calculate class-wide metrics first
        $classMetrics = $this->calculateClassMetrics($classStudents, $termId, $academicYearId, $resultSectionTypes);

        // Get course forms for the specific student
        $courseForms = CourseForm::with([
            'subject.subjectDepot',
            'subject.teacher',
            'scoreBoard.resultSectionType'
        ])
        ->where('student_id', $studentId)
        ->where('term_id', $termId)
        ->where('academic_year_id', $academicYearId)
        ->get();

        // Filter for unique subjects (same as view result page)
        $uniqueSubjects = $courseForms->unique('subject_id');
        $subjects = [];

        foreach ($uniqueSubjects as $courseForm) {
            $subjectData = [
                'subject_id' => $courseForm->subject_id,
                'subject_name' => $courseForm->subject->subjectDepot->name ?? 'Unknown Subject',
                'subject_code' => $courseForm->subject->code ?? '',
                'scores' => [],
                'ca_score' => 0,
                'exam_score' => 0,
                'total' => 0,
                'grade' => '',
                'position' => 'N/A',
                'class_average' => 'N/A',
                'highest_score' => 'N/A',
                'lowest_score' => 'N/A',
                'teacher_name' => $courseForm->subject->teacher->name ?? 'TEACHER'
            ];

            // Calculate scores based on calc_pattern
            $calculatedScores = $this->calculateScoresByPattern(
                $courseForm,
                $resultSectionTypes,
                $classMetrics,
                $studentId,
                $academicYearId,
                $termId
            );

            $subjectData['scores'] = $calculatedScores;

            // Calculate CA and Exam scores from individual scores (same as PDF template)
            $caScore = 0;
            $examScore = 0;
            $subjectTotal = 0;

            foreach ($calculatedScores as $typeName => $score) {
                $sectionType = $resultSectionTypes->where('name', $typeName)->first();
                if ($sectionType) {
                    $scoreValue = (float) $score;

                    // Calculate CA and Exam scores
                    if (stripos($sectionType->name ?? '', 'ca') !== false ||
                        stripos($sectionType->name ?? '', 'test') !== false ||
                        stripos($sectionType->name ?? '', 'assignment') !== false) {
                        $caScore += $scoreValue;
                    } elseif (stripos($sectionType->name ?? '', 'exam') !== false) {
                        $examScore += $scoreValue;
                    }

                    // Calculate total from input scores only
                    if ($sectionType->calc_pattern === 'input') {
                        $subjectTotal += $scoreValue;
                    }
                }
            }

            // If no specific breakdown, assume 40/60 split (same as PDF template)
            if ($caScore == 0 && $examScore == 0 && $subjectTotal > 0) {
                $caScore = round($subjectTotal * 0.4);
                $examScore = round($subjectTotal * 0.6);
            }

            $subjectData['ca_score'] = $caScore;
            $subjectData['exam_score'] = $examScore;
            $subjectData['total'] = $subjectTotal;
            $subjectData['grade'] = $this->calculateSubjectGrade($subjectData['total']);

            // Get class metrics for this subject
            $subjectClassMetrics = $classMetrics['subjects'][$courseForm->subject_id] ?? [];
            $subjectData['position'] = $subjectClassMetrics['position'] ?? 'N/A';
            $subjectData['class_average'] = $subjectClassMetrics['class_average'] ?? 'N/A';
            $subjectData['highest_score'] = $subjectClassMetrics['highest_score'] ?? 'N/A';
            $subjectData['lowest_score'] = $subjectClassMetrics['lowest_score'] ?? 'N/A';

            $subjects[] = $subjectData;
        }

        return ['subjects' => $subjects];
    }

    /**
     * Calculate class-wide metrics for all students
     */
    private function calculateClassMetrics(array $classStudentIds, int $termId, int $academicYearId, $resultSectionTypes): array
    {
        $metrics = [];

        // Get all course forms for the class
        $classCourseForms = CourseForm::with(['scoreBoard.resultSectionType'])
            ->whereIn('student_id', $classStudentIds)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        // Calculate totals for each student
        $studentTotals = [];
        foreach ($classStudentIds as $studentId) {
            $studentCourseForms = $classCourseForms->where('student_id', $studentId);
            $total = 0;

            foreach ($studentCourseForms as $courseForm) {
                foreach ($courseForm->scoreBoard as $score) {
                    $sectionType = $resultSectionTypes->where('id', $score->result_section_type_id)->first();
                    if ($sectionType && $sectionType->calc_pattern === 'input') {
                        $total += (float) $score->score;
                    }
                }
            }

            if ($total > 0) {
                $studentTotals[$studentId] = $total;
            }
        }

        // Calculate class metrics
        if (!empty($studentTotals)) {
            $metrics['class_average'] = round(array_sum($studentTotals) / count($studentTotals), 1);
            $metrics['class_highest_score'] = max($studentTotals);
            $metrics['class_lowest_score'] = min($studentTotals);

            // Calculate positions
            arsort($studentTotals);
            $positions = [];
            $rank = 1;
            $previousScore = null;
            $tieCount = 0;

            foreach ($studentTotals as $studentId => $score) {
                if ($score !== $previousScore) {
                    $rank += $tieCount;
                    $tieCount = 0;
                } else {
                    $tieCount++;
                }
                $positions[$studentId] = $rank;
                $previousScore = $score;
            }

            $metrics['positions'] = $positions;
        } else {
            $metrics['class_average'] = 0;
            $metrics['class_highest_score'] = 0;
            $metrics['class_lowest_score'] = 0;
            $metrics['positions'] = [];
        }

        return $metrics;
    }

    /**
     * Calculate scores for a course form based on calc_pattern
     */
    private function calculateScoresByPattern($courseForm, $resultSectionTypes, array $classMetrics, int $studentId, int $academicYearId, int $termId): array
    {
        $calculatedScores = [];

        foreach ($resultSectionTypes as $sectionType) {
            $typeName = $sectionType->name;

            // Find existing score for this section type
            $existingScore = $courseForm->scoreBoard
                ->where('result_section_type_id', $sectionType->id)
                ->first();

            switch ($sectionType->calc_pattern) {
                case 'input':
                    // Use the input score as is
                    $calculatedScores[$typeName] = $existingScore ? $existingScore->score : 0;
                    break;

                case 'total':
                    // Calculate total from input scores
                    $total = 0;
                    foreach ($courseForm->scoreBoard as $score) {
                        $scoreSectionType = $resultSectionTypes->where('id', $score->result_section_type_id)->first();
                        if ($scoreSectionType && $scoreSectionType->calc_pattern === 'input') {
                            $total += (float) $score->score;
                        }
                    }
                    $calculatedScores[$typeName] = $total;
                    break;

                case 'position':
                    // Use position from class metrics
                    $calculatedScores[$typeName] = $classMetrics['positions'][$studentId] ?? null;
                    break;

                case 'class_average':
                    // Use class average
                    $calculatedScores[$typeName] = $classMetrics['class_average'];
                    break;

                case 'class_highest_score':
                    // Use class highest score
                    $calculatedScores[$typeName] = $classMetrics['class_highest_score'];
                    break;

                case 'class_lowest_score':
                    // Use class lowest score
                    $calculatedScores[$typeName] = $classMetrics['class_lowest_score'];
                    break;

                case 'cumulative':
                    // Calculate cumulative average from previous terms in the same academic year
                    $calculatedScores[$typeName] = $this->calculateCumulativeScore($studentId, $courseForm->subject_id, $academicYearId, $termId);
                    break;

                case 'grade_level':
                    // Calculate grade based on total
                    $total = 0;
                    foreach ($courseForm->scoreBoard as $score) {
                        $scoreSectionType = $resultSectionTypes->where('id', $score->result_section_type_id)->first();
                        if ($scoreSectionType && $scoreSectionType->calc_pattern === 'input') {
                            $total += (float) $score->score;
                        }
                    }
                    $calculatedScores[$typeName] = $this->calculateGradeLevel($total);
                    break;

                case 'remarks':
                    // Calculate remarks based on total
                    $total = 0;
                    foreach ($courseForm->scoreBoard as $score) {
                        $scoreSectionType = $resultSectionTypes->where('id', $score->result_section_type_id)->first();
                        if ($scoreSectionType && $scoreSectionType->calc_pattern === 'input') {
                            $total += (float) $score->score;
                        }
                    }
                    $calculatedScores[$typeName] = $this->calculateRemarks($total);
                    break;

                default:
                    // For unknown patterns, use existing score or 0
                    $calculatedScores[$typeName] = $existingScore ? $existingScore->score : 0;
                    break;
            }
        }

        return $calculatedScores;
    }

    /**
     * Calculate grade level based on score
     */
    private function calculateGradeLevel(float $score): string
    {
        return match (true) {
            $score >= 75 => 'A1',
            $score >= 70 => 'B2',
            $score >= 65 => 'B3',
            $score >= 61 => 'C4',
            $score >= 55 => 'C5',
            $score >= 50 => 'C6',
            $score >= 45 => 'D7',
            $score >= 40 => 'E8',
            default => 'F9'
        };
    }

    /**
     * Calculate cumulative score from previous terms in the same academic year
     */
    private function calculateCumulativeScore(int $studentId, int $subjectId, int $academicYearId, int $currentTermId): float
    {
        try {
            // Get all terms, ordered by their sequence
            $terms = \App\Models\Term::orderBy('id', 'asc')->get();

            // Find the current term's position
            $currentTermIndex = $terms->search(function ($term) use ($currentTermId) {
                return $term->id === $currentTermId;
            });

            if ($currentTermIndex === false || $currentTermIndex === 0) {
                // If this is the first term or term not found, return 0
                return 0.0;
            }

            // Get previous terms (all terms before the current one)
            $previousTerms = $terms->take($currentTermIndex);

            $totalScores = [];
            $totalCount = 0;

            foreach ($previousTerms as $term) {
                // Get the course form for this student, subject, and term
                $courseForm = CourseForm::where([
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'term_id' => $term->id,
                    'academic_year_id' => $academicYearId
                ])->first();

                if ($courseForm) {
                    // Get all input scores for this course form
                    $inputScores = DB::table('result_section_student_types')
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

            return 0.0;

        } catch (\Exception $e) {
            Log::error('Error calculating cumulative score', [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'academic_year_id' => $academicYearId,
                'current_term_id' => $currentTermId,
                'error' => $e->getMessage()
            ]);

            return 0.0;
        }
    }

    /**
     * Calculate summary statistics from calculated data
     */
    private function calculateSummary(array $calculatedData): array
    {
        $subjects = $calculatedData['subjects'];
        $totalScore = 0;
        $totalSubjects = count($subjects);

        foreach ($subjects as $subject) {
            $totalScore += $subject['total'];
        }

        $average = $totalSubjects > 0 ? round($totalScore / $totalSubjects, 2) : 0;

        // Get position and total students from the first subject's class metrics
        $position = 'N/A';
        $totalStudents = 0;
        if (!empty($subjects)) {
            // Try to get position from class metrics if available
            $firstSubject = $subjects[0];
            if (isset($firstSubject['position']) && $firstSubject['position'] !== 'N/A') {
                $position = $firstSubject['position'];
            }
            // For total students, we'll need to calculate this separately
            // This will be updated when we have access to class metrics
        }

        return [
            'total_subjects' => $totalSubjects,
            'total_score' => $totalScore,
            'average' => $average,
            'grade' => $this->calculateGrade($average),
            'remarks' => $this->calculateRemarks($average),
            'position' => $position,
            'total_students' => $totalStudents,
        ];
    }

    /**
     * Calculate overall grade based on average
     */
    private function calculateGrade(float $average): string
    {
        return match (true) {
            $average >= 75 => 'A1',
            $average >= 70 => 'B2',
            $average >= 65 => 'B3',
            $average >= 61 => 'C4',
            $average >= 55 => 'C5',
            $average >= 50 => 'C6',
            $average >= 45 => 'D7',
            $average >= 40 => 'E8',
            default => 'F9'
        };
    }

    /**
     * Calculate subject grade
     */
    private function calculateSubjectGrade(float $score): string
    {
        return match (true) {
            $score >= 75 => 'A1',
            $score >= 70 => 'B2',
            $score >= 65 => 'B3',
            $score >= 61 => 'C4',
            $score >= 55 => 'C5',
            $score >= 50 => 'C6',
            $score >= 45 => 'D7',
            $score >= 40 => 'E8',
            default => 'F9'
        };
    }

    /**
     * Calculate remarks based on average
     */
    private function calculateRemarks(float $average): string
    {
        return match (true) {
            $average >= 86 => 'EXCELLENT',
            $average >= 75 => 'VERY GOOD',
            $average >= 65 => 'GOOD',
            $average >= 51 => 'WORK HARDER',
            $average >= 41 => 'PUT IN MORE EFFORT',
            default => 'BE MORE SERIOUS'
        };
    }

    /**
     * Get stored result for a student
     */
    public function getStoredResult(int $studentId, int $termId, int $academicYearId): ?StudentResult
    {
        return StudentResult::where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->where('calculation_status', 'completed')
            ->first();
    }

    /**
     * Check if result exists and is complete
     */
    public function hasCompleteResult(int $studentId, int $termId, int $academicYearId): bool
    {
        return StudentResult::where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->where('calculation_status', 'completed')
            ->exists();
    }

    /**
     * Generate student result PDF
     */
    public function generateStudentResultPdf(int $studentId, int $termId, int $academicYearId): string
    {
        try {
            // Check if student has course forms for this term and academic year
            $courseForms = CourseForm::where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('academic_year_id', $academicYearId)
                ->get();

            if ($courseForms->isEmpty()) {
                throw new \Exception('No course forms found for this student in the selected term and academic year');
            }

            // Get related data
            $student = Student::with(['class'])->findOrFail($studentId);
            $term = Term::findOrFail($termId);
            $academicYear = AcademicYear::findOrFail($academicYearId);

            // Get school information
            $school = SchoolInformation::where([
                ['term_id', $termId],
                ['academic_id', $academicYearId]
            ])->first();

            // Get student comment
            $studentComment = StudentComment::where([
                ['student_id', $studentId],
                ['term_id', $termId],
                ['academic_id', $academicYearId]
            ])->first();

            // Get course forms with scores and teacher information
            $courseForms = CourseForm::with([
                'subject.subjectDepot',
                'subject.teacher',
                'scoreBoard.resultSectionType'
            ])
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->get();

            // Get result section types for this term and class
            $classId = $student->class_id ?? $student->group_id;
            $resultSectionTypes = ResultSectionType::where('term_id', $termId)
                ->whereHas('resultSection', function ($query) use ($classId) {
                    $query->where('group_id', $classId);
                })
                ->orderBy('name')
                ->get();

            // Group by calc_pattern
            $markObtained = $resultSectionTypes->where('calc_pattern', 'input');
            $studentSummary = $resultSectionTypes->whereIn('calc_pattern', ['position', 'grade_level']);
            $termSummary = $resultSectionTypes->whereIn('calc_pattern', ['class_average', 'class_highest_score', 'class_lowest_score']);
            $remarks = $resultSectionTypes->where('calc_pattern', 'remarks');

            // Calculate total score from subject averages
            $totalScore = 0;
            $uniqueSubjects = $courseForms->unique('subject_id');
            $totalSubject = $uniqueSubjects->count();
            $subjectsWithScores = 0;

            foreach ($uniqueSubjects as $courseForm) {
                $subjectTotal = 0;
                $scoreCount = 0;

                foreach ($courseForm->scoreBoard as $score) {
                    $sectionType = $resultSectionTypes->where('id', $score->result_section_type_id)->first();
                    if ($sectionType && $sectionType->calc_pattern === 'total') {
                        $subjectTotal += (float) $score->score;
                        $scoreCount++;
                    }
                }

                // Use the total score for this subject (should be only one total per subject)
                if ($scoreCount > 0) {
                    $totalScore += $subjectTotal; // Use total directly, not average
                    $subjectsWithScores++;
                }
            }

            $percent = $subjectsWithScores > 0 ? round($totalScore / $subjectsWithScores, 1) : 0;

            // Get principal comment
            $principalComment = $this->getPrincipalComment($percent);

            // Get attendance data
            $studentAttendance = \App\Models\StudentAttendanceSummary::where([
                ['term_id', $termId],
                ['student_id', $studentId],
                ['academic_id', $academicYearId]
            ])->first();

            // Get next term
            $nextTerm = null;
            if ($term->ending_date) {
                $nextTerm = Term::where('starting_date', '>', $term->ending_date)
                    ->orderBy('starting_date')
                    ->first();
            }

            // Get psychomotor/behavioral data
            $psychomotorData = \App\Models\PyschomotorStudent::with('psychomotor')
                ->whereHas('psychomotor', function ($query) use ($termId, $academicYearId) {
                    $query->where('term_id', $termId)
                          ->where('academic_id', $academicYearId);
                })
                ->where('student_id', $studentId)
                ->get();

            // Organize behavioral data by category and term
            $behavioralData = [];

            // Get all terms in the academic year for comparison
            $allTerms = Term::all(); // Get all terms since terms don't have academic_year_id

            $termNames = ['1st', '2nd', '3rd'];

            foreach ($psychomotorData as $psychData) {
                $skillName = strtolower(str_replace(' ', '_', $psychData->psychomotor->skill));
                $termIndex = $allTerms->search(function ($term) use ($psychData) {
                    return $term->id === $psychData->psychomotor->term_id;
                });

                if ($termIndex !== false && isset($termNames[$termIndex])) {
                    $behavioralData[$skillName][$termNames[$termIndex]] = $psychData->rating;
                }
            }

            // Fill missing data with defaults
            $defaultSkills = [
                'obedience', 'honesty', 'self_control', 'self_reliance', 'initiative',
                'punctuality', 'neatness', 'perseverance', 'attendance', 'attentiveness',
                'courtesy', 'consideration', 'sociability', 'promptness', 'responsibility',
                'reading_writing', 'verbal_communication', 'sport_game', 'inquisitiveness', 'dexterity'
            ];

            foreach ($defaultSkills as $skill) {
                if (!isset($behavioralData[$skill])) {
                    $behavioralData[$skill] = ['1st' => '-', '2nd' => '-', '3rd' => '-'];
                } else {
                    foreach ($termNames as $termName) {
                        if (!isset($behavioralData[$skill][$termName])) {
                            $behavioralData[$skill][$termName] = '-';
                        }
                    }
                }
            }

            // Get annual summary data from previous terms
            $annualSummaryData = [];
            foreach ($courseForms as $courseForm) {
                $subjectId = $courseForm->subject_id;
                $annualSummaryData[$subjectId] = [
                    'first_term_avg' => 0,
                    'second_term_avg' => 0,
                    'year_avg' => 0
                ];

                // Get previous term data for this subject
                $previousTerms = $allTerms->where('id', '!=', $termId)->take(2);
                $termCount = 0;
                $totalAvg = 0;

                foreach ($previousTerms as $prevTerm) {
                    $prevCourseForm = CourseForm::with('scoreBoard.resultSectionType')
                        ->where('student_id', $studentId)
                        ->where('subject_id', $subjectId)
                        ->where('term_id', $prevTerm->id)
                        ->where('academic_year_id', $academicYearId)
                        ->first();

                    if ($prevCourseForm) {
                        $termScore = 0;
                        $scoreCount = 0;

                        foreach ($prevCourseForm->scoreBoard as $score) {
                            $sectionType = $resultSectionTypes->where('id', $score->result_section_type_id)->first();
                            if ($sectionType && $sectionType->calc_pattern === 'input') {
                                $termScore += (float) $score->score;
                                $scoreCount++;
                            }
                        }

                        $termAvg = $scoreCount > 0 ? $termScore / $scoreCount : 0;
                        $totalAvg += $termAvg;
                        $termCount++;

                        if ($termCount === 1) {
                            $annualSummaryData[$subjectId]['first_term_avg'] = $termAvg;
                        } elseif ($termCount === 2) {
                            $annualSummaryData[$subjectId]['second_term_avg'] = $termAvg;
                        }
                    }
                }

                // Calculate year average
                $currentTermAvg = $percent; // Current term average
                $totalAvg += $currentTermAvg;
                $termCount++;

                $annualSummaryData[$subjectId]['year_avg'] = $termCount > 0 ? $totalAvg / $termCount : 0;
            }

            // Prepare data for template - ensure all data is safe for PDF generation
            $data = [
                'student' => $student,
                'term' => $term,
                'academy' => $academicYear,
                'class' => $student->class,
                'school' => $school,
                'studentComment' => $studentComment,
                'courses' => $courseForms,
                'markObtained' => $markObtained,
                'studentSummary' => $studentSummary,
                'termSummary' => $termSummary,
                'remarks' => $remarks,
                'totalScore' => $totalScore,
                'totalSubject' => $totalSubject,
                'percent' => $percent,
                'principalComment' => $principalComment,
                'resultData' => $this->calculateResultDataWithPatterns($studentId, $termId, $academicYearId),
                'studentAttendance' => $studentAttendance,
                'nextTerm' => $nextTerm,
                'behavioralData' => $behavioralData,
                'resultSectionTypes' => $resultSectionTypes,
                'annualSummaryData' => $annualSummaryData,
            ];

            // Generate PDF with error handling
            $pdf = Pdf::loadView('results.template', $data);
            $pdf->setPaper('A4', 'portrait');

            // Generate filename with tenant context
            $tenantId = tenant('id') ?? 'default';
            $filename = "results/{$tenantId}/result_{$student->name}_{$term->name}_{$academicYear->title}.pdf";
            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);

            // Save to S3 with tenant-aware path
            Storage::disk('s3')->put($filename, $pdf->output());

            // Return S3 URL
            return config('filesystems.disks.s3.url') . '/' . $filename;

        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage(), [
                'student_id' => $studentId,
                'term_id' => $termId,
                'academic_year_id' => $academicYearId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Get principal comment based on performance
     */
    public function getPrincipalComment(float $percentage): string
    {
        $comments = [
            'excellent' => [
                "Excellent Performance! Keep up the outstanding work.",
                "Outstanding achievement! You are a role model for other students.",
                "Exceptional performance! Continue to strive for excellence."
            ],
            'very_good' => [
                "Very Good Performance! You are doing exceptionally well.",
                "Excellent work! Keep pushing yourself to achieve more.",
                "Great performance! You have shown remarkable improvement."
            ],
            'good' => [
                "Good Performance! You are making steady progress.",
                "Well done! Continue to work hard for better results.",
                "Good effort! Keep up the good work."
            ],
            'average' => [
                "Average Performance. There's room for improvement.",
                "You can do better. Focus more on your studies.",
                "Keep working hard to improve your performance."
            ],
            'below_average' => [
                "Below Average Performance. More effort is needed.",
                "You need to work harder to improve your grades.",
                "Focus more on your studies to achieve better results."
            ],
            'poor' => [
                "Poor Performance. Immediate attention is required.",
                "You need to put in more effort to pass your subjects.",
                "Serious improvement is needed in your academic performance."
            ]
        ];

        if ($percentage >= 80) {
            return $comments['excellent'][array_rand($comments['excellent'])];
        } elseif ($percentage >= 70) {
            return $comments['very_good'][array_rand($comments['very_good'])];
        } elseif ($percentage >= 60) {
            return $comments['good'][array_rand($comments['good'])];
        } elseif ($percentage >= 50) {
            return $comments['average'][array_rand($comments['average'])];
        } elseif ($percentage >= 40) {
            return $comments['below_average'][array_rand($comments['below_average'])];
        } else {
            return $comments['poor'][array_rand($comments['poor'])];
        }
    }
}
