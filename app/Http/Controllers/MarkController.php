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
        
        $validated = $request->validate([
            'marks_data' => ['required', 'array'],
            'marks_data.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'marks_data.*.mark' => ['nullable', 'numeric', 'between:0,100'],
            'exam_year_id' => ['required', 'exists:exam_years,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
        ]);
        $marksData = $validated['marks_data'];
        $examYearId = $request->input('exam_year_id'); 
        $termId = $request->input('term_id');
        $gradeId = $request->input('grade_id'); 
        $subjectId = $request->input('subject_id');
        
        // Class teachers may enter every subject for their assigned class. Subject
        // teachers may only enter the exact subject/class combinations assigned to them.
        if ($user->getTable() !== 'admins') {
            $isClassTeacher = (bool) ($user->is_class_teacher ?? $user->role_status === 1);
            $isSubjectTeacher = (bool) ($user->is_subject_teacher ?? $user->role_status === 0);
            $hasClassAccess = $isClassTeacher && DB::table('teacher_has_grade')
                ->where('teacher_id', $user->id)
                ->where('grade_id', $gradeId)
                ->exists();
            $hasSubjectAccess = $isSubjectTeacher && DB::table('teacher_subject_grade')
                ->where('teacher_id', $user->id)
                ->where('subject_id', $subjectId)
                ->where('grade_id', $gradeId)
                ->exists();
            // Legacy teacher assignments did not record subject-to-class pairs.
            $hasLegacySubjectAccess = $isSubjectTeacher && !DB::table('teacher_subject_grade')->where('teacher_id', $user->id)->exists()
                && DB::table('teacher_has_grade')->where('teacher_id', $user->id)->where('grade_id', $gradeId)->exists()
                && DB::table('teacher_has_subject')->where('teacher_id', $user->id)->where('subject_id', $subjectId)->exists();
            if (!$hasClassAccess && !$hasSubjectAccess && !$hasLegacySubjectAccess) {
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
                if (!$student || (int) $student->grade_id !== (int) $gradeId) continue;

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
                    // marks.student_id and marks.subject_id are required by the current schema.
                    // Omitting them was the reason inserts rolled back and Save appeared to fail.
                    $markRecord = Mark::create(['mark' => $data['mark'], 'student_id' => $student->id, 'subject_id' => $subjectId]);

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
