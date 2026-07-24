<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            /** @var \App\Models\Student $student */
            $student = $request->user();

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // ── 1. Recent performance ─────────────────────────────────────
            $recentPerformance = DB::table('marks')
                ->join('subjects', 'marks.subject_id', '=', 'subjects.id')
                ->where('marks.student_id', $student->id)
                ->orderBy('marks.created_at', 'desc')
                ->limit(5)
                ->get([
                    'subjects.name as subject',
                    'subjects.subject_code as subject_code',
                    'marks.mark as score',
                ]);

            // ── 2. Academic standing ──────────────────────────────────────
            $academicStanding = DB::table('marks')
                ->where('student_id', $student->id)
                ->avg('mark');

            $academicStanding = $academicStanding ? round($academicStanding, 1) : 0;

            // ── 3. Class rank ─────────────────────────────────────────────
            // Uses grade_id (not grade) to match students in the same class
            $classRank = null;

            if ($academicStanding > 0 && $student->grade_id) {
                $gradeAverages = DB::table('marks')
                    ->join('students', 'marks.student_id', '=', 'students.id')
                    ->where('students.grade_id', $student->grade_id)
                    ->groupBy('marks.student_id')
                    ->orderByDesc(DB::raw('AVG(marks.mark)'))
                    ->pluck(DB::raw('AVG(marks.mark)'), 'marks.student_id');

                $position = array_search(
                    $student->id,
                    array_keys($gradeAverages->toArray())
                );
                $classRank = $position !== false ? '#' . ($position + 1) : null;
            }

            // ── 4. Standing label ─────────────────────────────────────────
            $standingLabel = match (true) {
                $academicStanding === 0.0 => 'No marks recorded yet',
                $academicStanding >= 75   => 'Excellent',
                $academicStanding >= 65   => 'Merit',
                $academicStanding >= 50   => 'Pass',
                default                   => 'Needs Improvement',
            };

            // ── 5. Notifications ──────────────────────────────────────────
            // Safely returns empty array if notifications table doesn't exist yet
            $notifications = collect();
            if (DB::getSchemaBuilder()->hasTable('notifications')) {
                $notifications = DB::table('notifications')
                    ->where('student_id', $student->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(fn ($n) => [
                        'id'         => $n->id,
                        'title'      => $n->title,
                        'body'       => $n->body,
                        'read'       => (bool) $n->is_read,
                        'created_at' => Carbon::parse($n->created_at)->diffForHumans(),
                    ]);
            }

            // ── Response ──────────────────────────────────────────────────
            return response()->json([
                'success'            => true,
                'academic_standing'  => $academicStanding,
                'class_rank'         => $classRank,
                'standing_label'     => $standingLabel,
                'recent_performance' => $recentPerformance,
                'notifications'      => $notifications,
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