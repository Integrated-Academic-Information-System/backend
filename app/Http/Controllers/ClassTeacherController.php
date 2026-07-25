<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassTeacherController extends Controller
{
    public function students(Request $request)
    {
        try {
            /** @var \App\Models\Teacher $teacher */
            $teacher = $request->user();

            if (!$teacher) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // ── Get the grade this class teacher is incharge of ───────────────
            // A class teacher (role_status=1) has exactly one grade in teacher_has_grade
            $gradeRow = DB::table('teacher_has_grade')
                ->join('grades', 'teacher_has_grade.grade_id', '=', 'grades.id')
                ->where('teacher_has_grade.teacher_id', $teacher->id)
                ->select('grades.id as grade_id', 'grades.name as grade_name')
                ->first();

            if (!$gradeRow) {
                return response()->json([
                    'success'       => false,
                    'message'       => 'No class assigned to this teacher',
                ], 404);
            }

            // ── Get all active students in that grade ─────────────────────────
            $students = DB::table('students')
                ->where('grade_id', $gradeRow->grade_id)
                ->where('status', 1)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'reg_no',
                    'email',
                    'mobile_number',
                ]);

            // ── Response ──────────────────────────────────────────────────────
            return response()->json([
                'success'       => true,
                'class_name'    => $gradeRow->grade_name,
                'student_count' => $students->count(),
                'students'      => $students,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }

    public function classInfo(Request $request)
    {
        try {
            /** @var \App\Models\Teacher $teacher */
            $teacher = $request->user();

            if (!$teacher) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // ── Grade this class teacher is incharge of ────────────────────────
            $gradeRow = DB::table('teacher_has_grade')
                ->join('grades', 'teacher_has_grade.grade_id', '=', 'grades.id')
                ->where('teacher_has_grade.teacher_id', $teacher->id)
                ->select('grades.id as grade_id', 'grades.name as grade_name')
                ->first();

            if (!$gradeRow) {
                return response()->json([
                    'success' => false,
                    'message' => 'No class assigned to this teacher',
                ], 404);
            }

            // ── Student count in that grade ─────────────────────────────────────
            $studentCount = DB::table('students')
                ->where('grade_id', $gradeRow->grade_id)
                ->where('status', 1)
                ->count();

            // ── Assigned subjects + grades ──────────────────────────────────────
            // A subject counts as "assigned" only if:
            //   1. Teacher has access to the grade (teacher_has_grade)
            //   2. Teacher teaches the subject (teacher_has_subject)
            //   3. That subject is actually offered in that grade (grade_has_subject)
            $assignedSubjects = DB::table('teacher_has_grade')
                ->join('grades', 'teacher_has_grade.grade_id', '=', 'grades.id')
                ->join('grade_has_subject', 'grade_has_subject.grade_id', '=', 'teacher_has_grade.grade_id')
                ->join('subjects', 'subjects.id', '=', 'grade_has_subject.subject_id')
                ->join('teacher_has_subject', function ($join) use ($teacher) {
                    $join->on('teacher_has_subject.subject_id', '=', 'subjects.id')
                        ->where('teacher_has_subject.teacher_id', '=', $teacher->id);
                })
                ->where('teacher_has_grade.teacher_id', $teacher->id)
                ->select(
                    'subjects.id as subject_id',
                    'subjects.name as subject_name',
                    'grades.id as grade_id',
                    'grades.name as grade_name'
                )
                ->distinct()
                ->orderBy('grades.name')
                ->orderBy('subjects.name')
                ->get();

            return response()->json([
                'success'           => true,
                'class_name'        => $gradeRow->grade_name,
                'student_count'     => $studentCount,
                'assigned_subjects' => $assignedSubjects,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }
}
