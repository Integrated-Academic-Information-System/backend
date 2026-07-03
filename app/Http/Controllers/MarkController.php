<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mark;
use App\Models\StudentHasMark;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class MarkController extends Controller
{
    public function saveMarks(Request $request)
    {
        $user = auth('teacher')->user();

        if (!$user) {
            $user = auth('admin')->user();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please log in again.'
            ], 401);
        }
        
        $marksData = $request->input('marks_data'); 
        $examYearId = $request->input('exam_year_id'); 
        $termId = $request->input('term_id');
        $gradeId = $request->input('grade_id'); 
        $subjectId = $request->input('subject_id');
        
        // SECURITY CHECK: 
        // Even if the user is a 'Class Incharge', they CANNOT save marks for a subject they do not teach!
        if ($user->getTable() !== 'admins') {
            $hasAccessToGrade = DB::table('teacher_has_grade')
                ->where('teacher_id', $user->id)
                ->where('grade_id', $gradeId)
                ->exists();

            $hasAccessToSubject = DB::table('teacher_has_subject')
                ->where('teacher_id', $user->id)
                ->where('subject_id', $subjectId)
                ->exists();

            // MUST have access to BOTH the grade AND the specific subject to edit marks
            if (!$hasAccessToGrade || !$hasAccessToSubject) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized! You can only edit marks for subjects you specifically teach.'
                ], 403);
            }
        }

        try {
            \Log::info("Payload Received:", $request->all());
            DB::beginTransaction();

            foreach ($marksData as $data) {
                if ($data['mark'] === null || $data['mark'] === '') {
                    continue; 
                }

                $student = Student::find($data['student_id']);
                if (!$student) {
                    continue;
                }

                $existingRecord = StudentHasMark::where('student_id', $student->id)
                    ->where('grade_id', $gradeId)
                    ->where('subject_id', $subjectId)            
                    ->where('term_id', $termId)                
                    ->where('exam_year_id', $examYearId)                
                    ->first();

                if ($existingRecord) {
                    $mark = Mark::find($existingRecord->marks_id);
                    if ($mark) {
                        $mark->update(['mark' => $data['mark']]);
                    }
                } else {
                    $markRecord = Mark::create(['mark' => $data['mark']]);

                    StudentHasMark::create([
                        'student_id'     => $student->id,
                        'student_reg_no' => $student->reg_no ?? 'N/A',
                        'marks_id'       => $markRecord->id,
                        'grade_id'       => $gradeId,
                        'subject_id'     => $subjectId,            
                        'term_id'        => $termId,                
                        'exam_year_id'   => $examYearId               
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
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}