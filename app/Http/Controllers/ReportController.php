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
            ->distinct()
            ->get();

        if ($request->query('format') !== 'xlsx') {
            $fileName = 'Class_Report_' . date('Y-m-d') . '.csv';
            return response()->stream(function () use ($students) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Index No', 'Name', 'Mark']);
                foreach ($students as $student) fputcsv($file, [$student->reg_no, $student->name, $student->mark ?? '-']);
                fclose($file);
            }, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$fileName}"]);
        }

        $context = DB::table('grades')->where('id', $gradeId)->value('name');
        $subject = DB::table('subjects')->where('id', $subjectId)->value('name');
        $exam = DB::table('exam_years')->where('id', $examYearId)->value('year');
        $rows = $students->map(fn ($student) => [$student->name, $student->reg_no, $context, $subject, $exam, $student->mark ?? '', $this->grade($student->mark), $student->mark === null ? '' : $student->mark]);
        return $this->xlsxDownload('Class_Report_' . now()->format('Y-m-d') . '.xlsx', ['Student Name', 'Admission Number', 'Class', 'Subject', 'Exam', 'Marks', 'Grade', 'Average'], $rows);
    }

    private function grade($mark): string
    {
        if ($mark === null) return '';
        return match (true) { $mark >= 75 => 'A', $mark >= 65 => 'B', $mark >= 50 => 'C', $mark >= 35 => 'S', default => 'F' };
    }

    private function xlsxDownload(string $fileName, array $headings, $rows)
    {
        $file = tempnam(sys_get_temp_dir(), 'iais-report-');
        $zip = new \ZipArchive();
        $zip->open($file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Marks" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $allRows = collect([$headings])->concat($rows);
        $cells = $allRows->values()->map(function ($row, $rowIndex) {
            $sheetRowNumber = $rowIndex + 1;

            $columns = collect($row)->values()->map(function ($value, $columnIndex) use ($sheetRowNumber) {
                $reference = chr(65 + $columnIndex) . $sheetRowNumber;
                return '<c r="' . $reference . '" t="inlineStr"><is><t>' . htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is></c>';
            })->implode('');
            return '<row r="' . $sheetRowNumber . '">' . $columns . '</row>';
        })->implode('');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>' . $cells . '</sheetData></worksheet>');
        $zip->close();
        return response()->download($file, $fileName, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
    }
}
