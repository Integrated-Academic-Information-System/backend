<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\MarksController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TermController;
use App\Http\Controllers\Api\AcademicYearController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

// Public route - no login needed
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes - login required
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout',   [AuthController::class, 'logout']);
    Route::get('/auth/me',        [AuthController::class, 'me']);
    Route::get('/students',       [StudentController::class, 'index']);
    Route::get('/students/{id}',  [StudentController::class, 'show']);
    Route::get('/marks',          [MarksController::class, 'index']);
    Route::post('/marks/submit',  [MarksController::class, 'submit']);
    Route::put('/marks/{id}',     [MarksController::class, 'update']);
    Route::get('/results/{studentId}', [MarksController::class, 'studentResults']);
    Route::get('/alerts',         [AlertController::class, 'index']);
    Route::post('/alerts',        [AlertController::class, 'store']);
    Route::get('/notifications',  [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all',  [NotificationController::class, 'markAllRead']);
    Route::get('/grades',         [GradeController::class, 'index']);
    Route::get('/subjects',       [SubjectController::class, 'index']);
    Route::get('/terms',          [TermController::class, 'index']);
    Route::get('/academic-years', [AcademicYearController::class, 'index']);
    Route::post('/reports/generate', [ReportController::class, 'generate']);
});