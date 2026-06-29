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
        $this->call([SchoolDataSeeder::class, SubjectAssignmentSeeder::class]);

        Admin::create(['user_name' => 'admin', 'password' => bcrypt('admin123')]);

        // Get all grades to loop
        $grades = DB::table('grades')->get();

        foreach ($grades as $grade) {
            // 1. Create 10 students per grade
            for ($i = 1; $i <= 10; $i++) {
                $student = Student::create([
                    'reg_no'   => 'REG-' . $grade->id . '-' . $i,
                    'name'     => 'Student ' . $i . ' of Grade ' . $grade->name,
                    'password' => bcrypt('student123'),
                    'grade_id' => $grade->id, // Linked to grades table
                    'dob'      => '2018-01-01',
                    'reg_date' => now()->format('Y-m-d'), // Set registration date to current date
                    'status'   => 1, // Active status
                    'email'         => 'student' . $grade->id . $i . '@example.com', // Set email
                    'address'       => 'Default Address',                             // Set address
                    'mobile_number' => '0710000000',                                  // Set mobile number
                ]);

                // Assign random bucket subject (Optional logic)
                $bucket = DB::table('subject_has_bucket_subject')->inRandomOrder()->first();
                if ($bucket) {
                    DB::table('student_has_bucket_subject')->insert([
                        'student_id' => $student->id,
                        'subject_has_bucket_subject_id' => $bucket->id
                    ]);
                }
            }

            // 2. Create Class Teacher for this Grade
            $teacher = Teacher::create([
                'name'        => 'Class Teacher ' . $grade->name,
                'user_name'   => 'ct_' . strtolower(str_replace(' ', '', $grade->name)),
                'password'    => bcrypt('teacher123'),
                'role_status' => 1, // Class Teacher
                'email'         => 'teacher_' . $grade->id . '@example.com', 
                'mobile_number' => '077000000' . $grade->id, 
                'access_status' => 1,
            ]);
            
            // Assign Teacher to this Grade
            DB::table('teacher_has_grade')->insert([
                'teacher_id' => $teacher->id,
                'grade_id'   => $grade->id
            ]);

            // 3. Assign Teacher to teach a subject in this grade
            $subject = DB::table('subjects')->inRandomOrder()->first();
            if ($subject) {
                DB::table('teacher_has_subject')->insert([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id
                ]);
            }
        }
    }
}