<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $subjectId = fn($name) => DB::table('subjects')->where('name', $name)->value('id');
        $gradeId = fn($name) => DB::table('grades')->where('name', $name)->value('id');
        $bucketId = fn($name) => DB::table('bucket_subjects')->where('name', $name)->value('id');

        // ─────────────────────────────────────────
        // 1. INSERT ALL SUBJECTS
        // ─────────────────────────────────────────
        $allSubjects = [
            ['name' => 'Sinhala', 'code' => 'S001'],
            ['name' => 'Tamil', 'code' => 'S002'],
            ['name' => 'English Language', 'code' => 'S003'],
            ['name' => 'Mathematics', 'code' => 'S004'],
            ['name' => 'Science', 'code' => 'S005'],
            ['name' => 'History', 'code' => 'S006'],
            ['name' => 'Geography', 'code' => 'S007'],
            ['name' => 'Citizenship Education (Civics)', 'code' => 'S008'],
            ['name' => 'Religion', 'code' => 'S009'],
            ['name' => 'ICT', 'code' => 'S010'],
            ['name' => 'PTS', 'code' => 'S011'],
            ['name' => 'Health and Physical Education', 'code' => 'S012'],
            ['name' => 'Art', 'code' => 'S013'],
            ['name' => 'Music', 'code' => 'S014'],
            ['name' => 'Dancing', 'code' => 'S015'],
            ['name' => 'Drama', 'code' => 'S016'],
            ['name' => 'Business Studies', 'code' => 'S017'],
            ['name' => 'Accounting', 'code' => 'S018'],
            ['name' => 'Home Science', 'code' => 'S019'],
            ['name' => 'Agricultural Science', 'code' => 'S020'],
            ['name' => 'Political Science', 'code' => 'S021'],
            ['name' => 'Communication & Media Studies', 'code' => 'S022'],
            ['name' => 'Economics', 'code' => 'S023'],
            ['name' => 'Buddhist Civilization', 'code' => 'S024'],
            ['name' => 'General English', 'code' => 'S025'],
            ['name' => 'General Information Technology', 'code' => 'S026'],
            // New Subject Added for Commerce Stream
            ['name' => 'Business Statistics', 'code' => 'S027'],
        ];

        foreach ($allSubjects as $subject) {
            DB::table('subjects')->updateOrInsert(
                ['name' => $subject['name']],
                ['subject_code' => $subject['code']]
            );
        }

        // ─────────────────────────────────────────
        // 2. BUCKET SUBJECTS
        // ─────────────────────────────────────────
        $buckets = [
            ['name' => 'Aesthetic (G6-9)', 'code' => 'BK001'],
            ['name' => 'Category 01 (G10-11)', 'code' => 'BK002'],
            ['name' => 'Category 02 (G10-11)', 'code' => 'BK003'],
            ['name' => 'Category 03 (G10-11)', 'code' => 'BK004'],
            // Arts Stream Buckets
            ['name' => 'Category 01 (G12-13 Arts)', 'code' => 'BK005'],
            ['name' => 'Category 02 (G12-13 Arts)', 'code' => 'BK006'],
            ['name' => 'Category 03 (G12-13 Arts)', 'code' => 'BK007'],
            // Commerce Stream Bucket
            ['name' => 'Commerce Core Electives', 'code' => 'BK008'],
        ];

        foreach ($buckets as $bucket) {
            DB::table('bucket_subjects')->updateOrInsert(
                ['name' => $bucket['name']],
                ['subject_code' => $bucket['code']]
            );
        }

        // ─────────────────────────────────────────
        // 3. LINK SUBJECTS TO BUCKETS
        // ─────────────────────────────────────────
        $bucketSubjectMap = [
            'Aesthetic (G6-9)' => ['Art', 'Music', 'Dancing', 'Drama'],
            'Category 01 (G10-11)' => ['Citizenship Education (Civics)', 'Business Studies', 'Geography', 'Accounting'],
            'Category 02 (G10-11)' => ['Art', 'Dancing', 'Music'],
            'Category 03 (G10-11)' => ['ICT', 'Home Science', 'Agricultural Science', 'Health and Physical Education'],
            
            // Arts Stream Category Mappings
            'Category 01 (G12-13 Arts)' => ['English Language', 'Sinhala'],
            'Category 02 (G12-13 Arts)' => ['History', 'Political Science', 'Communication & Media Studies', 'Geography', 'Business Studies', 'Accounting', 'Economics', 'ICT', 'Home Science'],
            'Category 03 (G12-13 Arts)' => ['Buddhist Civilization', 'Art', 'Dancing', 'Music'],
            
            // Commerce Stream Mappings (Choose 2 from this list)
            'Commerce Core Electives' => ['Economics', 'Business Studies', 'ICT', 'Business Statistics'],
        ];

        foreach ($bucketSubjectMap as $bName => $sNames) {
            $bId = $bucketId($bName);
            foreach ($sNames as $sName) {
                $sId = $subjectId($sName);
                if ($bId && $sId) {
                    DB::table('subject_has_bucket_subject')->updateOrInsert([
                        'bucket_subject_id' => $bId,
                        'subject_id' => $sId,
                    ]);
                }
            }
        }

        // ─────────────────────────────────────────
        // 4. CORE SUBJECTS PER GRADE (USING grade_id)
        // ─────────────────────────────────────────
        $grade69Core = ['Sinhala', 'Tamil', 'English Language', 'Mathematics', 'Science', 'History', 'Geography', 'Citizenship Education (Civics)', 'Religion', 'ICT', 'PTS', 'Health and Physical Education'];
        $grade1011Core = ['Sinhala', 'English Language', 'Mathematics', 'Science', 'History', 'Religion'];
        
        // Split A/L Core Subjects based on Stream
        $grade1213ArtsCore = ['General English', 'General Information Technology'];
        $grade1213CommerceCore = ['General English', 'General Information Technology', 'Accounting']; // Accounting is mandatory for Commerce

        $gradeCoreMap = [
            'Grade 6' => $grade69Core, 'Grade 7' => $grade69Core, 'Grade 8' => $grade69Core, 'Grade 9' => $grade69Core,
            'Grade 10' => $grade1011Core, 'Grade 11' => $grade1011Core,
            'Grade 12 Arts' => $grade1213ArtsCore, 'Grade 13 Arts' => $grade1213ArtsCore,
            'Grade 12 Commerce' => $grade1213CommerceCore, 'Grade 13 Commerce' => $grade1213CommerceCore,
        ];

        foreach ($gradeCoreMap as $gName => $cSubjects) {
            $gId = $gradeId($gName);
            if ($gId) {
                foreach ($cSubjects as $sName) {
                    $sId = $subjectId($sName);
                    if ($sId) {
                        DB::table('grade_has_subject')->updateOrInsert([
                            'grade_id' => $gId, 
                            'subject_id' => $sId,
                        ]);
                    }
                }
            }
        }

        $this->command->info('✅ All Subjects, Buckets, and Core Subjects assigned perfectly using grade_id.');
    }
}