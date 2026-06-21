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
        $classesQuery = DB::table('grade_has_sub_grade')
            ->join('grades', 'grade_has_sub_grade.grade_id', '=', 'grades.id')
            ->join('sub_grades', 'grade_has_sub_grade.sub_grade_id', '=', 'sub_grades.id')
            ->select(
                'grade_has_sub_grade.id', 
                DB::raw("CONCAT(grades.name, ' - ', sub_grades.name) as name"),
                'grades.name as base_grade'
            );

        // Filter based on role
        if ($role === 'subject_teacher' && $teacherId) {
            // Subject teacher sees only assigned subjects and assigned classes
            $allowedSubjectIds = DB::table('teacher_has_subject')->where('teacher_id', $teacherId)->pluck('subject_id');
            $allowedClassIds = DB::table('teacher_has_grade')->where('teacher_id', $teacherId)->pluck('grade_has_sub_grade_id');
            
            $subjectsQuery->whereIn('id', $allowedSubjectIds);
            $classesQuery->whereIn('grade_has_sub_grade.id', $allowedClassIds);
        } else if ($role === 'class_incharge' && $teacherId) {
            // Class incharge sees ALL subjects, but ONLY their assigned class
            $allowedClassIds = DB::table('teacher_has_grade')->where('teacher_id', $teacherId)->pluck('grade_has_sub_grade_id');
            $classesQuery->whereIn('grade_has_sub_grade.id', $allowedClassIds);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'terms' => $terms,
                'grades' => $classesQuery->get(),
                'subjects' => $subjectsQuery->orderBy('name')->get(),
                'exam_years' => $years
            ]
        ], 200);
    }
}