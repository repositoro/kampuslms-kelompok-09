<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiCollectionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_collections_are_limited_to_enrolled_courses_and_published_assignments(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $enrolledCourse = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $otherCourse = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $enrolledCourse->students()->attach($student->id, ['enrolled_at' => now()]);
        Material::create([
            'course_id' => $enrolledCourse->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Materi peserta',
            'type' => 'link',
            'external_url' => 'https://example.test',
        ]);
        $published = Assignment::factory()->create([
            'course_id' => $enrolledCourse->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
        ]);
        Assignment::factory()->create([
            'course_id' => $enrolledCourse->id,
            'created_by' => $lecturer->id,
            'status' => 'draft',
        ]);
        Assignment::factory()->create([
            'course_id' => $otherCourse->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
        ]);

        Sanctum::actingAs($student);

        $this->getJson('/api/v1/courses')
            ->assertOk()
            ->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/courses/{$enrolledCourse->id}/materials")
            ->assertOk()
            ->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/courses/{$enrolledCourse->id}/assignments")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $published->id);
        $this->getJson("/api/v1/courses/{$otherCourse->id}/materials")->assertForbidden();
        $this->getJson("/api/v1/courses/{$otherCourse->id}/assignments")->assertForbidden();
    }

    public function test_only_assignment_owner_or_admin_can_list_submissions(): void
    {
        $owner = User::factory()->create(['role' => 'dosen']);
        $otherLecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $owner->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $owner->id,
            'status' => 'published',
        ]);
        Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/test.pdf',
            'original_name' => 'test.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($owner);
        $this->getJson("/api/v1/assignments/{$assignment->id}/submissions")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        Sanctum::actingAs($otherLecturer);
        $this->getJson("/api/v1/assignments/{$assignment->id}/submissions")->assertForbidden();

        Sanctum::actingAs($student);
        $this->getJson("/api/v1/assignments/{$assignment->id}/submissions")->assertForbidden();
    }
}
