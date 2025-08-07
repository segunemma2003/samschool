<?php

declare(strict_types=1);

use App\Filament\Ourstudent\Pages\ExamFinalSubmissionPage;
use App\Filament\Ourstudent\Pages\ExamInstructions;
use App\Filament\Ourstudent\Pages\ExamPage;
use App\Filament\Ourstudent\Pages\ExamReviewPage;
use App\Filament\Teacher\Pages\AssignmentStudentView;
use App\Filament\Teacher\Pages\SubmittedStudentsList;
use App\Filament\Teacher\Resources\AssignmentResource\Pages\ViewSubmittedAssignmentTeacher;
use App\Filament\Teacher\Resources\ExamResource\Pages\ExamStudentDetails;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\PaymentController;
use App\Models\Payroll;
use App\Models\SchoolInvoice;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    'universal',
    \TomatoPHP\FilamentTenancy\FilamentTenancyServiceProvider::TENANCY_IDENTIFICATION,
])->group(function () {
    // dd(tenant('id'));

    if(config('filament-tenancy.features.impersonation')) {
        Route::get('/login/url', [\TomatoPHP\FilamentTenancy\Http\Controllers\LoginUrl::class, 'index']);
    }
    // dd(config('filament-tenancy.central_domain'));
    if (config('filament-tenancy.central_domain') !== request()->getHost()) {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');

        // Route::get('/chat', function () {
        //     return redirect()->route('filament.ourstudent.pages.chat');
        // })->middleware(['auth']);
    });
    Route::get('/invoices/{invoice}/pdf', function (SchoolInvoice $invoice) {
        return app(\App\Services\InvoicePdfGenerator::class)->generate($invoice);
    })->name('invoices.pdf');
    Route::post('/payment/callback', [PaymentController::class, 'handleCallback'])
    ->name('payment.callback');
    Route::get('/payslips/{payroll}/pdf', function (Payroll $payroll) {
        return app(\App\Services\PayslipPdfGenerator::class)->generate($payroll);
    })->name('payslips.pdf');
    Route::get('/exam-page/{records}', ExamPage::class)->name('exam.page');
    Route::get('/exam-instructions', ExamInstructions::class)->name('exam.instructions');
    Route::get('/exam/review', ExamReviewPage::class)->name('exam.review');
    Route::get('/exam/final-submission', ExamFinalSubmissionPage::class)->name('exam.final_submission');
    Route::get('/submitted-students/{assignment}', SubmittedStudentsList::class)->name('filament.pages.submitted-students-list');
    Route::get('/exam/{quizScoreId}/details', ExamStudentDetails::class)->name('student.details.exam');


    Route::get('/exam/{exam}/student', [ExamController::class, 'takeExam'])->name('student.exam.take');

    Route::get('/result/{studentId}/student/{termId}/term/{academyId}', [ExamController::class, 'generatePdf'])->name('student.result.check');
    }

    // Test route to verify preview functionality
    Route::get('/test/preview/{studentId}/{termId}/{academicYearId}', function ($studentId, $termId, $academicYearId) {
        try {
            // Check if student has course forms for this term and academic year
            $courseForms = \App\Models\CourseForm::where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('academic_year_id', $academicYearId)
                ->get();

            if ($courseForms->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No course forms found for this student in the selected term and academic year',
                    'student_id' => $studentId,
                    'term_id' => $termId,
                    'academic_year_id' => $academicYearId
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Course forms found',
                'count' => $courseForms->count(),
                'student_id' => $studentId,
                'term_id' => $termId,
                'academic_year_id' => $academicYearId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    })->name('test.preview');

    // Student Result Routes for Multi-Tenant Context
    Route::get('/student/result/preview/{studentId}/{termId}/{academicYearId}', function ($studentId, $termId, $academicYearId) {
        try {
            // Check if student has course forms for this term and academic year
            $courseForms = \App\Models\CourseForm::where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('academic_year_id', $academicYearId)
                ->get();

            if ($courseForms->isEmpty()) {
                abort(404, 'No course forms found for this student in the selected term and academic year');
            }

            // Get basic data
            $student = \App\Models\Student::with(['class'])->findOrFail($studentId);
            $term = \App\Models\Term::findOrFail($termId);
            $academicYear = \App\Models\AcademicYear::findOrFail($academicYearId);

            // Get school information
            $school = \App\Models\SchoolInformation::where([
                ['term_id', $termId],
                ['academic_id', $academicYearId]
            ])->first();

            // Get student comment
            $studentComment = \App\Models\StudentComment::where([
                ['student_id', $studentId],
                ['term_id', $termId],
                ['academic_id', $academicYearId]
            ])->first();

            // Get course forms with scores
            $courseForms = \App\Models\CourseForm::with([
                'subject.subjectDepot',
                'subject.teacher',
                'scoreBoard.resultSectionType'
            ])
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->get();

            // Get result section types
            $classId = $student->class_id ?? $student->group_id;
            $resultSectionTypes = \App\Models\ResultSectionType::where('term_id', $termId)
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

            // Calculate total score
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

                if ($scoreCount > 0) {
                    $totalScore += $subjectTotal;
                    $subjectsWithScores++;
                }
            }

            $percent = $subjectsWithScores > 0 ? round($totalScore / $subjectsWithScores, 1) : 0;

            // Get principal comment
            $calculationService = new \App\Services\StudentResultCalculationService();
            $principalComment = $calculationService->getPrincipalComment($percent);

            // Get attendance data
            $studentAttendance = \App\Models\StudentAttendanceSummary::where([
                ['term_id', $termId],
                ['student_id', $studentId],
                ['academic_id', $academicYearId]
            ])->first();

            // Get next term
            $nextTerm = \App\Models\Term::where('starting_date', '>', $term->ending_date)
                ->orderBy('starting_date')
                ->first();

            // Get psychomotor/behavioral data
            $psychomotorData = \App\Models\PyschomotorStudent::with('psychomotor')
                ->whereHas('psychomotor', function ($query) use ($termId, $academicYearId) {
                    $query->where('term_id', $termId)
                          ->where('academic_id', $academicYearId);
                })
                ->where('student_id', $studentId)
                ->get();

            // Organize behavioral data
            $behavioralData = [];

            // Get all terms in the academic year
            $allTerms = \App\Models\Term::where('academic_year_id', $academicYearId)
                ->orderBy('starting_date')
                ->get();

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

            // Get annual summary data
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
                    $prevCourseForm = \App\Models\CourseForm::with('scoreBoard.resultSectionType')
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
                $currentTermAvg = $percent;
                $totalAvg += $currentTermAvg;
                $termCount++;

                $annualSummaryData[$subjectId]['year_avg'] = $termCount > 0 ? $totalAvg / $termCount : 0;
            }

            // Prepare data for template - using the same structure as the working test route
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
                'studentAttendance' => $studentAttendance,
                'nextTerm' => $nextTerm,
                'behavioralData' => $behavioralData,
                'resultSectionTypes' => $resultSectionTypes,
                'annualSummaryData' => $annualSummaryData,
            ];

            return view('results.template', $data);

        } catch (\Exception $e) {
            abort(500, 'Error generating result preview: ' . $e->getMessage());
        }
    })->name('student.result.preview');

    Route::get('/student/result/download/{studentId}/{termId}/{academicYearId}', function ($studentId, $termId, $academicYearId) {
        $calculationService = new \App\Services\StudentResultCalculationService();

        try {
            // Check if student has course forms for this term and academic year
            $courseForms = \App\Models\CourseForm::where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('academic_year_id', $academicYearId)
                ->get();

            if ($courseForms->isEmpty()) {
                abort(404, 'No course forms found for this student in the selected term and academic year');
            }

            // Generate PDF on-the-fly
            $pdfUrl = $calculationService->generateStudentResultPdf($studentId, $termId, $academicYearId);

            // Redirect to the generated PDF URL
            return redirect($pdfUrl);

        } catch (\Exception $e) {
            abort(500, 'Error generating result PDF: ' . $e->getMessage());
        }
    })->name('student.result.download');

    // Debug route to test preview functionality
    Route::get('/debug/preview/{studentId}/{termId}/{academicYearId}', function ($studentId, $termId, $academicYearId) {
        try {
            // Check if student has course forms for this term and academic year
            $courseForms = \App\Models\CourseForm::where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('academic_year_id', $academicYearId)
                ->get();

            if ($courseForms->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No course forms found for this student in the selected term and academic year',
                    'student_id' => $studentId,
                    'term_id' => $termId,
                    'academic_year_id' => $academicYearId
                ], 404);
            }

            // Get basic student info
            $student = \App\Models\Student::with(['class'])->findOrFail($studentId);
            $term = \App\Models\Term::findOrFail($termId);
            $academicYear = \App\Models\AcademicYear::findOrFail($academicYearId);

            return response()->json([
                'success' => true,
                'message' => 'Data found successfully',
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'class' => $student->class ? $student->class->name : null
                ],
                'term' => [
                    'id' => $term->id,
                    'name' => $term->name
                ],
                'academic_year' => [
                    'id' => $academicYear->id,
                    'title' => $academicYear->title
                ],
                'course_forms_count' => $courseForms->count(),
                'course_forms' => $courseForms->map(function($cf) {
                    return [
                        'id' => $cf->id,
                        'subject_id' => $cf->subject_id,
                        'subject_name' => $cf->subject ? $cf->subject->name : 'Unknown',
                        'scores_count' => $cf->scoreBoard ? $cf->scoreBoard->count() : 0
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    })->name('debug.preview');

    // Simple test route to check if template rendering works
    Route::get('/test/template', function () {
        return view('results.template', [
            'student' => \App\Models\Student::find(1),
            'term' => \App\Models\Term::find(1),
            'academy' => \App\Models\AcademicYear::find(1),
            'class' => null,
            'school' => null,
            'studentComment' => null,
            'courses' => collect(),
            'markObtained' => collect(),
            'studentSummary' => collect(),
            'termSummary' => collect(),
            'remarks' => collect(),
            'totalScore' => 0,
            'totalSubject' => 0,
            'percent' => 0,
            'principalComment' => 'Test Comment',
            'studentAttendance' => null,
            'nextTerm' => null,
            'behavioralData' => [],
            'resultSectionTypes' => collect(),
            'annualSummaryData' => [],
        ]);
    })->name('test.template');

    // Detailed debug route to test preview data processing
    Route::get('/debug/preview-detailed/{studentId}/{termId}/{academicYearId}', function ($studentId, $termId, $academicYearId) {
        try {
            $debug = [];

            // Step 1: Check course forms
            $courseForms = \App\Models\CourseForm::where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('academic_year_id', $academicYearId)
                ->get();

            $debug['step1_course_forms'] = [
                'count' => $courseForms->count(),
                'found' => !$courseForms->isEmpty()
            ];

            if ($courseForms->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No course forms found',
                    'debug' => $debug
                ], 404);
            }

            // Step 2: Get student
            $student = \App\Models\Student::with(['class'])->findOrFail($studentId);
            $debug['step2_student'] = [
                'id' => $student->id,
                'name' => $student->name,
                'class_id' => $student->class_id,
                'class_name' => $student->class ? $student->class->name : null
            ];

            // Step 3: Get term and academic year
            $term = \App\Models\Term::findOrFail($termId);
            $academicYear = \App\Models\AcademicYear::findOrFail($academicYearId);
            $debug['step3_basic_data'] = [
                'term' => ['id' => $term->id, 'name' => $term->name],
                'academic_year' => ['id' => $academicYear->id, 'title' => $academicYear->title]
            ];

            // Step 4: Get school information
            $school = \App\Models\SchoolInformation::where([
                ['term_id', $termId],
                ['academic_id', $academicYearId]
            ])->first();
            $debug['step4_school'] = [
                'found' => $school !== null,
                'data' => $school ? $school->toArray() : null
            ];

            // Step 5: Get result section types
            $classId = $student->class_id ?? $student->group_id;
            $resultSectionTypes = \App\Models\ResultSectionType::where('term_id', $termId)
                ->whereHas('resultSection', function ($query) use ($classId) {
                    $query->where('group_id', $classId);
                })
                ->orderBy('name')
                ->get();

            $debug['step5_result_section_types'] = [
                'class_id' => $classId,
                'count' => $resultSectionTypes->count(),
                'types' => $resultSectionTypes->map(function($rst) {
                    return [
                        'id' => $rst->id,
                        'name' => $rst->name,
                        'calc_pattern' => $rst->calc_pattern
                    ];
                })
            ];

            // Step 6: Test calculation service
            try {
                $calculationService = new \App\Services\StudentResultCalculationService();
                $principalComment = $calculationService->getPrincipalComment(75);
                $debug['step6_calculation_service'] = [
                    'success' => true,
                    'principal_comment' => $principalComment
                ];
            } catch (\Exception $e) {
                $debug['step6_calculation_service'] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Debug data collected successfully',
                'debug' => $debug
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'debug' => $debug ?? []
            ], 500);
        }
    })->name('debug.preview.detailed');

    // Test route to show exact template error
    Route::get('/test/template-error', function () {
        try {
            return view('results.template', [
                'student' => \App\Models\Student::find(1),
                'term' => \App\Models\Term::find(1),
                'academy' => \App\Models\AcademicYear::find(1),
                'class' => null,
                'school' => null,
                'studentComment' => null,
                'courses' => collect(),
                'markObtained' => collect(),
                'studentSummary' => collect(),
                'termSummary' => collect(),
                'remarks' => collect(),
                'totalScore' => 0,
                'totalSubject' => 0,
                'percent' => 0,
                'principalComment' => 'Test Comment',
                'studentAttendance' => null,
                'nextTerm' => null,
                'behavioralData' => [],
                'resultSectionTypes' => collect(),
                'annualSummaryData' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    })->name('test.template.error');


    // Route::get('/teacher/assignment/{assignment}/student/{student}', ViewSubmittedAssignmentTeacher::class)->name('filament.pages.assignment-student-view');
});
