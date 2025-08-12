<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Result;
use App\Models\PsychomotorCategory;
use App\Models\PyschomotorStudent;
use App\Models\SchoolInformation;
use App\Models\Student;
use App\Models\StudentAttendanceSummary;
use App\Models\StudentComment;
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
            $results = $this->getStudentResults($studentId, $termId, $academicId);

            if ($results->isEmpty()) {
                throw new \Exception('No saved results found for this student in the selected term and academic year. Please ensure results have been entered and saved first.');
            }

            // Get psychomotor/behavioral data
            $psychomotorData = $this->getPsychomotorData($studentId, $termId, $academicId);

            // Calculate summary statistics from saved results
            $summaryStats = $this->calculateSummaryFromResults($results);

            // Get student's class ranking/position if available
            $studentRanking = $this->getStudentRanking($studentId, $termId, $academicId);

            // Prepare view data using saved results
            $viewData = [
                'student' => $student,
                'school' => $relatedData['school'],
                'academy' => $academy,
                'term' => $term,
                'studentAttendance' => $relatedData['studentAttendance'],
                'studentComment' => $relatedData['studentComment'],
                'results' => $results, // Use saved results instead of courses
                'class' => $student->class,
                'totalSubject' => $results->count(),
                'totalScore' => $summaryStats['totalScore'],
                'percent' => $summaryStats['percentage'],
                'averageScore' => $summaryStats['averageScore'],
                'position' => $studentRanking['position'] ?? 'N/A',
                'totalStudents' => $studentRanking['totalStudents'] ?? 0,
                'psychomotorData' => $psychomotorData,
                'psychomotorCategory' => PsychomotorCategory::all(),
                'principalComment' => $this->getPerformanceComment(
                    $summaryStats['percentage'],
                    $summaryStats['englishScore'],
                    $summaryStats['mathScore']
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

    private function getStudentResults(int $studentId, int $termId, int $academicId)
    {
        // Get all saved results for this student in the specified term and academic year
        return Result::with(['subject.subjectDepot', 'student', 'term', 'academy'])
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_id', $academicId)
            ->orderBy('subject_id')
            ->get();
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

    private function calculateSummaryFromResults($results): array
    {
        $totalScore = $results->sum('mark_obtained');
        $totalPossible = $results->sum('mark_obtainable');
        $subjectCount = $results->count();

        $percentage = $totalPossible > 0 ? round(($totalScore / $totalPossible) * 100, 1) : 0;
        $averageScore = $subjectCount > 0 ? round($totalScore / $subjectCount, 1) : 0;

        // Find English and Math scores for specific commenting
        $englishScore = 0;
        $mathScore = 0;

        foreach ($results as $result) {
            $subjectName = strtolower($result->subject->subjectDepot->name ?? '');
            $scorePercentage = $result->mark_obtainable > 0 ?
                ($result->mark_obtained / $result->mark_obtainable) * 100 : 0;

            if (str_contains($subjectName, 'english') || str_contains($subjectName, 'literacy')) {
                $englishScore = $scorePercentage;
            } elseif (str_contains($subjectName, 'math') || str_contains($subjectName, 'numeracy')) {
                $mathScore = $scorePercentage;
            }
        }

        return [
            'totalScore' => $totalScore,
            'totalPossible' => $totalPossible,
            'percentage' => $percentage,
            'averageScore' => $averageScore,
            'englishScore' => $englishScore,
            'mathScore' => $mathScore,
        ];
    }

    private function getStudentRanking(int $studentId, int $termId, int $academicId): array
    {
        // Calculate student's position in class based on total scores
        $studentResult = Result::where([
            ['student_id', $studentId],
            ['term_id', $termId],
            ['academic_id', $academicId]
        ])->get();

        if ($studentResult->isEmpty()) {
            return ['position' => 'N/A', 'totalStudents' => 0];
        }

        $studentTotalScore = $studentResult->sum('mark_obtained');
        $studentClassId = Student::find($studentId)->class_id;

        // Get all students in the same class with their total scores
        $classResults = Result::select('student_id')
            ->selectRaw('SUM(mark_obtained) as total_score')
            ->where('term_id', $termId)
            ->where('academic_id', $academicId)
            ->whereHas('student', function($query) use ($studentClassId) {
                $query->where('class_id', $studentClassId);
            })
            ->groupBy('student_id')
            ->orderByDesc('total_score')
            ->get();

        $position = $classResults->search(function ($item) use ($studentId) {
            return $item->student_id == $studentId;
        });

        $position = $position !== false ? $position + 1 : 'N/A';

        return [
            'position' => $this->formatPosition($position),
            'totalStudents' => $classResults->count()
        ];
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
        return match (true) {
            $percentage >= 70 => 'A',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'P',
            default => 'F'
        };
    }

    private function getGradeColor(string $grade): string
    {
        return match ($grade) {
            'A', 'B' => 'text-green',
            'C' => 'text-blue',
            'P' => 'pass-score',
            'F' => 'fail-score',
            default => ''
        };
    }

    private function getGradeRemark(string $grade): string
    {
        return match ($grade) {
            'A' => 'EXCELLENT',
            'B', 'C' => 'CREDIT',
            'P' => 'PASS',
            'F' => 'FAIL',
            default => 'N/A'
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
}
