<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

Route::get('/', [LandingController::class, 'index']);


Route::get('/payment/callback', [App\Http\Controllers\PaymentController::class, 'handleGatewayCallback']);

Route::post('/pay', [App\Http\Controllers\PaymentController::class, 'redirectToGateway'])->name('pay');

Route::post('/logout-everywhere', function () {
    return redirect('/')->with('status', 'You have been logged out from all panels.');
})->name('logout.everywhere');


require __DIR__.'/channels.php';
