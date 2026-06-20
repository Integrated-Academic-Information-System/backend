<?php

//routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


// A GET request/when come to students route, run getStudents function in StudentController to fetch all students from the database and return as JSON response to the frontend 
Route::post('/login', [AuthController::class, 'login']);
Route::get('/students', [StudentController::class, 'getStudents']);

Route::middleware('auth:api')->group(function () {
    
    Route::post('/logout',  [AuthController::class, 'logout']);

    // your protected routes here...
});