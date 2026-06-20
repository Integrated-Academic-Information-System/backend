<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mark;
use App\Models\StudentHasMark;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class MarkController extends Controller
{
    // Function to handle saving marks sent from the frontend
    public function saveMarks(Request $request)
    {
        $marksData = $request->input('marks_data'); 
        
        // Extract dynamic IDs from request
        $examYearId = $request->input('exam_year_id'); 
        $termId = $request->input('term_id');
        $gradeId = $request->input('grade_id');
        $subjectId = $request->input('subject_id');
        
        try {
            DB::beginTransaction();

            foreach ($marksData as $data) {
                if ($data['mark'] === null || $data['mark'] === '') {
                    continue; 
                }

                $student = Student::find($data['student_id']);

                // Find if a record already exists with these dynamic IDs
                $existingRecord = StudentHasMark::where('student_id', $student->id)
                    ->where('grade_has_sub_grade_id', $gradeId) 
                    ->where('subject_id', $subjectId)           
                    ->where('term_id', $termId)                 
                    ->where('exam_year_id', $examYearId) // Real year ID added here                  
                    ->first();

                if ($existingRecord) {
                    // Update existing mark
                    $mark = Mark::find($existingRecord->marks_id);
                    if ($mark) {
                        $mark->update([
                            'mark' => $data['mark']
                        ]);
                    }
                } else {
                    // Create new mark and link it using dynamic IDs
                    $markRecord = Mark::create([
                        'mark' => $data['mark']
                    ]);

                    StudentHasMark::create([
                        'student_id' => $student->id,
                        'student_reg_no' => $student->reg_no ?? 'N/A',
                        'marks_id' => $markRecord->id,
                        'grade_has_sub_grade_id' => $gradeId, 
                        'subject_id' => $subjectId,           
                        'term_id' => $termId,                 
                        'exam_year_id' => $examYearId // Real year ID added here                   
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Marks successfully saved and updated!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save marks.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}