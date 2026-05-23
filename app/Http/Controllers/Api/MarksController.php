<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Notification;
use Illuminate\Http\Request;

class MarksController extends Controller {

    public function index(Request $request) {
        $request->validate([
            'grade_id'         => 'required',
            'subject_id'       => 'required',
            'term_id'          => 'required',
            'academic_year_id' => 'required',
        ]);

        $students = Student::with(['user', 'marks' => function($q) use ($request) {
            $q->where('subject_id', $request->subject_id)
              ->where('term_id', $request->term_id)
              ->where('academic_year_id', $request->academic_year_id);
        }])->where('grade_id', $request->grade_id)->get();

        return response()->json($students->map(fn($s) => [
            'student_id' => $s->id,
            'name'       => $s->user->name,
            'index_no'   => $s->index_no,
            'marks'      => $s->marks->first()?->marks,
            'status'     => $s->marks->first()?->status ?? 'pending',
            'mark_id'    => $s->marks->first()?->id,
        ]));
    }

    public function submit(Request $request) {
        $request->validate([
            'subject_id'       => 'required',
            'term_id'          => 'required',
            'academic_year_id' => 'required',
            'marks'            => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.marks'      => 'required|integer|min:0|max:100',
        ]);

        foreach ($request->marks as $entry) {
            Mark::updateOrCreate(
                [
                    'student_id'       => $entry['student_id'],
                    'subject_id'       => $request->subject_id,
                    'term_id'          => $request->term_id,
                    'academic_year_id' => $request->academic_year_id,
                ],
                [
                    'marks'        => $entry['marks'],
                    'status'       => 'validated',
                    'submitted_by' => $request->user()->id,
                ]
            );

            // Send notification to student
            $student = Student::find($entry['student_id']);
            Notification::create([
                'user_id' => $student->user_id,
                'title'   => 'Marks Updated',
                'message' => 'Your marks have been updated.',
            ]);
        }

        return response()->json(['message' => 'Marks submitted successfully']);
    }

    public function update(Request $request, $id) {
        $request->validate([
            'marks'  => 'required|integer|min:0|max:100',
            'status' => 'in:pending,validated,submitted',
        ]);

        $mark = Mark::findOrFail($id);
        $mark->update([
            'marks'        => $request->marks,
            'status'       => $request->status ?? $mark->status,
            'submitted_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Mark updated', 'mark' => $mark]);
    }

    public function studentResults(Request $request, $studentId) {
        $marks = Mark::with(['subject', 'term', 'academicYear'])
            ->where('student_id', $studentId)
            ->get();

        return response()->json($marks->map(fn($m) => [
            'subject'       => $m->subject->name,
            'term'          => $m->term->name,
            'academic_year' => $m->academicYear->year,
            'marks'         => $m->marks,
            'status'        => $m->status,
        ]));
    }
}