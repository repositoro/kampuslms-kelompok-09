<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiShowAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrolled_student_can_view_enrolled_course_material_published_assignment_and_own_grade(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $material = Material::create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Materi',
            'type' => 'link',
            'external_url' => 'https://example.test',
        ]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
        ]);
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/test.pdf',
            'original_name' => 'test.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
        ]);
        $grade = Grade::create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 90,
            'graded_at' => now(),
        ]);

        Sanctum::actingAs($student);

        $this->getJson("/api/v1/courses/{$course->id}")->assertOk();
        $this->getJson("/api/v1/materials/{$material->id}")->assertOk();
        $this->getJson("/api/v1/assignments/{$assignment->id}")->assertOk();
        $this->getJson("/api/v1/submissions/{$submission->id}/grade")->assertOk()
            ->assertJsonPath('data.id', $grade->id);
    }

    public function test_student_cannot_view_unenrolled_course_material_assignment_or_another_students_grade(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $otherStudent = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $material = Material::create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Materi',
            'type' => 'link',
            'external_url' => 'https://example.test',
        ]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
        ]);
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $otherStudent->id,
            'file_path' => 'submissions/test.pdf',
            'original_name' => 'test.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
        ]);
        Grade::create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 90,
            'graded_at' => now(),
        ]);

        Sanctum::actingAs($student);

        $this->getJson("/api/v1/courses/{$course->id}")->assertForbidden();
        $this->getJson("/api/v1/materials/{$material->id}")->assertForbidden();
        $this->getJson("/api/v1/assignments/{$assignment->id}")->assertForbidden();
        $this->getJson("/api/v1/submissions/{$submission->id}/grade")->assertForbidden();
    }

    public function test_student_cannot_view_a_draft_assignment_even_when_enrolled(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $draft = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'status' => 'draft',
        ]);

        Sanctum::actingAs($student);

        $this->getJson("/api/v1/assignments/{$draft->id}")->assertForbidden();
    }
}
