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

        // 1. SECURITY CHECK: If the user is not an admin, they must provide a teacher_id
        if ($role !== 'admin' && empty($teacherId)) {
            return response()->json([
                'success' => false, 
                'message' => 'Teacher ID is missing from Frontend!'
            ], 400);
        }

        $terms = DB::table('terms')->select('id', 'name')->get();
        $years = DB::table('exam_years')->select('id', 'year as name')->get();
        
        $subjectsQuery = DB::table('subjects');
        $classesQuery = DB::table('grades')->select('id', 'name');
        
        $editableSubjectIds = []; // the subjects that the teacher can edit (for frontend to know)

        if ($role !== 'admin') {
            
            // 1. get the grades that the teacher is allowed to see (Class Incharge or Subject Teacher)
            $allowedGradeIds = DB::table('teacher_has_grade')->where('teacher_id', $teacherId)->pluck('grade_id');
            $classesQuery->whereIn('id', $allowedGradeIds);
            
            // 2. get the subjects that the teacher is allowed to see (Subject Teacher or Class Incharge)
            $editableSubjectIds = DB::table('teacher_has_subject')->where('teacher_id', $teacherId)->pluck('subject_id');

            // 3. Apply the security bounds to the subjects query
            if ($role === 'subject_teacher') {
                // Subject Teachers can only see the subjects they teach
                $subjectsQuery->whereIn('id', $editableSubjectIds);
            } 
            // Class Incharges can see all subjects for the grades they are in charge of, so no additional filtering needed for them.
            // But we still need to ensure that the subjects belong to the grades they are in charge of.
        } 

        return response()->json([
            'success' => true,
            'data' => [
                'terms' => $terms,
                'grades' => $classesQuery->orderBy('id')->get(),
                'subjects' => $subjectsQuery->orderBy('name')->get(),
                'exam_years' => $years,
                'editable_subject_ids' => $editableSubjectIds // for frontend to know which subjects the teacher can edit
            ]
        ], 200);
    }
}