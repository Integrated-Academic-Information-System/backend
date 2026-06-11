<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Admin::create([
            'user_name' => 'admin',
            'password'  => bcrypt('admin123'),
        ]);


        // Student seeds
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
        ]);

        Student::create([
            'reg_no'        => 'REG002',
            'password'      => bcrypt('student123'),
            'name'          => 'Jane Smith',
            'address'       => '456 Lake Rd, Kandy',
            'reg_date'      => '2024-02-20',
            'leave_date'    => null,
            'mobile_number' => '0777654321',
            'email'         => 'jane@example.com',
            'status'        => 1,
        ]);

        // Teachers
        Teacher::create([
            'name'          => 'Mr. Kamal Perera',
            'user_name'     => 'teacher_kamal',
            'password'      => bcrypt('teacher123'),
            'email'         => 'teacher1@example.com',
            'mobile_number' => '0712345678',
            'access_status' => 1,
            'role_status'   => 1, //Class Teacher
        ]);

        Teacher::create([
            'name'          => 'Ms. Nimali Silva',
            'user_name'     => 'teacher_nimali',
            'password'      => bcrypt('teacher123'),
            'email'         => 'teacher2@example.com',
            'mobile_number' => '0723456789',
            'access_status' => 1,
            'role_status'   => 0, //Subject Teacher
        ]);
    }
}
