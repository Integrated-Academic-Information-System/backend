<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function getStudents(Request $request)
    {
        $role = $request->query('role');
        $teacherId = $request->query('teacher_id');

        $gradeId = $request->query('grade_id');
        $termId = $request->query('term_id');
        $subjectId = $request->query('subject_id');
        $examYearId = $request->query('exam_year_id');

        $query = DB::table('students');

        // ALWAYS apply the requested grade from the dropdown
        if ($gradeId) {
            $query->where('students.grade_has_sub_grade_id', $gradeId);
        }

        // Apply Security bounds based on Role
        if ($role === 'subject_teacher' && $teacherId) {
            $allowedClasses = DB::table('teacher_has_grade')->where('teacher_id', $teacherId)->pluck('grade_has_sub_grade_id')->toArray();
            $allowedSubjects = DB::table('teacher_has_subject')->where('teacher_id', $teacherId)->pluck('subject_id')->toArray();

            // Force query to stay within allowed classes (Security boundary)
            $query->whereIn('students.grade_has_sub_grade_id', $allowedClasses);

            if (!$subjectId || !in_array($subjectId, $allowedSubjects)) {
                $subjectId = $allowedSubjects[0] ?? null; 
            }
        } else if ($role === 'class_incharge' && $teacherId) {
            $allowedClasses = DB::table('teacher_has_grade')->where('teacher_id', $teacherId)->pluck('grade_has_sub_grade_id')->toArray();
            
            // Force query to stay within allowed classes (Security boundary)
            $query->whereIn('students.grade_has_sub_grade_id', $allowedClasses);
            // Notice: No subject restriction for class incharge, they can view all
        }

        // STRICT JOIN FOR MARKS
        if ($gradeId && $termId && $subjectId && $examYearId) {
            $students = $query->select('students.*', 'marks.mark as marks')
                ->leftJoin('student_has_marks', function($join) use ($gradeId, $termId, $subjectId, $examYearId) {
                    $join->on('students.id', '=', 'student_has_marks.student_id')
                         ->where('student_has_marks.grade_has_sub_grade_id', '=', $gradeId)
                         ->where('student_has_marks.term_id', '=', $termId)
                         ->where('student_has_marks.subject_id', '=', $subjectId)
                         ->where('student_has_marks.exam_year_id', '=', $examYearId);
                })
                ->leftJoin('marks', 'student_has_marks.marks_id', '=', 'marks.id')
                ->get();
        } else {
            $students = $query->select('students.*', DB::raw('NULL as marks'))->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'Filtered students fetched',
            'data' => $students
        ], 200);
    }
}