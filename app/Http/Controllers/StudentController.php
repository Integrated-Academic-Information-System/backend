<?php

namespace App\Http\Controllers;

use App\Models\Mark;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // ─── GET /api/students ────────────────────────────────────────────────────
    // Existing method — kept as is

    public function getStudents()
    {
        $students = Student::all();

        return response()->json([
            'success' => true,
            'message' => 'Students fetched successfully',
            'data'    => $students,
        ], 200);
    }

    // ─── GET /api/student/profile ─────────────────────────────────────────────

    public function profile(Request $request): JsonResponse
    {
        $student = $this->getAuthStudent($request);

        if (! $student) {
            return response()->json(['message' => 'Student profile not found.'], 404);
        }

        return response()->json([
            'id'            => $student->id,
            'name'          => $student->name,
            'reg_no'        => $student->reg_no,
            'grade'         => $student->grade?->name,
            'dob'           => $student->dob,
            'address'       => $student->address,
            'mobile_number' => $student->mobile_number,
            'email'         => $student->email,
            'status'        => $student->status,
        ]);
    }

    // ─── GET /api/student/dashboard ───────────────────────────────────────────

    public function dashboard(Request $request): JsonResponse
    {
        $student = $this->getAuthStudent($request);

        if (! $student) {
            return response()->json(['message' => 'Student not found.'], 404);
        }

        $currentTerm = Term::where('is_current', 1)->first();

        if (! $currentTerm) {
            return response()->json(['message' => 'No current term found.'], 404);
        }

        $marks = Mark::with('subject')
            ->where('student_id', $student->id)
            ->where('term_id', $currentTerm->id)
            ->get();

        $totalObtained = $marks->sum('marks_obtained');
        $totalPossible = $marks->sum('total_marks');
        $percentage    = $totalPossible > 0
            ? round(($totalObtained / $totalPossible) * 100, 1)
            : 0;

        $gpa = $this->calculateGpa($percentage);

        return response()->json([
            'student_name' => $student->name,
            'reg_no'       => $student->reg_no,
            'grade'        => $student->grade?->name,
            'term'         => $currentTerm->name,
            'percentage'   => $percentage,
            'gpa'          => $gpa,
            'total_marks'  => [
                'obtained' => $totalObtained,
                'total'    => $totalPossible,
            ],
        ]);
    }

    // ─── GET /api/student/latest-results ──────────────────────────────────────

    public function latestResults(Request $request): JsonResponse
    {
        $student = $this->getAuthStudent($request);

        if (! $student) {
            return response()->json(['message' => 'Student not found.'], 404);
        }

        $currentTerm = Term::where('is_current', 1)->first();

        if (! $currentTerm) {
            return response()->json(['message' => 'No current term found.'], 404);
        }

        $marks = Mark::with(['subject', 'term'])
            ->where('student_id', $student->id)
            ->where('term_id', $currentTerm->id)
            ->get();

        $totalObtained = $marks->sum('marks_obtained');
        $totalPossible = $marks->sum('total_marks');
        $percentage    = $totalPossible > 0
            ? round(($totalObtained / $totalPossible) * 100, 1)
            : 0;

        $gpa = $this->calculateGpa($percentage);

        // Class rank
        $allTotals = Mark::selectRaw('student_id, SUM(marks_obtained) as total')
            ->where('term_id', $currentTerm->id)
            ->whereHas('student', function ($q) use ($student) {
                $q->where('grade_id', $student->grade_id);
            })
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->pluck('total', 'student_id')
            ->toArray();

        $rankPosition = array_search($student->id, array_keys($allTotals));
        $rank         = $rankPosition !== false ? $rankPosition + 1 : 1;
        $classSize    = count($allTotals);

        $subjects = $marks->map(function (Mark $mark) {
            $subject = $mark->subject;
            return [
                'id'           => (string) $subject->id,
                'name'         => $subject->name,
                'teacher'      => '',
                'score'        => (int) $mark->marks_obtained . '/' . (int) $mark->total_marks,
                'obtained'     => $mark->marks_obtained,
                'total'        => $mark->total_marks,
                'grade_letter' => $mark->grade_letter
                    ?? $this->getGradeLetter($mark->marks_obtained, $mark->total_marks),
                'icon'         => $subject->icon,
            ];
        })->values();

        return response()->json([
            'student' => [
                'name'   => $student->name,
                'reg_no' => $student->reg_no,
                'grade'  => $student->grade?->name,
                'term'   => $currentTerm->name,
            ],
            'summary' => [
                'gpa'        => $gpa,
                'percentage' => $percentage,
                'rank'       => $rank,
                'class_size' => $classSize,
                'obtained'   => $totalObtained,
                'total'      => $totalPossible,
            ],
            'subjects' => $subjects,
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function getAuthStudent(Request $request): ?Student
    {
        $user = $request->user();
        return Student::with(['grade'])
            ->where('id', $user->id)
            ->first();
    }

    private function calculateGpa(float $percentage): float
    {
        if ($percentage >= 90) return 4.0;
        if ($percentage >= 85) return 3.7;
        if ($percentage >= 80) return 3.3;
        if ($percentage >= 75) return 3.0;
        if ($percentage >= 70) return 2.7;
        if ($percentage >= 65) return 2.3;
        if ($percentage >= 60) return 2.0;
        if ($percentage >= 55) return 1.7;
        if ($percentage >= 50) return 1.3;
        if ($percentage >= 45) return 1.0;
        return 0.0;
    }

    private function getGradeLetter(float $obtained, float $total): string
    {
        $pct = $total > 0 ? ($obtained / $total) * 100 : 0;

        if ($pct >= 90) return 'A+';
        if ($pct >= 80) return 'A';
        if ($pct >= 75) return 'A-';
        if ($pct >= 70) return 'B+';
        if ($pct >= 65) return 'B';
        if ($pct >= 60) return 'B-';
        if ($pct >= 55) return 'C+';
        if ($pct >= 50) return 'C';
        if ($pct >= 45) return 'C-';
        if ($pct >= 40) return 'S';
        return 'F';
    }
}