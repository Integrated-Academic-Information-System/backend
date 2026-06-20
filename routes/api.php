<?php

//routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\FormDataController;

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

    /*
    Protected Routes (Require Authentication)
    */


    Route::middleware('auth:api')->group(function () {

       

        // Logout
        Route::post('/logout', [AuthController::class, 'logout']);

    });


    /*
    Default Authenticated User Route (Optional)
    */

    // Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    //     return $request->user();
    // });