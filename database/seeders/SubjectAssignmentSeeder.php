<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────
        // HELPERS
        // ─────────────────────────────────────────
        $subjectId = fn($name) => DB::table('subjects')->where('name', $name)->value('id');

        $ghsgId = fn($gradeName, $sgName) => DB::table('grade_has_sub_grade')
            ->join('grades',     'grade_has_sub_grade.grade_id',     '=', 'grades.id')
            ->join('sub_grades', 'grade_has_sub_grade.sub_grade_id', '=', 'sub_grades.id')
            ->where('grades.name',     $gradeName)
            ->where('sub_grades.name', $sgName)
            ->value('grade_has_sub_grade.id');

        $bucketId = fn($name) => DB::table('bucket_subjects')->where('name', $name)->value('id');

        $subGrades = ['A', 'B', 'C'];

        // ─────────────────────────────────────────
        // STEP 1 — INSERT ALL SUBJECTS
        // ─────────────────────────────────────────
        $allSubjects = [
            // Grade 6-9 core
            ['name' => 'Sinhala',                                    'code' => 'S001'],
            ['name' => 'Tamil',                                      'code' => 'S002'],
            ['name' => 'English Language',                           'code' => 'S003'],
            ['name' => 'Mathematics',                                'code' => 'S004'],
            ['name' => 'Science',                                    'code' => 'S005'],
            ['name' => 'History',                                    'code' => 'S006'],
            ['name' => 'Geography',                                  'code' => 'S007'],
            ['name' => 'Citizenship Education (Civics)',             'code' => 'S008'],
            ['name' => 'Religion',                                   'code' => 'S009'],
            ['name' => 'ICT',                                        'code' => 'S010'],
            ['name' => 'PTS',                                        'code' => 'S011'],
            ['name' => 'Health and Physical Education',              'code' => 'S012'],
            // Aesthetic options (bucket for 6-9)
            ['name' => 'Art',                                        'code' => 'S013'],
            ['name' => 'Music',                                      'code' => 'S014'],
            ['name' => 'Dancing',                                    'code' => 'S015'],
            ['name' => 'Drama',                                      'code' => 'S016'],
            // Grade 10-11 Category 01
            ['name' => 'Business Studies',                           'code' => 'S017'],
            ['name' => 'Accounting',                                 'code' => 'S018'],
            // Grade 10-11 Category 02 (Art, Dancing, Music already added)
            // Grade 10-11 Category 03
            ['name' => 'Home Science',                               'code' => 'S019'],
            ['name' => 'Agricultural Science',                       'code' => 'S020'],
            // Grade 12-13 Category 02
            ['name' => 'Political Science',                          'code' => 'S021'],
            ['name' => 'Communication & Media Studies',              'code' => 'S022'],
            ['name' => 'Economics',                                  'code' => 'S023'],
            // Grade 12-13 Category 03
            ['name' => 'Buddhist Civilization',                      'code' => 'S024'],
            // Grade 12-13 Common
            ['name' => 'General English',                            'code' => 'S025'],
            ['name' => 'General Information Technology',             'code' => 'S026'],
        ];

        foreach ($allSubjects as $subject) {
            DB::table('subjects')->updateOrInsert(
                ['name' => $subject['name']],
                ['subject_code' => $subject['code']]
            );
        }

        $this->command->info('✅ Subjects inserted.');

        // ─────────────────────────────────────────
        // STEP 2 — BUCKET SUBJECTS (category groups)
        // ─────────────────────────────────────────
        $buckets = [
            // Grade 6-9
            ['name' => 'Aesthetic (G6-9)',    'code' => 'BK001'],
            // Grade 10-11
            ['name' => 'Category 01 (G10-11)', 'code' => 'BK002'],
            ['name' => 'Category 02 (G10-11)', 'code' => 'BK003'],
            ['name' => 'Category 03 (G10-11)', 'code' => 'BK004'],
            // Grade 12-13
            ['name' => 'Category 01 (G12-13)', 'code' => 'BK005'],
            ['name' => 'Category 02 (G12-13)', 'code' => 'BK006'],
            ['name' => 'Category 03 (G12-13)', 'code' => 'BK007'],
        ];

        foreach ($buckets as $bucket) {
            DB::table('bucket_subjects')->updateOrInsert(
                ['name' => $bucket['name']],
                ['subject_code' => $bucket['code']]
            );
        }

        $this->command->info('✅ Bucket subjects inserted.');

        // ─────────────────────────────────────────
        // STEP 3 — LINK SUBJECTS TO BUCKETS
        //          via subject_has_bucket_subject
        // ─────────────────────────────────────────
        $bucketSubjectMap = [
            // Grade 6-9: Aesthetic choice
            'Aesthetic (G6-9)' => [
                'Art', 'Music', 'Dancing', 'Drama',
            ],
            // Grade 10-11 categories
            'Category 01 (G10-11)' => [
                'Citizenship Education (Civics)', 'Business Studies',
                'Geography', 'Accounting',
            ],
            'Category 02 (G10-11)' => [
                'Art', 'Dancing', 'Music',
            ],
            'Category 03 (G10-11)' => [
                'ICT', 'Home Science', 'Agricultural Science',
                'Health and Physical Education',
            ],
            // Grade 12-13 categories
            'Category 01 (G12-13)' => [
                'English Language', 'Sinhala',
            ],
            'Category 02 (G12-13)' => [
                'History', 'Political Science', 'Communication & Media Studies',
                'Geography', 'Business Studies', 'Accounting',
                'Economics', 'ICT', 'Home Science',
            ],
            'Category 03 (G12-13)' => [
                'Buddhist Civilization', 'Art', 'Dancing', 'Music',
            ],
        ];

        foreach ($bucketSubjectMap as $bucketName => $subjectNames) {
            $bId = $bucketId($bucketName);
            foreach ($subjectNames as $subjectName) {
                $sId = $subjectId($subjectName);
                if ($bId && $sId) {
                    DB::table('subject_has_bucket_subject')->updateOrInsert([
                        'bucket_subject_id' => $bId,
                        'subject_id'        => $sId,
                    ]);
                }
            }
        }

        $this->command->info('✅ Subjects linked to bucket groups.');

        // ─────────────────────────────────────────
        // STEP 4 — CORE SUBJECTS PER GRADE
        //          via grade_has_subject
        // ─────────────────────────────────────────

        // Grade 6-9 fixed core (Aesthetic is a bucket — NOT here)
        $grade69Core = [
            'Sinhala', 'Tamil', 'English Language', 'Mathematics',
            'Science', 'History', 'Geography',
            'Citizenship Education (Civics)', 'Religion',
            'ICT', 'PTS', 'Health and Physical Education',
        ];

        // Grade 10-11 fixed core (categories are buckets — NOT here)
        $grade1011Core = [
            'Sinhala', 'English Language', 'Mathematics',
            'Science', 'History', 'Religion',
        ];

        // Grade 12-13 fixed core (categories are buckets — NOT here)
        $grade1213Core = [
            'General English',
            'General Information Technology',
        ];

        $gradeCoreMap = [
            'Grade 6'  => $grade69Core,
            'Grade 7'  => $grade69Core,
            'Grade 8'  => $grade69Core,
            'Grade 9'  => $grade69Core,
            'Grade 10' => $grade1011Core,
            'Grade 11' => $grade1011Core,
            'Grade 12' => $grade1213Core,
            'Grade 13' => $grade1213Core,
        ];

        foreach ($gradeCoreMap as $gradeName => $coreSubjects) {
            foreach ($subGrades as $sg) {
                $ghsg = $ghsgId($gradeName, $sg);
                if (!$ghsg) {
                    $this->command->warn("⚠️  grade_has_sub_grade not found: $gradeName - $sg");
                    continue;
                }
                foreach ($coreSubjects as $subjectName) {
                    $sId = $subjectId($subjectName);
                    if ($sId) {
                        DB::table('grade_has_subject')->updateOrInsert([
                            'grade_has_sub_grade_id' => $ghsg,
                            'subject_id'             => $sId,
                        ]);
                    } else {
                        $this->command->warn("⚠️  Subject not found: $subjectName");
                    }
                }
            }
        }

        $this->command->info('✅ Core subjects assigned to all grades.');
        $this->command->info('🎉 All done! SubjectAssignmentSeeder completed successfully.');
    }
}