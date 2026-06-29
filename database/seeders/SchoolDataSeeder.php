<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Insert Terms
        foreach (['First Term', 'Second Term', 'Third Term'] as $term) 
            DB::table('terms')->updateOrInsert(['name' => $term]);

        // 2. Insert Exam Years
        foreach (['2023-2024', '2024-2025', '2025-2026'] as $year) 
            DB::table('exam_years')->updateOrInsert(['year' => $year]);

        // 3. Insert Grades (6-13) - No sub-grades logic here anymore
        foreach (['Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12', 'Grade 13'] as $gradeName) {
            DB::table('grades')->updateOrInsert(['name' => $gradeName]);
        }
    }
}