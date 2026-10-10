<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiSubmissionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_own_submission_for_an_enrolled_published_assignment(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
        ]);
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/student.pdf',
            'original_name' => 'student.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($student);

        $this->getJson("/api/v1/submissions/{$submission->id}")
            ->assertOk()
            ->assertJsonPath('data.user_id', $student->id);
    }

    public function test_student_cannot_view_another_students_submission_or_submit_to_unenrolled_or_draft_assignment(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $otherStudent = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
        ]);
        $draft = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'status' => 'draft',
        ]);
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $otherStudent->id,
            'file_path' => 'submissions/other.pdf',
            'original_name' => 'other.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($student);

        $this->getJson("/api/v1/submissions/{$submission->id}")->assertForbidden();
        $this->postJson("/api/v1/assignments/{$assignment->id}/submissions")->assertForbidden();
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $this->postJson("/api/v1/assignments/{$draft->id}/submissions")->assertForbidden();
    }
}
