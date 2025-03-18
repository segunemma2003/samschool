<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware([
    // 'api',
    'universal',
    \TomatoPHP\FilamentTenancy\FilamentTenancyServiceProvider::TENANCY_IDENTIFICATION,
])->group(function () {
    Route::get('/', function(){
        return response()->json(['tenant' => tenant('id')]);
    });
    Route::post('/save-exam-data', [ExamController::class, 'saveExamData']);
});