<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Insert Terms
        $terms = ['First Term', 'Second Term', 'Third Term'];
        foreach ($terms as $term) {
            DB::table('terms')->updateOrInsert(['name' => $term]);
        }

        // 2. Insert Exam Years
        $years = ['2023-2024', '2024-2025', '2025-2026', '2026-2027'];
        foreach ($years as $year) {
            DB::table('exam_years')->updateOrInsert(['year' => $year]);
        }

        // 3. Insert Grades (6 to 13)
        $grades = ['Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12', 'Grade 13'];
        foreach ($grades as $grade) {
            DB::table('grades')->updateOrInsert(['name' => $grade]);
        }

        // 4. Insert Sub Grades (A, B, C)
        $subGrades = ['A', 'B', 'C'];
        foreach ($subGrades as $sub) {
            DB::table('sub_grades')->updateOrInsert(['name' => $sub]);
        }

        // 5. Create Combinations in grade_has_sub_grade (e.g., Grade 6 - A)
        $dbGrades = DB::table('grades')->get();
        $dbSubGrades = DB::table('sub_grades')->get();
        foreach ($dbGrades as $g) {
            foreach ($dbSubGrades as $sg) {
                DB::table('grade_has_sub_grade')->updateOrInsert([
                    'grade_id' => $g->id,
                    'sub_grade_id' => $sg->id
                ]);
            }
        }

        // 6. Insert All Subjects
        $subjects = [
            'First Language (Sinhala/Tamil)', 'English Language', 'Mathematics', 'Science', 
            'History', 'Geography', 'Citizenship Education (Civics)', 'Religion', 
            'Aesthetic Subject (Art/Music/Dancing/Drama)', 'ICT', 'PTS',
            'Business & Accounting Studies', 'Entrepreneurship Studies', 'Foreign Languages',
            'Agriculture & Food Technology', 'Aquatic Bio-Resources Technology', 'Home Economics', 
            'Arts & Crafts', 'Mechanical/Electrical/Electronic Technology', 'Design & Technology',
            'Combined Mathematics', 'Physics', 'Chemistry', 'Biology', 'Agricultural Science',
            'Accounting', 'Business Studies', 'Economics', 'Science for Technology (SFT)',
            'Engineering Technology', 'Bio-Systems Technology', 'Logic', 'Media Studies'
        ];
        
        foreach ($subjects as $index => $sub) {
            // Generate a dynamic subject code like S001, S002, S003 based on the array index
            $code = 'S' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

            DB::table('subjects')->updateOrInsert(
                ['name' => $sub],
                ['subject_code' => $code] // Pass the generated code to the database
            );
        }
    }
}