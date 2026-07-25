<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// ─── Public Routes ────────────────────────────────────────────────────────────

Route::post('/login', [AuthController::class, 'login']);
Route::get('/students', [StudentController::class, 'getStudents']);

// ─── Authenticated Routes (JWT) ───────────────────────────────────────────────

Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Student routes
    Route::prefix('student')->group(function () {
        Route::get('/profile',        [StudentController::class, 'profile']);
        Route::get('/dashboard',      [StudentController::class, 'dashboard']);
        Route::get('/latest-results', [StudentController::class, 'latestResults']);
    });
});