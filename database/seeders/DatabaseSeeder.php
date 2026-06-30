<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Run core data seeders first
        $this->call([SchoolDataSeeder::class, SubjectAssignmentSeeder::class]);

        // Create Default Admin
        Admin::create(['user_name' => 'admin', 'password' => bcrypt('admin123')]);

        // Helper functions to get IDs easily
        $subjectId = fn($name) => DB::table('subjects')->where('name', $name)->value('id');
        $gradeId = fn($name) => DB::table('grades')->where('name', $name)->value('id');

        $grades = DB::table('grades')->get();

        // ─────────────────────────────────────────────────────────────────────────
        // PHASE 1: CREATE STUDENTS AND ASSIGN BUCKET SUBJECTS
        // ─────────────────────────────────────────────────────────────────────────
        foreach ($grades as $grade) {
            for ($i = 1; $i <= 10; $i++) {
                $student = Student::create([
                    'reg_no'        => 'REG-' . $grade->id . '-' . $i,
                    'name'          => 'Student ' . $i . ' of ' . $grade->name,
                    'password'      => bcrypt('student123'),
                    'grade_id'      => $grade->id,
                    'dob'           => '2018-01-01',
                    'reg_date'      => now()->format('Y-m-d'),
                    'status'        => 1,
                    'email'         => 'student' . $grade->id . $i . '@example.com',
                    'address'       => 'Default Address',
                    'mobile_number' => '0710000000',
                ]);

                // Handle bucket subject categories properly based on grade rules
                $gradeName = $grade->name;
                $bucketIdsToAssign = [];

                if (preg_match('/Grade [6-9]/', $gradeName)) {
                    $bucketIdsToAssign = $this->getRandomBucketSubjects('Aesthetic (G6-9)', 1);
                } elseif (preg_match('/Grade 10|Grade 11/', $gradeName)) {
                    $bucketIdsToAssign = array_merge(
                        $this->getRandomBucketSubjects('Category 01 (G10-11)', 1),
                        $this->getRandomBucketSubjects('Category 02 (G10-11)', 1),
                        $this->getRandomBucketSubjects('Category 03 (G10-11)', 1)
                    );
                } elseif (str_contains($gradeName, 'Arts')) {
                    $bucketIdsToAssign = array_merge(
                        $this->getRandomBucketSubjects('Category 01 (G12-13 Arts)', 1),
                        $this->getRandomBucketSubjects('Category 02 (G12-13 Arts)', 1),
                        $this->getRandomBucketSubjects('Category 03 (G12-13 Arts)', 1)
                    );
                } elseif (str_contains($gradeName, 'Commerce')) {
                    $bucketIdsToAssign = $this->getRandomBucketSubjects('Commerce Core Electives', 2);
                }

                foreach ($bucketIdsToAssign as $bsId) {
                    DB::table('student_has_bucket_subject')->insert([
                        'student_id' => $student->id,
                        'subject_has_bucket_subject_id' => $bsId
                    ]);
                }
            }

            // ─────────────────────────────────────────────────────────────────────────
            // PHASE 2: CREATE CLASS INCHARGES (ROLE_STATUS = 1)
            // ─────────────────────────────────────────────────────────────────────────
            $classTeacher = Teacher::create([
                'name'          => 'Class Teacher ' . $grade->name,
                'user_name'     => 'ct_' . strtolower(str_replace(' ', '', $grade->name)),
                'password'      => bcrypt('teacher123'),
                'role_status'   => 1, // 1 = Class Incharge
                'email'         => 'teacher_ct_' . $grade->id . '@example.com', 
                'mobile_number' => '077000000' . $grade->id, 
                'access_status' => 1,
            ]);
            
            // Link Class Teacher to their OWN Grade for administrative/viewing rights
            DB::table('teacher_has_grade')->insert([
                'teacher_id' => $classTeacher->id,
                'grade_id'   => $grade->id
            ]);

            // Assign them a random core subject inside their OWN class to teach as well
            $randomCoreSub = DB::table('grade_has_subject')->where('grade_id', $grade->id)->inRandomOrder()->first();
            if ($randomCoreSub) {
                DB::table('teacher_has_subject')->insert([
                    'teacher_id' => $classTeacher->id,
                    'subject_id' => $randomCoreSub->subject_id
                ]);
            }
        }

        // ─────────────────────────────────────────────────────────────────────────
        // PHASE 3: CROSS-ASSIGN CLASS TEACHERS TO OTHER GRADES (AS SUBJECT TEACHERS)
        // ─────────────────────────────────────────────────────────────────────────
        // Example Rule: Grade 6 Class Teacher will also teach Sinhala in Grade 8
        $g6Teacher = Teacher::where('user_name', 'ct_grade6')->first();
        $g8Id = $gradeId('Grade 8');
        $sinhalaId = $subjectId('Sinhala');

        if ($g6Teacher && $g8Id && $sinhalaId) {
            // Give Grade 6 Teacher teaching access to Grade 8
            DB::table('teacher_has_grade')->insert(['teacher_id' => $g6Teacher->id, 'grade_id' => $g8Id]);
            // Assign Sinhala subject to Grade 6 Teacher for Grade 8 mapping
            DB::table('teacher_has_subject')->insert(['teacher_id' => $g6Teacher->id, 'subject_id' => $sinhalaId]);
        }

        // ─────────────────────────────────────────────────────────────────────────
        // PHASE 4: CREATE PURE SUBJECT TEACHERS (ROLE_STATUS = 0)
        // ─────────────────────────────────────────────────────────────────────────
        // Teacher 1: Mathematics Expert for Middle School (Grades 6, 7, 9)
        $mathTeacher1 = Teacher::create([
            'name'          => 'Nimal (Math Teacher 1)',
            'user_name'     => 'teacher_math1',
            'password'      => bcrypt('teacher123'),
            'role_status'   => 0, // 0 = Pure Subject Teacher (No class incharge)
            'email'         => 'math1@example.com',
            'mobile_number' => '0771112223',
            'access_status' => 1,
        ]);

        $mathId = $subjectId('Mathematics');
        $math1Grades = [$gradeId('Grade 6'), $gradeId('Grade 7'), $gradeId('Grade 9')];

        foreach ($math1Grades as $gId) {
            if ($gId && $mathId) {
                DB::table('teacher_has_grade')->insert(['teacher_id' => $mathTeacher1->id, 'grade_id' => $gId]);
            }
        }
        if ($mathId) {
            DB::table('teacher_has_subject')->insert(['teacher_id' => $mathTeacher1->id, 'subject_id' => $mathId]);
        }

        // Teacher 2: Mathematics Expert for Upper School (Grades 8, 10, 11)
        $mathTeacher2 = Teacher::create([
            'name'          => 'Sunil (Math Teacher 2)',
            'user_name'     => 'teacher_math2',
            'password'      => bcrypt('teacher123'),
            'role_status'   => 0,
            'email'         => 'math2@example.com',
            'mobile_number' => '0774445556',
            'access_status' => 1,
        ]);

        $math2Grades = [$gradeId('Grade 8'), $gradeId('Grade 10'), $gradeId('Grade 11')];
        foreach ($math2Grades as $gId) {
            if ($gId && $mathId) {
                DB::table('teacher_has_grade')->insert(['teacher_id' => $mathTeacher2->id, 'grade_id' => $gId]);
            }
        }
        if ($mathId) {
            DB::table('teacher_has_subject')->insert(['teacher_id' => $mathTeacher2->id, 'subject_id' => $mathId]);
        }

        $this->command->info('✅ Advanced School Matrix seeded successfully with cross-role boundaries!');
    }

    /**
     * Fetch random bucket subjects securely
     */
    private function getRandomBucketSubjects($bucketName, $limit = 1)
    {
        $bucket = DB::table('bucket_subjects')->where('name', $bucketName)->first();
        if (!$bucket) return [];

        return DB::table('subject_has_bucket_subject')
            ->where('bucket_subject_id', $bucket->id)
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id')
            ->toArray();
    }
}