<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
});

Route::middleware(\App\Http\Middleware\JwtRequest::class)->group(function () {
    //
});
