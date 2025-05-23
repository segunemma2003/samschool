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


    // Route::get('/teacher/assignment/{assignment}/student/{student}', ViewSubmittedAssignmentTeacher::class)->name('filament.pages.assignment-student-view');
}
    // Your Tenant routes here


});
