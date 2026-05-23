<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Grade;
use App\Models\Term;
use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    public function run(): void {

        // Grades
        $grades = [];
        foreach ([6,7,8,9,10,11] as $level) {
            $grades[$level] = Grade::create(['name' => "Grade $level", 'level' => $level]);
        }

        // Academic Year
        $year = AcademicYear::create(['year' => '2023-2024', 'is_active' => true]);

        // Terms
        Term::create(['name' => 'First Term',    'order' => 1]);
        Term::create(['name' => 'Mid-Term Assessment', 'order' => 2]);
        Term::create(['name' => 'Final Term',    'order' => 3]);

        // Subjects for Grade 7
        $subjects = ['Mathematics', 'Science', 'English', 'Sinhala', 'History'];
        foreach ($subjects as $sub) {
            Subject::create(['name' => $sub, 'grade_id' => $grades[7]->id]);
        }

        // Admin user
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@school.lk',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Subject Teacher
        $teacherUser = User::create([
            'name'     => 'Kumara Perera',
            'email'    => 'teacher@school.lk',
            'password' => Hash::make('password'),
            'role'     => 'subject_teacher',
        ]);
        Teacher::create(['user_id' => $teacherUser->id, 'type' => 'subject_teacher', 'grade_id' => $grades[7]->id]);

        // Class Incharge
        $inchargeUser = User::create([
            'name'     => 'Nimal Silva',
            'email'    => 'incharge@school.lk',
            'password' => Hash::make('password'),
            'role'     => 'class_incharge',
        ]);
        Teacher::create(['user_id' => $inchargeUser->id, 'type' => 'class_incharge', 'grade_id' => $grades[7]->id]);

        // Students
        $studentNames = ['Adrian Thorne', 'Beatrix Vance', 'Cassian Grey', 'Daphne Laize'];
        foreach ($studentNames as $i => $name) {
            $u = User::create([
                'name'     => $name,
                'email'    => strtolower(str_replace(' ', '.', $name)) . '@school.lk',
                'password' => Hash::make('password'),
                'role'     => 'student',
            ]);
            Student::create([
                'user_id'  => $u->id,
                'index_no' => '1234' . ($i + 5),
                'grade_id' => $grades[7]->id,
            ]);
        }
    }
}