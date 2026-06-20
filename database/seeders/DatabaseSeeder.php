<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. MUST RUN FIRST: Create terms, years, grades, and subjects
        // This ensures that grade_has_sub_grade_id = 1 actually exists before adding students
        $this->call([
            SchoolDataSeeder::class,
        ]);


        // 2. Create Admin
        Admin::create([
            'user_name' => 'admin',
            'password'  => bcrypt('admin123'),
        ]);

        // ---------------------------------------------------------
        // GRADE 6 - A (grade_has_sub_grade_id = 1)
        // ---------------------------------------------------------
        Student::create([
            'reg_no'        => 'REG001',
            'password'      => bcrypt('student123'),
            'name'          => 'John Doe',
            'address'       => '123 Main St, Colombo',
            'reg_date'      => '2024-01-15',
            'leave_date'    => null,
            'mobile_number' => '0771234567',
            'email'         => 'john@example.com',
            'status'        => 1,
            'grade_has_sub_grade_id' => 1, 
        ]);

        Student::create([
            'reg_no'        => 'REG002',
            'password'      => bcrypt('student123'),
            'name'          => 'Kasun Perera',
            'address'       => 'Temple Road, Maharagama',
            'reg_date'      => '2024-01-16',
            'leave_date'    => null,
            'mobile_number' => '0712223334',
            'email'         => 'kasun@example.com',
            'status'        => 1,
            'grade_has_sub_grade_id' => 1, 
        ]);

        // ---------------------------------------------------------
        // GRADE 6 - B (grade_has_sub_grade_id = 2)
        // ---------------------------------------------------------
        Student::create([
            'reg_no'        => 'REG003',
            'password'      => bcrypt('student123'),
            'name'          => 'Jane Smith',
            'address'       => '456 Lake Rd, Kandy',
            'reg_date'      => '2024-02-20',
            'leave_date'    => null,
            'mobile_number' => '0777654321',
            'email'         => 'jane@example.com',
            'status'        => 1,
            'grade_has_sub_grade_id' => 2, 
        ]);

        Student::create([
            'reg_no'        => 'REG004',
            'password'      => bcrypt('student123'),
            'name'          => 'Nimali Silva',
            'address'       => 'School Lane, Galle',
            'reg_date'      => '2024-01-18',
            'leave_date'    => null,
            'mobile_number' => '0723334445',
            'email'         => 'nimali@example.com',
            'status'        => 1,
            'grade_has_sub_grade_id' => 2, 
        ]);

        // ---------------------------------------------------------
        // GRADE 6 - C (grade_has_sub_grade_id = 3)
        // ---------------------------------------------------------
        Student::create([
            'reg_no'        => 'REG005',
            'password'      => bcrypt('student123'),
            'name'          => 'Ruwan Kumara',
            'address'       => 'Station Road, Matara',
            'reg_date'      => '2024-01-10',
            'leave_date'    => null,
            'mobile_number' => '0754445556',
            'email'         => 'ruwan@example.com',
            'status'        => 1,
            'grade_has_sub_grade_id' => 3, 
        ]);

        Student::create([
            'reg_no'        => 'REG006',
            'password'      => bcrypt('student123'),
            'name'          => 'Sanduni Fernando',
            'address'       => 'Galle Road, Panadura',
            'reg_date'      => '2024-01-11',
            'leave_date'    => null,
            'mobile_number' => '0765556667',
            'email'         => 'sanduni@example.com',
            'status'        => 1,
            'grade_has_sub_grade_id' => 3, 
        ]);

        // ---------------------------------------------------------
        // GRADE 7 - A (grade_has_sub_grade_id = 4)
        // ---------------------------------------------------------
        Student::create([
            'reg_no'        => 'REG007',
            'password'      => bcrypt('student123'),
            'name'          => 'Kamal Addararachchi',
            'address'       => 'Flower Road, Kurunegala',
            'reg_date'      => '2023-01-15',
            'leave_date'    => null,
            'mobile_number' => '0786667778',
            'email'         => 'kamal@example.com',
            'status'        => 1,
            'grade_has_sub_grade_id' => 4, 
        ]);

        Student::create([
            'reg_no'        => 'REG008',
            'password'      => bcrypt('student123'),
            'name'          => 'Amandi Perera',
            'address'       => 'Lake View, Kandy',
            'reg_date'      => '2023-01-20',
            'leave_date'    => null,
            'mobile_number' => '0717778889',
            'email'         => 'amandi@example.com',
            'status'        => 1,
            'grade_has_sub_grade_id' => 4, 
        ]);

        // ---------------------------------------------------------
        // GRADE 7 - B (grade_has_sub_grade_id = 5)
        // ---------------------------------------------------------
        Student::create([
            'reg_no'        => 'REG009',
            'password'      => bcrypt('student123'),
            'name'          => 'Dasun Shanaka',
            'address'       => 'Beach Road, Negombo',
            'reg_date'      => '2023-02-15',
            'leave_date'    => null,
            'mobile_number' => '0728889990',
            'email'         => 'dasun@example.com',
            'status'        => 1,
            'grade_has_sub_grade_id' => 5, 
        ]);

        Student::create([
            'reg_no'        => 'REG010',
            'password'      => bcrypt('student123'),
            'name'          => 'Piyumi Hansamali',
            'address'       => 'High Level Rd, Nugegoda',
            'reg_date'      => '2023-02-18',
            'leave_date'    => null,
            'mobile_number' => '0779990001',
            'email'         => 'piyumi@example.com',
            'status'        => 1,
            'grade_has_sub_grade_id' => 5, 
        ]);

        // 3. Create Teachers
        Teacher::create([
            'name'          => 'Mr. Kamal Perera',
            'user_name'     => 'teacher_kamal',
            'password'      => bcrypt('teacher123'),
            'email'         => 'teacher1@example.com',
            'mobile_number' => '0712345678',
            'access_status' => 1,
            'role_status'   => 1, // Class Teacher
        ]);

        Teacher::create([
            'name'          => 'Ms. Nimali Silva',
            'user_name'     => 'teacher_nimali',
            'password'      => bcrypt('teacher123'),
            'email'         => 'teacher2@example.com',
            'mobile_number' => '0723456789',
            'access_status' => 1,
            'role_status'   => 0, // Subject Teacher
        ]);

    
    }
}