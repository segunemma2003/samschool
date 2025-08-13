<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\PsychomotorCategory;
use App\Models\PyschomotorStudent;
use App\Models\SchoolInformation;
use App\Models\Student;
use App\Models\StudentAttendanceSummary;
use App\Models\StudentComment;
use App\Models\StudentResult;
use App\Models\Term;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\Log;

class ResultPdfService
{
    public function generateStudentResult(int $studentId, int $termId, int $academicId)
    {
        try {
            // Load student with basic relationships
            $student = Student::with([
                'class',
                'arm',
                'guardian'
            ])->findOrFail($studentId);

            // Get term and academic year
            $term = Term::findOrFail($termId);
            $academy = AcademicYear::findOrFail($academicId);

            // Get all related data
            $relatedData = $this->getRelatedData($student, $termId, $academicId);

            // Get saved results for this student
            $studentResult = $this->getStudentResult($studentId, $termId, $academicId);

            if (!$studentResult || !$studentResult->calculated_data) {
                throw new \Exception('No saved results found for this student in the selected term and academic year. Please ensure results have been entered and saved first.');
            }

            // Parse calculated data
            $calculatedData = json_decode($studentResult->calculated_data, true);

            if (!$calculatedData) {
                throw new \Exception('Invalid calculated data format. Please recalculate results.');
            }

            // Get psychomotor/behavioral data
            $psychomotorData = $this->getPsychomotorData($studentId, $termId, $academicId);

            // Extract summary and subjects from calculated data
            $summary = $calculatedData['summary'] ?? [];
            $subjects = $calculatedData['subjects'] ?? [];

            // Get student's class ranking/position if available
            $studentRanking = $this->getStudentRanking($studentId, $termId, $academicId);

            // Prepare view data using calculated results
            $viewData = [
                'student' => $student,
                'school' => $relatedData['school'],
                'academy' => $academy,
                'term' => $term,
                'studentAttendance' => $relatedData['studentAttendance'],
                'studentComment' => $relatedData['studentComment'],
                'studentResult' => $studentResult, // Pass the StudentResult model
                'calculatedData' => $calculatedData, // Pass parsed calculated data
                'summary' => $summary, // Pass summary for easy access
                'subjects' => $subjects, // Pass subjects for easy access
                'class' => $student->class,
                'totalSubject' => $summary['total_subjects'] ?? 0,
                'totalScore' => $summary['total_score'] ?? 0,
                'percent' => $summary['average'] ?? 0,
                'averageScore' => $summary['average'] ?? 0,
                'overallGrade' => $summary['grade'] ?? 'F9',
                'remarks' => $summary['remarks'] ?? 'NO COMMENT',
                'position' => $studentRanking['position'] ?? 'N/A',
                'totalStudents' => $studentRanking['totalStudents'] ?? 0,
                'psychomotorData' => $psychomotorData,
                'psychomotorCategory' => PsychomotorCategory::all(),
                'principalComment' => $this->getPerformanceComment(
                    $summary['average'] ?? 0,
                    $this->getSubjectScore($subjects, ['english', 'literacy']),
                    $this->getSubjectScore($subjects, ['mathematics', 'math', 'numeracy'])
                ),
            ];

            return $this->generatePdf($viewData, $student, $term, $academy);

        } catch (\Exception $e) {
            Log::error('Result PDF generation error', [
                'student_id' => $studentId,
                'term_id' => $termId,
                'academic_id' => $academicId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function getRelatedData(Student $student, int $termId, int $academicId): array
    {
        return [
            'school' => SchoolInformation::where([
                ['term_id', $termId],
                ['academic_id', $academicId]
            ])->first(),

            'studentAttendance' => StudentAttendanceSummary::where([
                ['term_id', $termId],
                ['student_id', $student->id],
                ['academic_id', $academicId]
            ])->first(),

            'studentComment' => StudentComment::where([
                ['student_id', $student->id],
                ['term_id', $termId],
                ['academic_id', $academicId]
            ])->first(),
        ];
    }

    private function getStudentResult(int $studentId, int $termId, int $academicId)
    {
        // Get the StudentResult record with calculated_data
        return StudentResult::where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicId) // Note: academic_year_id not academic_id
            ->first();
    }

    private function getPsychomotorData(int $studentId, int $termId, int $academicId)
    {
        // Get psychomotor/behavioral assessment data if available
        return PyschomotorStudent::with('psychomotorCategory')
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_id', $academicId)
            ->get()
            ->groupBy('psychomotor_category_id');
    }

    private function getSubjectScore(array $subjects, array $subjectKeywords): float
    {
        foreach ($subjects as $subject) {
            $subjectName = strtolower($subject['subject_name'] ?? '');

            foreach ($subjectKeywords as $keyword) {
                if (str_contains($subjectName, $keyword)) {
                    return $subject['total'] ?? 0;
                }
            }
        }

        return 0;
    }

    private function getStudentRanking(int $studentId, int $termId, int $academicId): array
    {
        try {
            // Get the student's class
            $student = Student::find($studentId);
            if (!$student) {
                return ['position' => 'N/A', 'totalStudents' => 0];
            }

            $studentClassId = $student->class_id;

            // Get all students in the same class with their calculated results
            $classResults = StudentResult::select('student_id', 'calculated_data')
                ->where('term_id', $termId)
                ->where('academic_year_id', $academicId)
                ->whereHas('student', function($query) use ($studentClassId) {
                    $query->where('class_id', $studentClassId);
                })
                ->get()
                ->map(function($result) {
                    $calculatedData = json_decode($result->calculated_data, true);
                    return [
                        'student_id' => $result->student_id,
                        'total_score' => $calculatedData['summary']['total_score'] ?? 0,
                        'average' => $calculatedData['summary']['average'] ?? 0
                    ];
                })
                ->sortByDesc('total_score') // Sort by total score descending
                ->values(); // Reset keys

            // Find the student's position
            $position = $classResults->search(function ($item) use ($studentId) {
                return $item['student_id'] == $studentId;
            });

            $position = $position !== false ? $position + 1 : 'N/A';

            return [
                'position' => $this->formatPosition($position),
                'totalStudents' => $classResults->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating student ranking', [
                'student_id' => $studentId,
                'term_id' => $termId,
                'academic_id' => $academicId,
                'error' => $e->getMessage()
            ]);

            return ['position' => 'N/A', 'totalStudents' => 0];
        }
    }

    private function formatPosition($position): string
    {
        if (!is_numeric($position)) {
            return 'N/A';
        }

        $suffix = match ($position % 10) {
            1 => $position % 100 === 11 ? 'TH' : 'ST',
            2 => $position % 100 === 12 ? 'TH' : 'ND',
            3 => $position % 100 === 13 ? 'TH' : 'RD',
            default => 'TH'
        };

        return $position . $suffix;
    }

    private function getGradeFromScore(float $percentage): string
    {
        // Nigerian grading system
        return match (true) {
            $percentage >= 80 => 'A1',
            $percentage >= 75 => 'A2',
            $percentage >= 70 => 'B3',
            $percentage >= 65 => 'B4',
            $percentage >= 60 => 'C5',
            $percentage >= 55 => 'C6',
            $percentage >= 50 => 'D7',
            $percentage >= 45 => 'E8',
            default => 'F9'
        };
    }

    private function getGradeColor(string $grade): string
    {
        $gradeNumber = (int) filter_var($grade, FILTER_SANITIZE_NUMBER_INT);

        return match (true) {
            $gradeNumber <= 2 => 'text-green',  // A1, A2
            $gradeNumber <= 4 => 'text-blue',   // B3, B4
            $gradeNumber <= 6 => 'pass-score',  // C5, C6
            default => 'fail-score'            // D7, E8, F9
        };
    }

    private function getGradeRemark(string $grade): string
    {
        $gradeNumber = (int) filter_var($grade, FILTER_SANITIZE_NUMBER_INT);

        return match (true) {
            $gradeNumber <= 2 => 'EXCELLENT',   // A1, A2
            $gradeNumber <= 4 => 'CREDIT',      // B3, B4
            $gradeNumber <= 6 => 'PASS',        // C5, C6
            default => 'FAIL'                  // D7, E8, F9
        };
    }

    private function generatePdf(array $viewData, Student $student, Term $term, AcademicYear $academy)
    {
        $pdf = SnappyPdf::loadView('results.template', $viewData)
            ->setOption('page-size', 'A4')
            ->setOption('orientation', 'portrait')
            ->setOption('margin-top', '8mm')
            ->setOption('margin-right', '8mm')
            ->setOption('margin-bottom', '8mm')
            ->setOption('margin-left', '8mm')
            ->setOption('encoding', 'UTF-8')
            ->setOption('enable-local-file-access', true)
            ->setOption('enable-smart-shrinking', true)
            ->setOption('print-media-type', true)
            ->setOption('dpi', 150)
            ->setOption('image-quality', 100)
            ->setOption('javascript-delay', 1000)
            ->setOption('no-stop-slow-scripts', true)
            ->setOption('disable-smart-shrinking', false)
            ->setOption('lowquality', false);

        $fileName = sprintf(
            "result-%s-%s-%s.pdf",
            str_replace(' ', '-', strtolower($student->name)),
            str_replace(' ', '-', strtolower($term->name)),
            str_replace('/', '-', $academy->title)
        );

        return [
            'pdf' => $pdf,
            'filename' => $fileName
        ];
    }

    private function getPerformanceComment(float $percentage, float $englishScore, float $mathScore): string
    {
        $comments = [
            'excellent' => [
                "Excellent Performance. Keep it up!",
                "Outstanding work! Continue this excellent effort.",
                "Do not relent on your efforts. Keep it up!"
            ],
            'very_good' => [
                "A Very Good Performance",
                "You are doing well. Keep pushing forward.",
                "An impressive effort! Well done."
            ],
            'hardworking' => [
                "A Hardworking Learner",
                "Your effort is commendable. Keep improving.",
                "Good work, but you can achieve even more."
            ],
            'work_harder' => [
                "Work Harder For a better result",
                "Keep improving your performance.",
                "Better focus next term will yield great results."
            ],
            'put_in_effort' => [
                "Put In More Effort",
                "Your performance can improve significantly.",
                "Stay committed to achieving better outcomes."
            ],
            'be_serious' => [
                "Be more serious in your studies",
                "Focus more on your academic goals.",
                "A stronger commitment to learning is needed."
            ],
            'english_math_fail' => [
                "Good effort, but focus more on English and Mathematics next term",
                "Put in more effort in English and Mathematics",
                "Improve your performance in English and Mathematics"
            ],
            'math_fail' => [
                "Good performance overall, but work harder in Mathematics",
                "Put in more effort in Mathematics next term",
                "Focus on improving your Mathematics skills"
            ],
            'english_fail' => [
                "Good work, but concentrate more on English next term",
                "Put in more effort in English Language",
                "Work on improving your English skills"
            ]
        ];

        // Check English and Mathematics performance for high achievers
        if ($percentage >= 70) {
            if ($englishScore < 50 && $mathScore < 50) {
                return $comments['english_math_fail'][array_rand($comments['english_math_fail'])];
            } elseif ($englishScore < 50) {
                return $comments['english_fail'][array_rand($comments['english_fail'])];
            } elseif ($mathScore < 50) {
                return $comments['math_fail'][array_rand($comments['math_fail'])];
            }
        }

        // General performance comments
        if ($percentage >= 85) {
            return $comments['excellent'][array_rand($comments['excellent'])];
        } elseif ($percentage >= 75) {
            return $comments['very_good'][array_rand($comments['very_good'])];
        } elseif ($percentage >= 65) {
            return $comments['hardworking'][array_rand($comments['hardworking'])];
        } elseif ($percentage >= 50) {
            return $comments['work_harder'][array_rand($comments['work_harder'])];
        } elseif ($percentage >= 40) {
            return $comments['put_in_effort'][array_rand($comments['put_in_effort'])];
        } else {
            return $comments['be_serious'][array_rand($comments['be_serious'])];
        }
    }

    /**
     * Generate multiple student results in bulk
     */
    public function generateBulkResults(array $studentIds, int $termId, int $academicId): array
    {
        $results = [];
        $errors = [];

        foreach ($studentIds as $studentId) {
            try {
                $result = $this->generateStudentResult($studentId, $termId, $academicId);
                $results[] = $result;
            } catch (\Exception $e) {
                $errors[] = [
                    'student_id' => $studentId,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'successful' => $results,
            'errors' => $errors,
            'total_processed' => count($studentIds),
            'successful_count' => count($results),
            'error_count' => count($errors)
        ];
    }
}
