<?php

//routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\FormDataController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ClassTeacherController;

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
Route::get('/reports/marks/export', [ReportController::class, 'generateReport']);

/*
    Protected Routes (Require Authentication)
    */

Route::middleware('auth:student')->group(function () {
    Route::get('/student/profile', [StudentController::class, 'profile']);
    Route::get('/student/dashboard', [StudentController::class, 'dashboard']);
    Route::post('/student/logout', [AuthController::class, 'logout']);
    Route::get('/student/dashboard', [StudentDashboardController::class, 'dashboard']);
});


// Logout
Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AuthController::class, 'logout']);
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::get('/admin/grades', [AdminUserController::class, 'grades']);
    Route::get('/admin/user-management/form-data', [AdminUserController::class, 'formData']);
    Route::get('/admin/grades/{grade}/subjects', [AdminUserController::class, 'gradeSubjects']);
    Route::post('/admin/students', [AdminUserController::class, 'storeStudent']);
    Route::get('/admin/students/{student}', [AdminUserController::class, 'showStudent']);
    Route::put('/admin/students/{student}', [AdminUserController::class, 'updateStudent']);
    Route::post('/admin/teachers', [AdminUserController::class, 'storeTeacher']);
    Route::get('/admin/teachers/{teacher}', [AdminUserController::class, 'showTeacher']);
    Route::put('/admin/teachers/{teacher}', [AdminUserController::class, 'updateTeacher']);
    Route::get('/admin/users/{type}/{id}', [AdminUserController::class, 'showUser'])->whereIn('type', ['student', 'teacher']);
    Route::put('/admin/users/{type}/{id}/password', [AdminUserController::class, 'changePassword'])->whereIn('type', ['student', 'teacher']);
    Route::delete('/admin/users/{type}/{id}', [AdminUserController::class, 'destroy'])->whereIn('type', ['student', 'teacher']);
});

Route::middleware('auth:teacher')->group(function () {
    Route::post('/teacher/logout', [AuthController::class, 'logout']);
    Route::get('/teacher/profile', [TeacherController::class, 'profile']);       
    Route::put('/teacher/profile/update', [TeacherController::class, 'updateProfile']); 
    Route::get('/teacher/class/students', [ClassTeacherController::class, 'students']);
    Route::get('/teacher/class/overview', [ClassTeacherController::class, 'classInfo']);
});
