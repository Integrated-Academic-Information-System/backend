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
        if ($gradeId && $subjectId) {
            $isCoreSubject = DB::table('grade_has_subject')
                ->where('grade_id', $gradeId)
                ->where('subject_id', $subjectId)
                ->exists();

            if (!$isCoreSubject) {
                $query->join('student_has_bucket_subject', 'students.id', '=', 'student_has_bucket_subject.student_id')
                      ->join('subject_has_bucket_subject', 'student_has_bucket_subject.subject_has_bucket_subject_id', '=', 'subject_has_bucket_subject.id')
                      ->where('subject_has_bucket_subject.subject_id', $subjectId);
            }
        }

        // 3. APPLY SECURITY BOUNDS
        if ($role !== 'admin' && $teacherId) {
            $allowedClasses = DB::table('teacher_has_grade')->where('teacher_id', $teacherId)->pluck('grade_id')->toArray();
            $query->whereIn('students.grade_id', $allowedClasses);

            // ONLY Subject Teachers get blocked from viewing other subjects. 
            // Class Incharges CAN view other subjects for Reports.
            if ($role === 'subject_teacher') {
                $allowedSubjects = DB::table('teacher_has_subject')->where('teacher_id', $teacherId)->pluck('subject_id')->toArray();
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
        // Get the authenticated student from the JWT token
        try {
            $student = JWTAuth::parseToken()->authenticate();
            if (!$student) return response()->json(['message' => 'Student not found'], 404);

            $gradeId = $student->grade_id;
            $gradeInfo = DB::table('grades')->where('id', $gradeId)->select('name')->first();
            $gradeLabel = $gradeInfo ? $gradeInfo->name : 'N/A';

            $coreSubjects = DB::table('grade_has_subject')->join('subjects', 'grade_has_subject.subject_id', '=', 'subjects.id')->where('grade_has_subject.grade_id', $gradeId)->select('subjects.id', 'subjects.name', 'subjects.subject_code')->get();
            $bucketSubjects = DB::table('student_has_bucket_subject')->join('subject_has_bucket_subject', 'student_has_bucket_subject.subject_has_bucket_subject_id', '=', 'subject_has_bucket_subject.id')->join('subjects', 'subject_has_bucket_subject.subject_id', '=', 'subjects.id')->where('student_has_bucket_subject.student_id', $student->id)->select('subjects.id', 'subjects.name', 'subjects.subject_code')->get();

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


    public function dashboard(Request $request)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            if (!$student) return response()->json(['message' => 'Student not found'], 404);

            // 1. Find the current term
            $currentTerm = DB::table('terms')->where('is_current', true)->first();
            if (!$currentTerm) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active term found.'
                ], 404);
            }

            // 2. Academic Standing % — this student's average mark in the current term
            $studentAvg = DB::table('student_has_marks')
                ->join('marks', 'student_has_marks.marks_id', '=', 'marks.id')
                ->where('student_has_marks.student_id', $student->id)
                ->where('student_has_marks.term_id', $currentTerm->id)
                ->avg('marks.mark');

            $academicStanding = $studentAvg !== null ? round($studentAvg, 1) : 0;

            // 3. Class Rank — rank this student against everyone in the same grade
            $classAverages = DB::table('student_has_marks')
                ->join('marks', 'student_has_marks.marks_id', '=', 'marks.id')
                ->where('student_has_marks.grade_id', $student->grade_id)
                ->where('student_has_marks.term_id', $currentTerm->id)
                ->select('student_has_marks.student_id', DB::raw('AVG(marks.mark) as avg_mark'))
                ->groupBy('student_has_marks.student_id')
                ->orderByDesc('avg_mark')
                ->get();

            $rankIndex = $classAverages->search(fn($row) => $row->student_id === $student->id);
            $classRank = $rankIndex !== false ? $rankIndex + 1 : null;
            $totalStudents = $classAverages->count();

            $standingLabel = null;
            if ($classRank !== null && $totalStudents > 0) {
                $percentile = round(($classRank / $totalStudents) * 100);
                $standingLabel = "Top {$percentile}% of your class";
            }

            // 4. Recent Performance — latest 3 marks entered, any subject
            $recentMarks = DB::table('student_has_marks')
                ->join('marks', 'student_has_marks.marks_id', '=', 'marks.id')
                ->join('subjects', 'student_has_marks.subject_id', '=', 'subjects.id')
                ->where('student_has_marks.student_id', $student->id)
                ->orderByDesc('student_has_marks.created_at')
                ->select(
                    'subjects.name as subject',
                    'subjects.subject_code as subject_code',
                    'marks.mark as score'
                )
                ->limit(3)
                ->get();

            // 5. Notifications — latest 5 for this student
            $notifications = DB::table('notifications')
                ->where('student_id', $student->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'academic_standing' => $academicStanding,
                'class_rank' => $classRank ? '#' . str_pad($classRank, 2, '0', STR_PAD_LEFT) : null,
                'standing_label' => $standingLabel,
                'recent_performance' => $recentMarks,
                'notifications' => $notifications,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized', 'error' => $e->getMessage()], 401);
        }
    }
}