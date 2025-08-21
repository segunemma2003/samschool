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
        // Cast parameters to integers
        $studentId = (int) $studentId;
        $termId = (int) $termId;
        $academicYearId = (int) $academicYearId;

        try {
            // Use the same logic as ExamController to get fresh data
            $student = \App\Models\Student::with(['class', 'class.group'])->whereId($studentId)->firstOrFail();

            if (!$student->class) {
                return response()->view('errors.student-data', [
                    'error' => 'Student is not assigned to any class',
                    'solution' => 'Please assign the student to a class first',
                    'student_id' => $studentId,
                    'student_name' => $student->name
                ], 400);
            }

            // Remove group requirement - system will work without groups
            // if (!$student->class->group) {
            //     return response()->view('errors.student-data', [
            //         'error' => 'Student\'s class is not assigned to any school section',
            //         'solution' => 'Please assign the class to a school section first',
            //         'student_id' => $studentId,
            //         'student_name' => $student->name,
            //         'class_name' => $student->class->name
            //     ], 400);
            // }

            $term = \App\Models\Term::findOrFail($termId);
            $academicYear = \App\Models\AcademicYear::findOrFail($academicYearId);

            // Get course forms with scores (same as view result page)
            $courses = \App\Models\CourseForm::with([
                'subject.subjectDepot',
                'subject.teacher',
                'scoreBoard.resultSectionType'
            ])
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->get();

            if ($courses->isEmpty()) {
                abort(404, 'No courses found for this student in the selected term and academic year.');
            }

            // Get result section types (same as view result page) - modified to work without groups
            $resultSectionTypes = \App\Models\ResultSectionType::with('resultSection')
                ->where('term_id', $termId)
                ->get();

            if ($resultSectionTypes->isEmpty()) {
                // If no result section types found, create a simple fallback
                $resultSectionTypes = collect([
                    (object) [
                        'id' => 1,
                        'name' => 'Total',
                        'code' => 'total',
                        'calc_pattern' => 'total',
                        'type' => 'total',
                        'score_weight' => 100
                    ]
                ]);
            }

            // Group headings by calc_pattern (same as view result page)
            $groupedHeadings = $resultSectionTypes->groupBy('calc_pattern');
            $totalHeadings = $resultSectionTypes->where('calc_pattern', 'total');
            $markObtained = $resultSectionTypes->whereIn('calc_pattern', ['input', 'total']) ?? collect([]);
            $studentSummary = $resultSectionTypes->whereIn('calc_pattern', ['position', 'grade_level']) ?? collect([]);
            $termSummary = $resultSectionTypes->whereIn('calc_pattern', ['class_average', 'class_highest_score', 'class_lowest_score']) ?? collect([]);
            $remarks = $resultSectionTypes->whereIn('calc_pattern', ['remarks']) ?? collect([]);

            // Calculate scores (same as view result page)
            $totalScore = 0;
            $totalSubject = count($courses);
            $percent = 0;

            if ($totalSubject > 0) {
                $totalScore = $courses->reduce(function ($carry, $course) use ($totalHeadings) {
                    $score = $course->scoreBoard
                        ->whereIn('result_section_type_id', $totalHeadings->pluck('id'))
                        ->sum(function($item) {
                            return (int) $item->score;
                        });
                    return $carry + $score;
                }, 0);

                $percent = round($totalScore / $totalSubject);
            }

            // Get other data
            $school = \App\Models\SchoolInformation::where([
                ['term_id', $termId],
                ['academic_id', $academicYearId]
            ])->first();

            $studentComment = \App\Models\StudentComment::where([
                ['student_id', $studentId],
                ['term_id', $termId],
                ['academic_id', $academicYearId]
            ])->first();

            $studentAttendance = \App\Models\StudentAttendanceSummary::where([
                ['term_id', $termId],
                ['student_id', $studentId],
                ['academic_id', $academicYearId]
            ])->first();

            // Get psychomotor/behavioral data
            $psychomotorData = \App\Models\PyschomotorStudent::with(['psychomotor.psychomotorCategory'])
                ->whereHas('psychomotor', function ($query) use ($termId, $academicYearId) {
                    $query->where('term_id', $termId)
                          ->where('academic_id', $academicYearId);
                })
                ->where('student_id', $studentId)
                ->get();

            // Get psychomotor categories for this term and academic year
            $psychomotorCategory = \App\Models\PsychomotorCategory::with(['psychomotors' => function ($query) use ($termId, $academicYearId) {
                $query->where('term_id', $termId)
                      ->where('academic_id', $academicYearId);
            }])
            ->whereHas('psychomotors', function ($query) use ($termId, $academicYearId) {
                $query->where('term_id', $termId)
                      ->where('academic_id', $academicYearId);
            })
            ->get();

            // Organize behavioral data by category and term
            $behavioralData = [];
            $allTerms = \App\Models\Term::all();
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

            // Get student result to access calculated_data
            $studentResult = \App\Models\StudentResult::where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('academic_year_id', $academicYearId)
                ->where('calculation_status', 'completed')
                ->first();

            if (!$studentResult) {
                abort(404, 'Result not ready yet. Please ensure the teacher has viewed and commented on the student result first.');
            }

            // Get calculated data from StudentResult
            $calculatedData = $studentResult->calculated_data;
            $summary = $calculatedData['summary'] ?? [];
            $subjects = $calculatedData['subjects'] ?? [];
            $headings = $calculatedData['headings'] ?? [];

            // Get unique codes from subjects for Marks Obtained section
            $inputCodes = [];
            foreach ($subjects as $subject) {
                if (isset($subject['scores']) && is_array($subject['scores'])) {
                    foreach ($subject['scores'] as $score) {
                        if (isset($score['calc_pattern']) && $score['calc_pattern'] === 'input') {
                            $inputCodes[$score['code']] = $score['code'];
                        }
                    }
                }
            }

            // Prepare data for template using calculated data
            $data = [
                'student' => $student,
                'term' => $term,
                'academy' => $academicYear,
                'class' => $student->class,
                'school' => $school,
                'studentComment' => $studentComment,
                'studentAttendance' => $studentAttendance,
                'nextTerm' => null,
                'behavioralData' => $behavioralData,
                'psychomotorCategory' => $psychomotorCategory,
                'psychomotorData' => $psychomotorData,
                'annualSummaryData' => [],
                // Use calculated data
                'totalScore' => $summary['total_score'] ?? $totalScore,
                'totalSubject' => $summary['total_subjects'] ?? $totalSubject,
                'percent' => $summary['average'] ?? $percent,
                'principalComment' => $studentResult->teacher_comment ?? 'No comment available',
                'subjects' => $subjects,
                'inputCodes' => array_values($inputCodes), // Pass unique input codes
                'headings' => $headings,
                'summary' => $summary,
                'studentResult' => $studentResult, // Add studentResult for calculation_total
            ];

            return view('results.template', $data);

        } catch (\Exception $e) {
            abort(500, 'Error generating result preview: ' . $e->getMessage());
        }
    })->name('student.result.preview');

    Route::get('/student/result/download/{studentId}/{termId}/{academicYearId}', function ($studentId, $termId, $academicYearId) {
        // Cast parameters to integers
        $studentId = (int) $studentId;
        $termId = (int) $termId;
        $academicYearId = (int) $academicYearId;

        // Set memory and time limits for PDF generation
        ini_set('memory_limit', '512M');
        set_time_limit(120); // 2 minutes timeout

        try {
            // Use the same logic as ExamController to get fresh data
            $student = \App\Models\Student::with(['class', 'class.group'])->whereId($studentId)->firstOrFail();

            if (!$student->class) {
                return response()->view('errors.student-data', [
                    'error' => 'Student is not assigned to any class',
                    'solution' => 'Please assign the student to a class first',
                    'student_id' => $studentId,
                    'student_name' => $student->name
                ], 400);
            }

            // Remove group requirement - system will work without groups
            // if (!$student->class->group) {
            //     return response()->view('errors.student-data', [
            //         'error' => 'Student\'s class is not assigned to any school section',
            //         'solution' => 'Please assign the class to a school section first',
            //         'student_id' => $studentId,
            //         'student_name' => $student->name,
            //         'class_name' => $student->class->name,
            //         'no_groups_exist' => true
            //     ], 400);
            // }

            $term = \App\Models\Term::findOrFail($termId);
            $academicYear = \App\Models\AcademicYear::findOrFail($academicYearId);

            // Get course forms with scores (same as view result page)
            $courses = \App\Models\CourseForm::with([
                'subject.subjectDepot',
                'subject.teacher',
                'scoreBoard.resultSectionType'
            ])
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->get();

            if ($courses->isEmpty()) {
                abort(404, 'No courses found for this student in the selected term and academic year.');
            }

            // Get result section types (same as view result page) - modified to work without groups
            $resultSectionTypes = \App\Models\ResultSectionType::with('resultSection')
                ->where('term_id', $termId)
                ->get();

            if ($resultSectionTypes->isEmpty()) {
                // If no result section types found, create a simple fallback
                $resultSectionTypes = collect([
                    (object) [
                        'id' => 1,
                        'name' => 'Total',
                        'code' => 'total',
                        'calc_pattern' => 'total',
                        'type' => 'total',
                        'score_weight' => 100
                    ]
                ]);
            }

            // Group headings by calc_pattern (same as view result page)
            $groupedHeadings = $resultSectionTypes->groupBy('calc_pattern');
            $totalHeadings = $resultSectionTypes->where('calc_pattern', 'total');
            $markObtained = $resultSectionTypes->whereIn('calc_pattern', ['input', 'total']) ?? collect([]);
            $studentSummary = $resultSectionTypes->whereIn('calc_pattern', ['position', 'grade_level']) ?? collect([]);
            $termSummary = $resultSectionTypes->whereIn('calc_pattern', ['class_average', 'class_highest_score', 'class_lowest_score']) ?? collect([]);
            $remarks = $resultSectionTypes->whereIn('calc_pattern', ['remarks']) ?? collect([]);

            // Calculate scores (same as view result page)
            $totalScore = 0;
            $totalSubject = count($courses);
            $percent = 0;

            if ($totalSubject > 0) {
                $totalScore = $courses->reduce(function ($carry, $course) use ($totalHeadings) {
                    $score = $course->scoreBoard
                        ->whereIn('result_section_type_id', $totalHeadings->pluck('id'))
                        ->sum(function($item) {
                            return (int) $item->score;
                        });
                    return $carry + $score;
                }, 0);

                $percent = round($totalScore / $totalSubject);
            }

            // Get other data
            $school = \App\Models\SchoolInformation::where([
                ['term_id', $termId],
                ['academic_id', $academicYearId]
            ])->first();

            $studentComment = \App\Models\StudentComment::where([
                ['student_id', $studentId],
                ['term_id', $termId],
                ['academic_id', $academicYearId]
            ])->first();

            $studentAttendance = \App\Models\StudentAttendanceSummary::where([
                ['term_id', $termId],
                ['student_id', $studentId],
                ['academic_id', $academicYearId]
            ])->first();

            // Get psychomotor/behavioral data with error handling
            try {
                $psychomotorData = \App\Models\PyschomotorStudent::with(['psychomotor.psychomotorCategory'])
                    ->whereHas('psychomotor', function ($query) use ($termId, $academicYearId) {
                        $query->where('term_id', $termId)
                              ->where('academic_id', $academicYearId);
                    })
                    ->where('student_id', $studentId)
                    ->get();

                // Get psychomotor categories for this term and academic year
                $psychomotorCategory = \App\Models\PsychomotorCategory::with(['psychomotors' => function ($query) use ($termId, $academicYearId) {
                    $query->where('term_id', $termId)
                          ->where('academic_id', $academicYearId);
                }])
                ->whereHas('psychomotors', function ($query) use ($termId, $academicYearId) {
                    $query->where('term_id', $termId)
                          ->where('academic_id', $academicYearId);
                })
                ->get();
            } catch (\Exception $e) {
                // If psychomotor data fails, use empty collections
                $psychomotorData = collect();
                $psychomotorCategory = collect();
            }

            // Ensure variables are always defined as collections
            $psychomotorData = $psychomotorData ?? collect();
            $psychomotorCategory = $psychomotorCategory ?? collect();

            // Organize behavioral data by category and term
            $behavioralData = [];
            $allTerms = \App\Models\Term::all();
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

            // Get student result to access calculated_data
            $studentResult = \App\Models\StudentResult::where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('academic_year_id', $academicYearId)
                ->where('calculation_status', 'completed')
                ->first();

            if (!$studentResult) {
                abort(404, 'Result not ready yet. Please ensure the teacher has viewed and commented on the student result first.');
            }

            // Get calculated data from StudentResult
            $calculatedData = $studentResult->calculated_data;
            $summary = $calculatedData['summary'] ?? [];
            $subjects = $calculatedData['subjects'] ?? [];
            $headings = $calculatedData['headings'] ?? [];

            // Get unique codes from subjects for Marks Obtained section
            $inputCodes = [];
            foreach ($subjects as $subject) {
                if (isset($subject['scores']) && is_array($subject['scores'])) {
                    foreach ($subject['scores'] as $score) {
                        if (isset($score['calc_pattern']) && $score['calc_pattern'] === 'input') {
                            $inputCodes[$score['code']] = $score['code'];
                        }
                    }
                }
            }

            // Prepare data for template using calculated data
            $data = [
                'student' => $student,
                'term' => $term,
                'academy' => $academicYear,
                'class' => $student->class,
                'school' => $school,
                'studentComment' => $studentComment,
                'studentAttendance' => $studentAttendance,
                'nextTerm' => null,
                'behavioralData' => $behavioralData,
                'psychomotorCategory' => $psychomotorCategory,
                'psychomotorData' => $psychomotorData,
                'annualSummaryData' => [],
                // Use calculated data
                'totalScore' => $summary['total_score'] ?? $totalScore,
                'totalSubject' => $summary['total_subjects'] ?? $totalSubject,
                'percent' => $summary['average'] ?? $percent,
                'principalComment' => $studentResult->teacher_comment ?? 'No comment available',
                'subjects' => $subjects,
                'inputCodes' => array_values($inputCodes), // Pass unique input codes
                'headings' => $headings,
                'summary' => $summary,
                'studentResult' => $studentResult, // Add studentResult for calculation_total
            ];

            // Generate PDF with memory optimization
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('results.template', $data);
            $pdf->setPaper('A4', 'portrait');

            // Force garbage collection before PDF generation
            gc_collect_cycles();

            // Generate filename with proper sanitization
            $safeStudentName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $student->name);
            $safeTermName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $term->name);
            $safeAcademicYear = preg_replace('/[^a-zA-Z0-9_-]/', '_', $academicYear->title);
            $filename = "result_{$safeStudentName}_{$safeTermName}_{$safeAcademicYear}.pdf";

            // Return PDF directly
            return $pdf->stream($filename);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Result download error', [
                'student_id' => $studentId,
                'term_id' => $termId,
                'academic_year_id' => $academicYearId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
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

    // Simple test download route without S3
    Route::get('/test/download-simple/{studentId}/{termId}/{academicYearId}', function ($studentId, $termId, $academicYearId) {
        // Cast parameters to integers
        $studentId = (int) $studentId;
        $termId = (int) $termId;
        $academicYearId = (int) $academicYearId;

        try {
            // Simple data for testing
            $student = \App\Models\Student::findOrFail($studentId);
            $term = \App\Models\Term::findOrFail($termId);
            $academicYear = \App\Models\AcademicYear::findOrFail($academicYearId);

            $data = [
                'student' => $student,
                'term' => $term,
                'academy' => $academicYear,
                'class' => $student->class,
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
            ];

            // Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('results.template', $data);
            $pdf->setPaper('A4', 'portrait');

            // Sanitize filename
            $safeStudentName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $student->name);
            $safeTermName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $term->name);
            $safeAcademicYear = preg_replace('/[^a-zA-Z0-9_-]/', '_', $academicYear->title);
            $filename = "result_{$safeStudentName}_{$safeTermName}_{$safeAcademicYear}.pdf";

            // Return PDF directly
            return $pdf->stream($filename);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    })->name('test.download.simple');

    // Test route with actual data processing
    Route::get('/test/download-with-data/{studentId}/{termId}/{academicYearId}', function ($studentId, $termId, $academicYearId) {
        // Cast parameters to integers
        $studentId = (int) $studentId;
        $termId = (int) $termId;
        $academicYearId = (int) $academicYearId;

        try {
            // Check if student has course forms for this term and academic year
            $courseForms = \App\Models\CourseForm::where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('academic_year_id', $academicYearId)
                ->get();

            if ($courseForms->isEmpty()) {
                return response()->json(['error' => 'No course forms found'], 404);
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
            $nextTerm = null;
            if ($term->ending_date) {
                $nextTerm = \App\Models\Term::where('starting_date', '>', $term->ending_date)
                    ->first();
            }

            // Get psychomotor/behavioral data with error handling
            try {
                $psychomotorData = \App\Models\PyschomotorStudent::with(['psychomotor.psychomotorCategory'])
                    ->whereHas('psychomotor', function ($query) use ($termId, $academicYearId) {
                        $query->where('term_id', $termId)
                              ->where('academic_id', $academicYearId);
                    })
                    ->where('student_id', $studentId)
                    ->get();

                // Get psychomotor categories for this term and academic year
                $psychomotorCategory = \App\Models\PsychomotorCategory::with(['psychomotors' => function ($query) use ($termId, $academicYearId) {
                    $query->where('term_id', $termId)
                          ->where('academic_id', $academicYearId);
                }])
                ->whereHas('psychomotors', function ($query) use ($termId, $academicYearId) {
                    $query->where('term_id', $termId)
                          ->where('academic_id', $academicYearId);
                })
                ->get();
            } catch (\Exception $e) {
                // If psychomotor data fails, use empty collections
                $psychomotorData = collect();
                $psychomotorCategory = collect();
            }

            // Ensure variables are always defined as collections
            $psychomotorData = $psychomotorData ?? collect();
            $psychomotorCategory = $psychomotorCategory ?? collect();

            // Organize behavioral data by category and term
            $behavioralData = [];
            $allTerms = \App\Models\Term::all();
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

            // Get psychomotor/behavioral data
            $psychomotorData = \App\Models\PyschomotorStudent::with(['psychomotor.psychomotorCategory'])
                ->whereHas('psychomotor', function ($query) use ($termId, $academicYearId) {
                    $query->where('term_id', $termId)
                          ->where('academic_id', $academicYearId);
                })
                ->where('student_id', $studentId)
                ->get();

            // Get psychomotor categories for this term and academic year
            $psychomotorCategory = \App\Models\PsychomotorCategory::with(['psychomotors' => function ($query) use ($termId, $academicYearId) {
                $query->where('term_id', $termId)
                      ->where('academic_id', $academicYearId);
            }])
            ->whereHas('psychomotors', function ($query) use ($termId, $academicYearId) {
                $query->where('term_id', $termId)
                      ->where('academic_id', $academicYearId);
            })
            ->get();

            // Organize behavioral data by category and term
            $behavioralData = [];
            $allTerms = \App\Models\Term::all();
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

            // Prepare data for template
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
                'psychomotorCategory' => $psychomotorCategory,
                'psychomotorData' => $psychomotorData,
                'resultSectionTypes' => $resultSectionTypes,
                'annualSummaryData' => $annualSummaryData,
            ];

            // Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('results.template', $data);
            $pdf->setPaper('A4', 'portrait');

            // Sanitize filename
            $safeStudentName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $student->name);
            $safeTermName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $term->name);
            $safeAcademicYear = preg_replace('/[^a-zA-Z0-9_-]/', '_', $academicYear->title);
            $filename = "result_{$safeStudentName}_{$safeTermName}_{$safeAcademicYear}.pdf";

            // Return PDF directly
            return $pdf->stream($filename);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    })->name('test.download.with.data');


    // Route::get('/teacher/assignment/{assignment}/student/{student}', ViewSubmittedAssignmentTeacher::class)->name('filament.pages.assignment-student-view');
});

// Test route to verify psychomotor data
Route::get('/test/psychomotor/{studentId}/{termId}/{academicYearId}', function ($studentId, $termId, $academicYearId) {
    try {
        // Get psychomotor data
        $psychomotorData = \App\Models\PyschomotorStudent::with(['psychomotor.psychomotorCategory'])
            ->whereHas('psychomotor', function ($query) use ($termId, $academicYearId) {
                $query->where('term_id', $termId)
                      ->where('academic_id', $academicYearId);
            })
            ->where('student_id', $studentId)
            ->get();

        // Get psychomotor categories
        $psychomotorCategory = \App\Models\PsychomotorCategory::with(['psychomotors' => function ($query) use ($termId, $academicYearId) {
            $query->where('term_id', $termId)
                  ->where('academic_id', $academicYearId);
        }])
        ->whereHas('psychomotors', function ($query) use ($termId, $academicYearId) {
            $query->where('term_id', $termId)
                  ->where('academic_id', $academicYearId);
        })
        ->get();

        return response()->json([
            'success' => true,
            'psychomotor_data_count' => $psychomotorData->count(),
            'psychomotor_categories_count' => $psychomotorCategory->count(),
            'psychomotor_data' => $psychomotorData->map(function($item) {
                return [
                    'id' => $item->id,
                    'rating' => $item->rating,
                    'comment' => $item->comment,
                    'psychomotor' => [
                        'id' => $item->psychomotor->id,
                        'skill' => $item->psychomotor->skill,
                        'category' => $item->psychomotor->psychomotorCategory ? $item->psychomotor->psychomotorCategory->name : null
                    ]
                ];
            }),
            'categories' => $psychomotorCategory->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'psychomotors_count' => $category->psychomotors->count(),
                    'psychomotors' => $category->psychomotors->map(function($psychomotor) {
                        return [
                            'id' => $psychomotor->id,
                            'skill' => $psychomotor->skill
                        ];
                    })
                ];
            })
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('test.psychomotor.data');

// Test route to verify psychomotor data structure
Route::get('/debug/psychomotor-structure/{studentId}/{termId}/{academicYearId}', function ($studentId, $termId, $academicYearId) {
    try {
        // Get psychomotor data
        $psychomotorData = \App\Models\PyschomotorStudent::with(['psychomotor.psychomotorCategory'])
            ->whereHas('psychomotor', function ($query) use ($termId, $academicYearId) {
                $query->where('term_id', $termId)
                      ->where('academic_id', $academicYearId);
            })
            ->where('student_id', $studentId)
            ->get();

        // Get psychomotor categories
        $psychomotorCategory = \App\Models\PsychomotorCategory::with(['psychomotors' => function ($query) use ($termId, $academicYearId) {
            $query->where('term_id', $termId)
                  ->where('academic_id', $academicYearId);
        }])
        ->whereHas('psychomotors', function ($query) use ($termId, $academicYearId) {
            $query->where('term_id', $termId)
                  ->where('academic_id', $academicYearId);
        })
        ->get();

        return response()->json([
            'success' => true,
            'psychomotor_data_type' => get_class($psychomotorData),
            'psychomotor_data_count' => $psychomotorData->count(),
            'psychomotor_data_structure' => $psychomotorData->map(function($item) {
                return [
                    'id' => $item->id,
                    'psychomotor_id' => $item->psychomotor_id,
                    'rating' => $item->rating,
                    'psychomotor' => $item->psychomotor ? [
                        'id' => $item->psychomotor->id,
                        'skill' => $item->psychomotor->skill,
                        'category_id' => $item->psychomotor->psychomotor_category_id
                    ] : null
                ];
            }),
            'categories_count' => $psychomotorCategory->count(),
            'categories' => $psychomotorCategory->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'psychomotors_count' => $category->psychomotors->count(),
                    'psychomotors' => $category->psychomotors->map(function($psychomotor) {
                        return [
                            'id' => $psychomotor->id,
                            'skill' => $psychomotor->skill
                        ];
                    })
                ];
            })
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('debug.psychomotor.structure');

// Test route to check student data
Route::get('/test/student/{studentId}', function ($studentId) {
    try {
        $student = \App\Models\Student::with(['class', 'class.group'])->findOrFail($studentId);

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'class_id' => $student->class_id,
                'class' => $student->class ? [
                    'id' => $student->class->id,
                    'name' => $student->class->name,
                    'group_id' => $student->class->group_id,
                    'group' => $student->class->group ? [
                        'id' => $student->class->group->id,
                        'name' => $student->class->group->name
                    ] : null
                ] : null
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
})->name('test.student.data');
