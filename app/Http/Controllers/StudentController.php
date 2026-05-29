<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student; // this is Model connected to students table in database

class StudentController extends Controller
{
    // this API endpoint is used to fetch all students from the database and return as JSON response
    public function getStudents()
    {
        // get all students from the database using Eloquent ORM
        $students = Student::all();

        // return the students as a JSON response with a success message and HTTP status code 200 (OK) for the frontend to consume
        return response()->json([
            'success' => true,
            'message' => 'Students fetched successfully',
            'data' => $students
        ], 200);
    }
}