<?php
//Studentbucketseeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentBucketSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────
        // subject_has_bucket_subject IDs reference:
        //
        // AESTHETIC (G6-9) — bucket_subject_id = 1
        //   1  = Art
        //   2  = Music
        //   3  = Dancing
        //   4  = Drama
        //
        // CATEGORY 01 (G10-11) — bucket_subject_id = 2
        //   5  = Citizenship Education (Civics)
        //   6  = Business Studies
        //   7  = Geography
        //   8  = Accounting
        //
        // CATEGORY 02 (G10-11) — bucket_subject_id = 3
        //   9  = Art
        //   10 = Dancing
        //   11 = Music
        //
        // CATEGORY 03 (G10-11) — bucket_subject_id = 4
        //   12 = ICT
        //   13 = Home Science
        //   14 = Agricultural Science
        //   15 = Health and Physical Education
        //
        // CATEGORY 01 (G12-13) — bucket_subject_id = 5
        //   16 = English Language
        //   17 = Sinhala
        //
        // CATEGORY 02 (G12-13) — bucket_subject_id = 6
        //   18 = History
        //   19 = Political Science
        //   20 = Communication & Media Studies
        //   21 = Geography
        //   22 = Business Studies
        //   23 = Accounting
        //   24 = Economics
        //   25 = ICT
        //   26 = Home Science
        //
        // CATEGORY 03 (G12-13) — bucket_subject_id = 7
        //   27 = Buddhist Civilization
        //   28 = Art
        //   29 = Dancing
        //   30 = Music
        // ─────────────────────────────────────────────────────────

        $assignments = [
            // ── GRADE 6-A (grade_has_sub_grade_id = 1) ──────────
            // REG001 - John Doe → Art
            [
                'student_id'                    => 1,
                'subject_has_bucket_subject_id' => 1, // Art
            ],
            // REG002 - Kasun Perera → Music
            [
                'student_id'                    => 2,
                'subject_has_bucket_subject_id' => 2, // Music
            ],

            // ── GRADE 6-B (grade_has_sub_grade_id = 2) ──────────
            // REG003 - Jane Smith → Dancing
            [
                'student_id'                    => 3,
                'subject_has_bucket_subject_id' => 3, // Dancing
            ],
            // REG004 - Nimali Silva → Drama
            [
                'student_id'                    => 4,
                'subject_has_bucket_subject_id' => 4, // Drama
            ],

            // ── GRADE 6-C (grade_has_sub_grade_id = 3) ──────────
            // REG005 - Ruwan Kumara → Art
            [
                'student_id'                    => 5,
                'subject_has_bucket_subject_id' => 1, // Art
            ],
            // REG006 - Sanduni Fernando → Music
            [
                'student_id'                    => 6,
                'subject_has_bucket_subject_id' => 2, // Music
            ],

            // ── GRADE 7-A (grade_has_sub_grade_id = 4) ──────────
            // REG007 - Kamal Addararachchi → Dancing
            [
                'student_id'                    => 7,
                'subject_has_bucket_subject_id' => 3, // Dancing
            ],
            // REG008 - Amandi Perera → Drama
            [
                'student_id'                    => 8,
                'subject_has_bucket_subject_id' => 4, // Drama
            ],

            // ── REG009 & REG010 have grade_has_sub_grade_id = 16 & 23
            // which are Grade 11-B and Grade 12-C respectively
            // ── GRADE 11 student needs G10-11 categories ─────────
            // REG009 - Dasun Shanaka (Grade 11-B, ghsg=16)
            //   Category 01 → Geography
            [
                'student_id'                    => 9,
                'subject_has_bucket_subject_id' => 7, // Geography
            ],
            //   Category 02 → Art
            [
                'student_id'                    => 9,
                'subject_has_bucket_subject_id' => 9, // Art
            ],
            //   Category 03 → ICT
            [
                'student_id'                    => 9,
                'subject_has_bucket_subject_id' => 12, // ICT
            ],

            // ── GRADE 12 student needs G12-13 categories ─────────
            // REG010 - Piyumi Hansamali (Grade 12-C, ghsg=23)
            //   Category 01 → Sinhala
            [
                'student_id'                    => 10,
                'subject_has_bucket_subject_id' => 17, // Sinhala
            ],
            //   Category 02 → Economics
            [
                'student_id'                    => 10,
                'subject_has_bucket_subject_id' => 24, // Economics
            ],
            //   Category 03 → Art
            [
                'student_id'                    => 10,
                'subject_has_bucket_subject_id' => 28, // Art
            ],
        ];

        foreach ($assignments as $assignment) {
            DB::table('student_has_bucket_subject')->updateOrInsert(
                [
                    'student_id'                    => $assignment['student_id'],
                    'subject_has_bucket_subject_id' => $assignment['subject_has_bucket_subject_id'],
                ]
            );
        }

        $this->command->info('✅ Student bucket subjects assigned successfully.');
    }
}