<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CourseForm;
use App\Models\Exam;
use App\Models\ExamRecording;
use App\Models\Psychomotor;
use App\Models\QuizScore;
use App\Models\QuizSubmission;
use App\Models\ResultSectionType;
use App\Models\SchoolClass;
use App\Models\SchoolInformation;
use App\Models\Student;
use App\Models\StudentAttendanceSummary;
use App\Models\StudentComment;
use App\Models\Term;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ExamController extends Controller
{
    // Promotion criteria config: class_id => [min_average, required_subjects]
    protected $promotionCriteria = [
        // Example: 1 => ['min_average' => 60, 'required_subjects' => ['English', 'Math']],
        // Add your class IDs and criteria here
    ];

    protected function determinePromotion($student, $courses, $average) {
        $classId = $student->class_id;
        $criteria = $this->promotionCriteria[$classId] ?? ['min_average' => 50, 'required_subjects' => []];
        if ($average < $criteria['min_average']) return false;
        foreach ($criteria['required_subjects'] as $subjectName) {
            $subject = $courses->first(fn($c) => stripos($c->subject->subjectDepot->name, $subjectName) !== false);
            if (!$subject || $subject->scoreBoard->sum('score') < 50) return false;
        }
        return true;
    }

    public function generatePdf($studentId,$termId,$academyId){
        $cacheKey = "pdf_{$studentId}_{$termId}_{$academyId}";
        if (Cache::has($cacheKey)) {
            $pdfPath = Cache::get($cacheKey);
            if (file_exists($pdfPath)) {
                return response()->file($pdfPath);
            } else {
                Cache::forget($cacheKey);
            }
        }
        try {
            // Eager load all students in the class with their courseForms, scoreBoard, and subject.subjectDepot
            $student = Student::with(['class', 'class.group'])->whereId($studentId)->firstOrFail();
            // Use chunking to avoid memory overload for large classes
            $allStudents = collect();
            Student::with(['courseForms' => function($q) use ($termId, $academyId) {
                $q->where('term_id', $termId)
                  ->where('academic_year_id', $academyId)
                  ->with(['scoreBoard', 'subject.subjectDepot']);
            }])->where('class_id', $student->class_id)
              ->chunk(100, function($students) use (&$allStudents) {
                  $allStudents = $allStudents->merge($students);
              });

            $studentAverages = [];
            $totalStudents = $allStudents->count();
            if (!$student) {
                throw new Exception('Student record not found.');
            }
            if (!$student->class || !$student->class->group) {
                throw new Exception('Student class or group information is missing.');
            }
            $termAndAcademy = collect([
                'term' => Term::find($termId),
                'academy' => AcademicYear::find($academyId)
            ]);
            if (!$termAndAcademy['term'] || !$termAndAcademy['academy']) {
                throw new \Exception('Term or Academic Year information is missing.');
            }
            $relatedData = collect([
                'school' => SchoolInformation::where([
                    ['term_id', $termId],
                    ['academic_id', $academyId]
                ]),
                'studentAttendance' => StudentAttendanceSummary::where([
                    ['term_id', $termId],
                    ['student_id', $student->id],
                    ['academic_id', $academyId]
                ]),
                'studentComment' => StudentComment::where([
                    ['student_id', $student->id],
                    ['term_id', $termId],
                    ['academic_id', $academyId]
                ])
            ])->map(fn ($query) => $query->first());
            $missingData = [];
            if (!$relatedData['school']) $missingData[] = 'School Information';
            if (!$relatedData['studentAttendance']) $missingData[] = 'Student Attendance';
            if (!$relatedData['studentComment']) $missingData[] = 'Student Comment';
            if (count($missingData) > 0) {
                throw new \Exception('The following information is missing and must be filled before generating the PDF: ' . implode(', ', $missingData));
            }
            // Only get this student's courses (already eager loaded for all students)
            $courses = $allStudents->firstWhere('id', $student->id)?->courseForms ?? collect();
            if(count($courses) < 1){
                throw new \Exception('No courses found for this student in the selected term and academic year.');
            }
            $invalidCourses = [];
            foreach ($courses as $course) {
                if (!$course->subject || !$course->subject->subjectDepot) {
                    $invalidCourses[] = $course->id;
                }
            }
            if (count($invalidCourses) > 0) {
                throw new \Exception('Some courses have missing subject information. Please complete the data for course IDs: ' . implode(', ', $invalidCourses));
            }
            // For psychomotor, limit to 100 records (adjust as needed for your data size)
            $psychomotorAffective = Psychomotor::with(['psychomotorStudent' => function($query) use($student, $termId, $academyId) {
                $query->where('student_id', $student->id);
            }])
            ->where([
                ['term_id', $termId],
                ['academic_id', $academyId],
                ['type', 'affective']
            ])->limit(100)->get();
            $psychomotorNormal = Psychomotor::with(['psychomotorStudent' => function($query) use($student, $termId, $academyId) {
                $query->where('student_id', $student->id);
            }])
            ->where([
                ['term_id', $termId],
                ['academic_id', $academyId],
                ['type', 'psychomotor']
            ])->limit(100)->get();
            // Check for missing psychomotor ratings for current term and academic year
            $missingAffectiveRatings = [];
            $missingPsychomotorRatings = [];
            foreach ($psychomotorAffective as $affective) {
                if (!$affective->psychomotorStudent || count($affective->psychomotorStudent) === 0) {
                    $missingAffectiveRatings[] = $affective->name;
                }
            }
            foreach ($psychomotorNormal as $psychomotor) {
                if (!$psychomotor->psychomotorStudent || count($psychomotor->psychomotorStudent) === 0) {
                    $missingPsychomotorRatings[] = $psychomotor->name;
                }
            }
            if (count($missingAffectiveRatings) > 0 || count($missingPsychomotorRatings) > 0) {
                $missingData = [];
                if (count($missingAffectiveRatings) > 0) {
                    $missingData[] = 'Affective Domain Ratings: ' . implode(', ', $missingAffectiveRatings);
                }
                if (count($missingPsychomotorRatings) > 0) {
                    $missingData[] = 'Psychomotor Domain Ratings: ' . implode(', ', $missingPsychomotorRatings);
                }
                throw new \Exception('The following psychomotor ratings are missing for ' . $termAndAcademy['term']->name . ' ' . $termAndAcademy['academy']->title . ' and must be filled before generating the PDF: ' . implode('; ', $missingData));
            }
            $headings = ResultSectionType::with('resultSection')
                ->where('term_id', $termId)
                ->whereHas('resultSection', function ($query) use ($student) {
                    $query->where('group_id', $student->class->group->id);
                })
                ->get();
            if ($headings->isEmpty()) {
                throw new Exception('Result section types are not configured for this student\'s group.');
            }
            $groupedHeadings = $headings->groupBy('calc_pattern');
            $totalHeadings = $headings->where('calc_pattern', 'total');
            $markObtained = $headings->whereIn('calc_pattern', ['input', 'total']) ?? collect([]);
            $studentSummary = $headings->whereIn('calc_pattern', ['position', 'grade_level']) ?? collect([]);
            $termSummary = $headings->whereIn('calc_pattern', ['class_average', 'class_highest_score', 'class_lowest_score']) ?? collect([]);
            $remarks = $headings->whereIn('calc_pattern', ['remarks']) ?? collect([]);
            // Batch process averages and positions using loaded data only
            foreach ($allStudents as $oneStudent) {
                $studentCourses = $oneStudent->courseForms;
                if ($studentCourses->isEmpty()) continue;
                $studentTotal = 0;
                $subjectCount = 0;
                foreach ($studentCourses as $course) {
                    $subjectCount++;
                    $score = $course->scoreBoard
                        ->whereIn('result_section_type_id', $totalHeadings->pluck('id'))
                        ->sum(function ($item) {
                            return (int) $item->score;
                        });
                    $studentTotal += $score;
                }
                if ($subjectCount > 0) {
                    $studentAverage = $studentTotal / $subjectCount;
                    $studentAverages[] = [
                        'student_id' => $oneStudent->id,
                        'average' => round($studentAverage, 2)
                    ];
                }
            }
            usort($studentAverages, function ($a, $b) {
                return $b['average'] <=> $a['average'];
            });
            $position = null;
            foreach ($studentAverages as $index => $data) {
                if ($data['student_id'] == $student->id) {
                    $position = $index + 1;
                    break;
                }
            }
            $classAverage = 0;
            if (count($studentAverages) > 0) {
                $classAverage = round(array_sum(array_column($studentAverages, 'average')) / count($studentAverages), 2);
            }
            $class = SchoolClass::with('teacher')->where('id', $student->class->id)->first() ?? collect([]);
            $scoreData = $courses->reduce(function ($carry, $course) use ($totalHeadings) {
                $subject = strtolower($course->subject->subjectDepot->name);
                $score = $course->scoreBoard
                    ->whereIn('result_section_type_id', $totalHeadings->pluck('id'))
                    ->sum(function($item) {
                        return (int) $item->score;
                    });
                $carry['totalScore'] += $score;
                if (str_starts_with($subject, 'english') || str_starts_with($subject, 'literacy')) {
                    $carry['englishScore'] = $score;
                } elseif (str_starts_with($subject, 'math') || str_starts_with($subject, 'numeracy')) {
                    $carry['mathScore'] = $score;
                }
                return $carry;
            }, ['totalScore' => 0, 'englishScore' => 0, 'mathScore' => 0]);
            $totalScore = 0;
            $englishScore = 0;
            $mathScore = 0;
            $totalScore = $courses->reduce(function ($carry, $course) use ($totalHeadings, &$englishScore, &$mathScore) {
                foreach ($totalHeadings as $heading) {
                    $score = $course->scoreBoard->firstWhere('result_section_type_id', $heading->id);
                    $scoreValue = (int) ($score->score ?? 0);
                    $carry += $scoreValue;
                    $subject = $course->subject->subjectDepot->name;
                    if ((strncasecmp($subject, 'english', 7) === 0) || (strncasecmp($subject, 'literacy', 8) === 0)){
                        $englishScore = $scoreValue;
                    } elseif ((strncasecmp($subject,'math', 4)  == 0)|| (strncasecmp($subject, 'numeracy', 8) === 0)) {
                        $mathScore = $scoreValue;
                    }
                }
                return $carry;
            }, 0);
            $percent = round($scoreData['totalScore'] / $courses->count());
            $principalComment = self::getPerformanceComment($percent, $englishScore, $mathScore);
            $totalSubject =count($courses);
            $classStudents = $allStudents;
            $currentStudentAverage = 0;
            $currentStudentPosition = null;
            foreach ($studentAverages as $index => $data) {
                if ($data['student_id'] == $student->id) {
                    $currentStudentAverage = $data['average'];
                    $currentStudentPosition = $index + 1;
                    break;
                }
            }
            $classAverageScore = count($studentAverages) > 0
                ? array_sum(array_column($studentAverages, 'average')) / count($studentAverages)
                : 0;
            // Only apply promotion logic if third term
            $isThirdTerm = false;
            if ($termAndAcademy['term'] && (stripos($termAndAcademy['term']->name, 'third') !== false || $termAndAcademy['term']->id == 3 || stripos($termAndAcademy['term']->name, '3') !== false)) {
                $isThirdTerm = true;
            }
            $promoted = null;
            $promotionCriteria = null;
            if ($isThirdTerm) {
                $promoted = $this->determinePromotion($student, $courses, $currentStudentAverage);
                $promotionCriteria = $this->promotionCriteria[$student->class_id] ?? ['min_average' => 50, 'required_subjects' => []];
            }
            $resultData =[
                'class'=>$class,
                'totalSubject'=>$totalSubject,
                'totalScore'=>$totalScore,
                'percent'=>$percent,
                'markObtained'=>$markObtained,
                'remarks'=>$remarks,
                'studentSummary'=> $studentSummary,
                'termSummary'=>$termSummary,
                'courses'=>$courses,
                'student'=>$student,
                'classAverage' => $classAverage,
                'totalStudents' => $totalStudents,
                'studentPosition' => $position,
                'principalComment' => $principalComment,
                'studentAverage' => $currentStudentAverage,
                'promoted' => $promoted,
                'promotionCriteria' => $promotionCriteria,
            ];
            // Generate PDF and cache path (pseudo, replace with your PDF logic)
            // $pdfPath = $this->generateAndStorePdf($resultData); // Implement this method as needed
            // Cache::put($cacheKey, $pdfPath, now()->addMinutes(10));
            // return response()->file($pdfPath);
            // For now, just return the view as before
            return view('exam.result', compact(
                'student',
                'class',
                'scoreData',
                'principalComment',
                'totalHeadings',
                'percent',
                'groupedHeadings',
                'headings',
                'psychomotorAffective',
                'psychomotorNormal',
                'termAndAcademy',
                'relatedData',
                'resultData'
            ));
        } catch (\Exception $e) {
            return view('exam.error', [
                'error' => $e->getMessage(),
                'previousUrl' => url()->previous()
            ]);
        }
    }
    public function takeExam($examId)
    {

        $exam = Exam::with([
            'subject',
            'subject.subjectDepot', // Load the subjectDepot through subject
            'questions' // Load the related questions
        ])->findOrFail($examId);
        $user = Auth::user();
        $student = Student::with('class')->whereEmail($user->email)->firstOrFail();
        $course = CourseForm::where('subject_id', $exam->subject_id)
        ->where('student_id', $student->id)
        ->where('academic_year_id', $exam->academic_year_id)
        ->firstOrFail();

        $term = Term::where('id', $exam->term_id)->first();
        $academy = AcademicYear::where('id', $exam->academic_year_id)->first();

        $questions = $exam->questions->toArray();
        $quizScore = QuizScore::where('exam_id', $exam->id)
        ->where('student_id', $student->id)
        ->first();
        $answers = QuizSubmission::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->get()
                    ->toArray();
        shuffle($questions);
        // dd($exam);
        return view('exam.react.take_exam',compact('student', 'exam','course', 'questions', 'answers', 'quizScore', 'term', 'academy'));
    }


    public static function getPerformanceComment($percentage, $englishScore, $mathScore) {
        // Define comments based on percentage range
        $comments = [
            'excellent' => [
                "Excellent Performance",
                "Keep it up",
                "Do not relent on your efforts. Keep it up"
            ],
            'very_good' => [
                "A Very Good Performance",
                "You are doing well. Keep pushing",
                "An impressive effort!"
            ],
            'hardworking' => [
                "A Hardworking Learner",
                "Your effort is commendable",
                "Good work, but you can achieve more"
            ],
            'work_harder' => [
                "Work Harder For a better result",
                "Keep improving",
                "Better focus next term will yield results"
            ],
            'put_in_effort' => [
                "Put In More Effort",
                "Your performance can improve",
                "Stay committed to better outcomes"
            ],
            'be_serious' => [
                "Be more serious in your studies",
                "Focus more on your academic goals",
                "A stronger commitment is needed"
            ],
            'english_math_fail' => [
                "Tried but should try harder next term for a better result in English and Mathematics",
                "Put in more effort in English and Mathematics",
                "A good result but can do better in English and Mathematics"
            ],
          'math_fail' => [
                "Tried but should try harder next term for a better result in Mathematics",
                "Put in more effort in Mathematics",
                "A good result but can do better in Mathematics"
            ],
          'english_fail' => [
                "Tried but should try harder next term for a better result in English",
                "Put in more effort in English",
                "A good result but can do better in English"
            ],
            'good_result' => [
                "Good results but can be better if you put in more effort",
                "Excellent Performance. Keep it up",
                "A good result. Keep it up"
            ],
            'improve_next_term' => [
                "Work harder next term for better results",
                "There is still room for improvement next term",
                "Stay focused for better performance next term"
            ]
        ];
    if ($percentage >= 70) {
        // Check English and Mathematics scores first
        if ($englishScore < 50 && $mathScore < 50) {
                return $comments['english_math_fail'][array_rand($comments['english_math_fail'])];
        } elseif ($englishScore < 50) {
            return $comments['english_fail'][array_rand($comments['english_fail'])];
        } elseif ($mathScore < 50) {
          return $comments['math_fail'][array_rand($comments['math_fail'])];
        }
      }

        // Determine the comment based on percentage
        if ($percentage >= 86) {
            $performanceComment = $comments['excellent'][array_rand($comments['excellent'])];
        } elseif ($percentage >= 75) {
            $performanceComment = $comments['very_good'][array_rand($comments['very_good'])];
        } elseif ($percentage >= 65) {
            $performanceComment = $comments['hardworking'][array_rand($comments['hardworking'])];
        } elseif ($percentage >= 51) {
            $performanceComment = $comments['work_harder'][array_rand($comments['work_harder'])];
        } elseif ($percentage >= 41) {
            $performanceComment = $comments['put_in_effort'][array_rand($comments['put_in_effort'])];
        } else {
            $performanceComment = $comments['be_serious'][array_rand($comments['be_serious'])];
        }

        return $performanceComment;
    }


public function saveExamData(Request $request)
    {
        $validatedData = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'course_form_id' => 'required|exists:course_forms,id',
            'recording_path' => 'nullable|string', // Optional field
            'total_score' => 'required|numeric',
            'answers' => 'required|array', // Array of answers
            'answers.*.question_id' => 'required|exists:question_banks,id',
            'answers.*.answer' => 'nullable|string',
            'answers.*.score' => 'required|numeric',
            'answers.*.correct' => 'required|boolean',
            'answers.*.comments' => 'nullable|string'
        ]);

        try {
            // Save exam recording if provided
            if (!empty($validatedData['recording_path'])) {
                ExamRecording::create([
                    'exam_id' => $validatedData['exam_id'],
                    'student_id' => $validatedData['student_id'],
                    'recording_path' => $validatedData['recording_path'],
                    'recorded_at' => now(),
                ]);
            }

            // Save or update QuizScore
            $quizScore = QuizScore::updateOrCreate(
                [
                    'course_form_id' => $validatedData['course_form_id'],
                    'student_id' => $validatedData['student_id'],
                    'exam_id' => $validatedData['exam_id']
                ],
                ['total_score' => $validatedData['total_score'], 'comments' => "submitted"]
            );

            // Save or update Quiz Submissions (Loop through answers)
            foreach ($validatedData['answers'] as $answerData) {
                QuizSubmission::updateOrCreate(
                    [
                        'course_form_id' => $validatedData['course_form_id'],
                        'student_id' => $validatedData['student_id'],
                        'exam_id' => $validatedData['exam_id'],
                        'question_id' => $answerData['question_id']
                    ],
                    [
                        'quiz_score_id' => $quizScore->id,
                        'answer' => $answerData['answer'] ?? null,
                        'score' => $answerData['score'],
                        'correct' => $answerData['correct'],
                        'comments' => $answerData['comments'] ?? "submitted",
                    ]
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Exam data saved successfully!',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save exam data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
