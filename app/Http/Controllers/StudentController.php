<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student; // this is Model connected to students table in database
use Illuminate\Support\Facades\DB; // this is used to perform database operations like transactions, queries, etc.

class StudentController extends Controller
{
    // Function to fetch all students from the database and return as JSON response to the frontend
    public function getStudents()
    {
        // Fetch all students from the database along with their marks using a left join to include students without marks
        $students = DB::table('students')
            ->leftJoin('student_has_marks', 'students.id', '=', 'student_has_marks.student_id')
            ->leftJoin('marks', 'student_has_marks.marks_id', '=', 'marks.id')
            ->select('students.*', 'marks.mark as marks') // Select all student fields and the associated mark (if any)
            ->get();

            
        // return the students as a JSON response with a success message and HTTP status code 200 (OK) for the frontend to consume
        return response()->json([
            'success' => true,
            'message' => 'Students fetched successfully',
            'data' => $students
        ], 200);
    }
}