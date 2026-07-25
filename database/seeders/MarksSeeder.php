<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarksSeeder extends Seeder
{
    public function run(): void
    {
        // Current term is Second Term (id = 2)
        $termId = 2;

        // Get all students
        $students = DB::table('students')->get();

        if ($students->isEmpty()) {
            $this->command->warn('⚠️  No students found.');
            return;
        }

        // Grade 6 core subjects (matching SubjectAssignmentSeeder)
        $grade6Subjects = [
            'Sinhala',
            'English Language',
            'Mathematics',
            'Science',
            'History',
            'Geography',
            'Citizenship Education (Civics)',
            'Religion',
            'ICT',
            'PTS',
            'Health and Physical Education',
        ];

        // Grade 10-11 core subjects
        $grade1011Subjects = [
            'Sinhala',
            'English Language',
            'Mathematics',
            'Science',
            'History',
            'Religion',
        ];

        // A/L subjects
        $grade1213Subjects = [
            'General English',
            'General Information Technology',
        ];

        foreach ($students as $student) {
            $gradeId = $student->grade_id;

            // Pick subjects based on grade
            $subjectNames = match ($gradeId) {
                1, 2, 3, 4 => $grade6Subjects,    // Grade 6-9
                5, 6       => $grade1011Subjects,  // Grade 10-11
                7, 8, 9, 10 => $grade1213Subjects, // Grade 12-13
                default    => $grade6Subjects,
            };

            foreach ($subjectNames as $subjectName) {
                $subjectId = DB::table('subjects')
                    ->where('name', $subjectName)
                    ->value('id');

                if (! $subjectId) {
                    $this->command->warn("⚠️  Subject not found: {$subjectName}");
                    continue;
                }

                // Generate random realistic mark
                $obtained = rand(55, 98);
                $total    = 100;
                $gradeLetter = $this->getGradeLetter(($obtained / $total) * 100);

                // Insert into marks table
                $markId = DB::table('marks')->insertGetId([
                    'student_id'     => $student->id,
                    'subject_id'     => $subjectId,
                    'term_id'        => $termId,
                    'grade_id'       => $gradeId,
                    'mark'           => $obtained,
                    'marks_obtained' => $obtained,
                    'total_marks'    => $total,
                    'grade_letter'   => $gradeLetter,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // Insert into student_has_marks pivot
                DB::table('student_has_marks')->updateOrInsert(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $subjectId,
                        'term_id'    => $termId,
                    ],
                    [
                        'student_reg_no' => $student->reg_no,
                        'marks_id'       => $markId,
                        'grade_id'       => $gradeId,
                        'exam_year_id'   => 1,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]
                );
            }

            $this->command->line("  ✓ Marks seeded for: {$student->name}");
        }

        $this->command->info('✅ Marks seeded successfully for all students.');
    }

    private function getGradeLetter(float $pct): string
    {
        return match (true) {
            $pct >= 90 => 'A+',
            $pct >= 80 => 'A',
            $pct >= 75 => 'A-',
            $pct >= 70 => 'B+',
            $pct >= 65 => 'B',
            $pct >= 60 => 'B-',
            $pct >= 55 => 'C+',
            $pct >= 50 => 'C',
            $pct >= 45 => 'C-',
            $pct >= 40 => 'S',
            default    => 'F',
        };
    }
}