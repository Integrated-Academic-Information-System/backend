<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

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



    public function profile(Request $request)
{
    try {
        $student = JWTAuth::parseToken()->authenticate();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $gradeHasSubGradeId = $student->grade_has_sub_grade_id;

        // Grade label
        $gradeInfo = DB::table('grade_has_sub_grade')
            ->join('grades', 'grade_has_sub_grade.grade_id', '=', 'grades.id')
            ->join('sub_grades', 'grade_has_sub_grade.sub_grade_id', '=', 'sub_grades.id')
            ->where('grade_has_sub_grade.id', $gradeHasSubGradeId)
            ->select('grades.name as grade_name', 'sub_grades.name as sub_grade_name')
            ->first();

        $gradeLabel = $gradeInfo
            ? $gradeInfo->grade_name . ' ' . $gradeInfo->sub_grade_name
            : 'N/A';

        // Core subjects — from grade
        $coreSubjects = DB::table('grade_has_subject')
            ->join('subjects', 'grade_has_subject.subject_id', '=', 'subjects.id')
            ->where('grade_has_subject.grade_has_sub_grade_id', $gradeHasSubGradeId)
            ->select('subjects.id', 'subjects.name', 'subjects.subject_code')
            ->get();

        // Bucket subjects — assigned to this student by admin
        $bucketSubjects = DB::table('student_has_bucket_subject')
            ->join('subject_has_bucket_subject', 
                'student_has_bucket_subject.subject_has_bucket_subject_id', 
                '=', 
                'subject_has_bucket_subject.id')
            ->join('subjects', 'subject_has_bucket_subject.subject_id', '=', 'subjects.id')
            ->where('student_has_bucket_subject.student_id', $student->id)
            ->select('subjects.id', 'subjects.name', 'subjects.subject_code')
            ->get();

        return response()->json([
            'name'            => $student->name,
            'reg_no'          => $student->reg_no,
            'grade'           => $gradeLabel,
            'dob'             => $student->dob,
            'address'         => $student->address,
            'mobile_number'   => $student->mobile_number,
            'email'           => $student->email,
            'core_subjects'   => $coreSubjects,
            'bucket_subjects' => $bucketSubjects,
        ]);

    } catch (\Exception $e) {
        return response()->json(['message' => 'Unauthorized', 'error' => $e->getMessage()], 401);
    }
}
}