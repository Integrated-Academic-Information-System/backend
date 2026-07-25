<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
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

        // 1. Filter by Grade
        if ($gradeId) {
            $query->where('students.grade_id', $gradeId);
        }

        // 2. FILTER STUDENTS BY BUCKET SUBJECT 
        if ($subjectId) {
            $isCoreSubject = $gradeId
                ? DB::table('grade_has_subject')->where('grade_id', $gradeId)->where('subject_id', $subjectId)->exists()
                : DB::table('grade_has_subject')->where('subject_id', $subjectId)->exists();

            if (!$isCoreSubject) {
                $query->whereIn('students.id', function ($subQuery) use ($subjectId) {
                    $subQuery->select('student_has_bucket_subject.student_id')
                        ->from('student_has_bucket_subject')
                        ->join('subject_has_bucket_subject', 'student_has_bucket_subject.subject_has_bucket_subject_id', '=', 'subject_has_bucket_subject.id')
                        ->where('subject_has_bucket_subject.subject_id', $subjectId);
                });
            }
        }

        // 3. APPLY SECURITY BOUNDS
        if ($role !== 'admin' && $teacherId) {
            $allowedClasses = DB::table('teacher_has_grade')->where('teacher_id', $teacherId)->pluck('grade_id')
                ->merge(DB::table('teacher_subject_grade')->where('teacher_id', $teacherId)->pluck('grade_id'))->unique()->toArray();
            $query->whereIn('students.grade_id', $allowedClasses);

            // ONLY Subject Teachers get blocked from viewing other subjects. 
            // Class Incharges CAN view other subjects for Reports.
            if ($role === 'subject_teacher') {
                $allowedSubjects = DB::table('teacher_subject_grade')->where('teacher_id', $teacherId)->pluck('subject_id')->toArray();
                if (!$allowedSubjects) $allowedSubjects = DB::table('teacher_has_subject')->where('teacher_id', $teacherId)->pluck('subject_id')->toArray();
                if ($subjectId && !in_array($subjectId, $allowedSubjects)) {
                    $subjectId = $allowedSubjects[0] ?? null; 
                }
            }
        }

        // 4. STRICT JOIN FOR MARKS
        if ($gradeId && $termId && $subjectId && $examYearId) {
            $students = $query->select('students.*', 'marks.mark as marks')
                ->leftJoin('student_has_marks', function($join) use ($gradeId, $termId, $subjectId, $examYearId) {
                    $join->on('students.id', '=', 'student_has_marks.student_id')
                         ->where('student_has_marks.grade_id', '=', $gradeId)
                         ->where('student_has_marks.term_id', '=', $termId)
                         ->where('student_has_marks.subject_id', '=', $subjectId)
                         ->where('student_has_marks.exam_year_id', '=', $examYearId);
                })
                ->leftJoin('marks', 'student_has_marks.marks_id', '=', 'marks.id')
                ->distinct()
                ->get();
        } else {
            $students = $query->select('students.*', DB::raw('NULL as marks'))->distinct()->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'Filtered students fetched',
            'data' => $students
        ], 200);
    }

    public function profile(Request $request)
    {
        // Get the authenticated student from the JWT token
        try {
            $student = JWTAuth::parseToken()->authenticate();
            if (!$student) return response()->json(['message' => 'Student not found'], 404);

            $gradeId = $student->grade_id;
            $gradeInfo = DB::table('grades')->where('id', $gradeId)->select('name')->first();
            $gradeLabel = $gradeInfo ? $gradeInfo->name : 'N/A';

            $coreSubjects = DB::table('grade_has_subject')->join('subjects', 'grade_has_subject.subject_id', '=', 'subjects.id')->where('grade_has_subject.grade_id', $gradeId)->select('subjects.id', 'subjects.name', 'subjects.subject_code')->get();
            $bucketSubjects = DB::table('student_has_bucket_subject')->join('subject_has_bucket_subject', 'student_has_bucket_subject.subject_has_bucket_subject_id', '=', 'subject_has_bucket_subject.id')->join('subjects', 'subject_has_bucket_subject.subject_id', '=', 'subjects.id')->where('student_has_bucket_subject.student_id', $student->id)->select('subjects.id', 'subjects.name', 'subjects.subject_code')->distinct()->get();

            return response()->json([
                'name'          => $student->name,
                'reg_no'        => $student->reg_no,
                'grade'         => $gradeLabel,
                'dob'           => $student->dob,
                'address'       => $student->address,
                'mobile_number' => $student->mobile_number,
                'email'         => $student->email,
                'core_subjects' => $coreSubjects,
                'bucket_subjects' => $bucketSubjects,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized', 'error' => $e->getMessage()], 401);
        }
    }
}
