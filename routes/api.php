<?php

//routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\FormDataController;
use App\Http\Controllers\ReportController;
/*
    Public Routes
    */

// Login
Route::post('/login', [AuthController::class, 'login']);

// Get all students
Route::get('/students', [StudentController::class, 'getStudents']);

// Save marks (currently public for testing)
Route::post('/save-marks', [MarkController::class, 'saveMarks']);


// Fetch dropdown data (Terms, Grades, Subjects)
Route::get('/form-data', [FormDataController::class, 'getDropdownData']);

// Generate report
Route::get('/generate-report', [ReportController::class, 'generateReport']);

/*
    Protected Routes (Require Authentication)
    */

Route::middleware('auth:student')->group(function () {
    Route::get('/student/profile', [StudentController::class, 'profile']);
    Route::post('/student/logout', [AuthController::class, 'logout']);
});


// Logout
Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:teacher')->group(function () {
    Route::post('/teacher/logout', [AuthController::class, 'logout']);
});



    /*
    Default Authenticated User Route (Optional)
    */

    // Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    //     return $request->user();
    // });