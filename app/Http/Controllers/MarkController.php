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
        // Extract data from the incoming JSON request
        $marksData = $request->input('marks_data'); // Array of { student_id, mark }
        
        try {
            // Use a database transaction to ensure all or nothing is saved
            DB::beginTransaction();

            foreach ($marksData as $data) {
                // Skip if no mark is provided for this student
                if ($data['mark'] === null || $data['mark'] === '') {
                    continue; 
                }

                $student = Student::find($data['student_id']);

                // 1. Database eke me lamayata kalin marks dala thiyenawada kiyala check karanawa
                $existingRecord = StudentHasMark::where('student_id', $student->id)
                    ->where('grade_has_sub_grade_id', 1) // Dummy ID
                    ->where('subject_id', 1)             // Dummy ID
                    ->where('term_id', 1)                // Dummy ID
                    ->where('exam_year_id', 1)           // Dummy ID
                    ->first();

                if ($existingRecord) {
                    // 2. if there is an existing record, update the mark in the 'marks' table
                    $mark = Mark::find($existingRecord->marks_id);
                    if ($mark) {
                        $mark->update([
                            'mark' => $data['mark']
                        ]);
                    }
                } else {
                    // 3. If no existing record, create a new mark in the 'marks' table
                    $markRecord = Mark::create([
                        'mark' => $data['mark']
                    ]);

                    // 4. Create the relationship in 'student_has_marks' table
                    // Note: Hardcoded IDs (1) are used for relationships (term, subject, etc.) 
                    // until you build the dynamic dropdown data fetching from DB.
                    StudentHasMark::create([
                        'student_id' => $student->id,
                        'student_reg_no' => $student->reg_no ?? 'N/A',
                        'marks_id' => $markRecord->id,
                        'grade_has_sub_grade_id' => 1, // Dummy ID - Update later
                        'subject_id' => 1,             // Dummy ID - Update later
                        'term_id' => 1,                // Dummy ID - Update later
                        'exam_year_id' => 1            // Dummy ID - Update later
                    ]);
                }

            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Marks successfully saved to the database!'
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