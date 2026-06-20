<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormDataController extends Controller
{
    public function getDropdownData()
    {
        // Fetch Terms
        $terms = DB::table('terms')->select('id', 'name')->get();
        
        // Fetch Exam Years and alias 'year' to 'name' for the frontend dropdown component
        $years = DB::table('exam_years')->select('id', 'year as name')->get();
        
        // Fetch Subjects
        $subjects = DB::table('subjects')->select('id', 'name')->orderBy('name')->get();

        // Combine Grades and SubGrades to send as "Grade 6 - A"
        $classes = DB::table('grade_has_sub_grade')
            ->join('grades', 'grade_has_sub_grade.grade_id', '=', 'grades.id')
            ->join('sub_grades', 'grade_has_sub_grade.sub_grade_id', '=', 'sub_grades.id')
            ->select(
                'grade_has_sub_grade.id', 
                DB::raw("CONCAT(grades.name, ' - ', sub_grades.name) as name"),
                'grades.name as base_grade' // Used in frontend to calculate A/L vs O/L grading rules
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'terms' => $terms,
                'grades' => $classes, // Sends "Grade 6 - A"
                'subjects' => $subjects,
                'exam_years' => $years
            ]
        ], 200);
    }
}