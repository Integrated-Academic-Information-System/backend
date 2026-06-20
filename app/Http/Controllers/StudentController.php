<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function getStudents(Request $request)
    {
        // Get the selected IDs sent from the React Native dropdowns
        
        $gradeId = $request->query('grade_id');
        $termId = $request->query('term_id');
        $subjectId = $request->query('subject_id');
        $examYearId = $request->query('exam_year_id');

        // Base query for the students table
        $query = DB::table('students');

        // 1. FILTER ROSTER BY CLASS: Only fetch students who belong to the selected class
        if ($gradeId) {
            $query->where('students.grade_has_sub_grade_id', $gradeId);
        }

        // 2. STRICT JOIN: Only join marks if ALL dropdowns (including Subject) are selected
        if ($gradeId && $termId && $subjectId && $examYearId) {
            $students = $query->select('students.*', 'marks.mark as marks')
                ->leftJoin('student_has_marks', function($join) use ($gradeId, $termId, $subjectId, $examYearId) {
                    $join->on('students.id', '=', 'student_has_marks.student_id')
                         // All conditions must match exactly
                         ->where('student_has_marks.grade_has_sub_grade_id', '=', $gradeId)
                         ->where('student_has_marks.term_id', '=', $termId)
                         ->where('student_has_marks.subject_id', '=', $subjectId)
                         ->where('student_has_marks.exam_year_id', '=', $examYearId);
                })
                ->leftJoin('marks', 'student_has_marks.marks_id', '=', 'marks.id')
                ->get();
        } else {
            // If subject (or any other field) is missing, just return students with NULL marks
            $students = $query->select('students.*', DB::raw('NULL as marks'))->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'Filtered students and marks fetched successfully',
            'data' => $students
        ], 200);
    }
}