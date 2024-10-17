<?php

declare(strict_types=1);

use App\Filament\Ourstudent\Pages\ExamFinalSubmissionPage;
use App\Filament\Ourstudent\Pages\ExamPage;
use App\Filament\Ourstudent\Pages\ExamReviewPage;
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
    });
    Route::get('/exam-page', ExamPage::class)->name('exam.page');
    Route::get('/exam/review', ExamReviewPage::class)->name('exam.review');
    Route::get('/exam/final-submission', ExamFinalSubmissionPage::class)->name('exam.final_submission');
}
    // Your Tenant routes here

});
