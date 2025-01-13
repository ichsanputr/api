<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    echo "Welcome to Catsentence :)";
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
});

// callback
Route::get('/tripay/callback', [\App\Http\Controllers\PaymentController::class, 'tripayCallback']);

// leaderboard
Route::prefix('fillme')->group(function () {
    Route::get('/leaderboard', [\App\Http\Controllers\FillmeController::class, 'getLeaderboard']);
});

Route::middleware(\App\Http\Middleware\JwtRequest::class)->group(function () {
    // profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [\App\Http\Controllers\AuthController::class, 'profile']);
        Route::put('/update', [\App\Http\Controllers\AuthController::class, 'updateProfile']);
    });

    // payment
    Route::prefix('payment')->group(function () {
        Route::get('/get', [\App\Http\Controllers\PaymentController::class, 'getPayments']);
        Route::post('/tripay', [\App\Http\Controllers\PaymentController::class, 'tripay']);
    });

    // fillme app
    Route::prefix('fillme')->group(function () {
        Route::get('/sentences', [\App\Http\Controllers\FillmeController::class, 'getSentences'])->name('addSentence');
        Route::post('/sentences', [\App\Http\Controllers\FillmeController::class, 'addSentences']);
        Route::post('/result', [\App\Http\Controllers\FillmeController::class, 'addResult']);
        Route::post('/report', [\App\Http\Controllers\FillmeController::class, 'addReport']);
    });
});
