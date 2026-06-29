<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormDataController extends Controller
{
    public function getDropdownData(Request $request)
    {
        $role = $request->query('role'); 
        $teacherId = $request->query('teacher_id'); 

        $terms = DB::table('terms')->select('id', 'name')->get();
        $years = DB::table('exam_years')->select('id', 'year as name')->get();
        
        $subjectsQuery = DB::table('subjects');
        
        // Use 'grades' table directly now instead of 'grade_has_sub_grade'
        $classesQuery = DB::table('grades')->select('id', 'name');

        // Filter based on role
        if ($role === 'subject_teacher' && $teacherId) {
            // Subject teacher sees only assigned subjects and assigned grades
            $allowedSubjectIds = DB::table('teacher_has_subject')->where('teacher_id', $teacherId)->pluck('subject_id');
            $allowedGradeIds = DB::table('teacher_has_grade')->where('teacher_id', $teacherId)->pluck('grade_id');
            
            $subjectsQuery->whereIn('id', $allowedSubjectIds);
            $classesQuery->whereIn('id', $allowedGradeIds);
            
        } else if ($role === 'class_incharge' && $teacherId) {
            // Class incharge sees ALL subjects, but ONLY their assigned grade
            $allowedGradeIds = DB::table('teacher_has_grade')->where('teacher_id', $teacherId)->pluck('grade_id');
            $classesQuery->whereIn('id', $allowedGradeIds);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'terms' => $terms,
                'grades' => $classesQuery->orderBy('id')->get(), // Order by ID to keep Grade 6, 7, 8... order
                'subjects' => $subjectsQuery->orderBy('name')->get(),
                'exam_years' => $years
            ]
        ], 200);
    }
}