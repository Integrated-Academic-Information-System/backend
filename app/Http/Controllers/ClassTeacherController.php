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
}