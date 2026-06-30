<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function generateReport(Request $request)
    {
        $gradeId = $request->query('grade_id');
        $subjectId = $request->query('subject_id');
        $examYearId = $request->query('exam_year_id');
        $termId = $request->query('term_id');

        // get students with their marks for the specified grade, subject, exam year, and term
        $students = DB::table('students')
            ->leftJoin('student_has_marks', function($join) use ($gradeId, $termId, $subjectId, $examYearId) {
                $join->on('students.id', '=', 'student_has_marks.student_id')
                     ->where('student_has_marks.grade_id', $gradeId)
                     ->where('student_has_marks.term_id', $termId)
                     ->where('student_has_marks.subject_id', $subjectId)
                     ->where('student_has_marks.exam_year_id', $examYearId);
            })
            ->leftJoin('marks', 'student_has_marks.marks_id', '=', 'marks.id')
            ->select('students.reg_no', 'students.name', 'marks.mark')
            ->where('students.grade_id', $gradeId)
            ->get();

        // make CSV file
        $fileName = 'Class_Report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Index No', 'Name', 'Mark']); // Header 

            foreach ($students as $student) {
                fputcsv($file, [$student->reg_no, $student->name, $student->mark ?? '-']);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}