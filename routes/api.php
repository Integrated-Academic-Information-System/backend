<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// A GET request/when come to students route, run getStudents function in StudentController to fetch all students from the database and return as JSON response to the frontend 
Route::get('/students', [StudentController::class, 'getStudents']);