<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\Student;
use Illuminate\Http\Request;

class ReportController extends Controller {
    public function generate(Request $request) {
        $request->validate([
            'grade_id'         => 'required',
            'term_id'          => 'required',
            'academic_year_id' => 'required',
        ]);

        $students = Student::with(['user', 'marks' => function($q) use ($request) {
            $q->with('subject')
              ->where('term_id', $request->term_id)
              ->where('academic_year_id', $request->academic_year_id);
        }])->where('grade_id', $request->grade_id)->get();

        $report = $students->map(fn($s) => [
            'student'  => $s->user->name,
            'index_no' => $s->index_no,
            'marks'    => $s->marks->map(fn($m) => [
                'subject' => $m->subject->name,
                'marks'   => $m->marks,
                'status'  => $m->status,
            ]),
            'total'    => $s->marks->sum('marks'),
            'average'  => $s->marks->avg('marks'),
        ]);

        return response()->json(['report' => $report]);
    }
}