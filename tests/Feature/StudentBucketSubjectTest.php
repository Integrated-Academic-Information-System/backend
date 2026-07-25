<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentBucketSubjectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_newly_created_student_persists_bucket_subjects_and_appears_in_filter(): void
    {
        $grade = DB::table('grades')->where('name', 'Grade 6')->first();
        $this->assertNotNull($grade);

        $artSubject = DB::table('subjects')->where('name', 'Art')->first();
        $musicSubject = DB::table('subjects')->where('name', 'Music')->first();
        $sinhalaSubject = DB::table('subjects')->where('name', 'Sinhala')->first();

        $this->assertNotNull($artSubject);
        $this->assertNotNull($musicSubject);
        $this->assertNotNull($sinhalaSubject);

        // Create student in Grade 6 with Sinhala (Core) and Art (Bucket)
        $payload = [
            'name' => 'Test Student Art',
            'reg_no' => 'REG-TEST-001',
            'password' => 'password123',
            'grade_id' => $grade->id,
            'subject_ids' => [$sinhalaSubject->id, $artSubject->id],
            'email' => 'testart@example.com',
        ];

        $admin = \App\Models\Admin::first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin, 'admin')->postJson('/api/admin/students', $payload);
        $response->assertStatus(201);

        $studentId = $response->json('data.id');

        // 1. Verify student_has_bucket_subject has entry for Art
        $bucketAssigned = DB::table('student_has_bucket_subject')
            ->join('subject_has_bucket_subject', 'student_has_bucket_subject.subject_has_bucket_subject_id', '=', 'subject_has_bucket_subject.id')
            ->where('student_has_bucket_subject.student_id', $studentId)
            ->where('subject_has_bucket_subject.subject_id', $artSubject->id)
            ->exists();

        $this->assertTrue($bucketAssigned, 'Newly created student should have Art assigned in student_has_bucket_subject');

        // 2. Filter students by Core Subject (Sinhala)
        $coreFilterResponse = $this->getJson("/api/students?grade_id={$grade->id}&subject_id={$sinhalaSubject->id}");
        $coreFilterResponse->assertStatus(200);
        $coreStudentIds = collect($coreFilterResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($studentId, $coreStudentIds, 'Student should appear in Core Subject student list');

        // 3. Filter students by Bucket Subject (Art)
        $artFilterResponse = $this->getJson("/api/students?grade_id={$grade->id}&subject_id={$artSubject->id}");
        $artFilterResponse->assertStatus(200);
        $artStudentIds = collect($artFilterResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($studentId, $artStudentIds, 'Newly created student should appear immediately in Art (Bucket Subject) student list');

        // 4. Filter students by another Bucket Subject (Music) - should NOT contain this student
        $musicFilterResponse = $this->getJson("/api/students?grade_id={$grade->id}&subject_id={$musicSubject->id}");
        $musicFilterResponse->assertStatus(200);
        $musicStudentIds = collect($musicFilterResponse->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($studentId, $musicStudentIds, 'Student should NOT appear in Music (Bucket Subject) student list');
    }
}
