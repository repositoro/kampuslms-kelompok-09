<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseMaterialAssignmentPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_lecturer_can_open_material_and_assignment_resource_pages(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
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
        ]);
        Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);

        $this->actingAs($lecturer);

        $this->get("/dosen/courses/{$course->id}/materials")->assertOk();
        $this->get("/dosen/courses/{$course->id}/materials/create")->assertOk();
        $this->get("/dosen/materials/{$material->id}")->assertOk();
        $this->get("/dosen/materials/{$material->id}/edit")->assertOk();
        $this->get("/dosen/courses/{$course->id}/assignments")
            ->assertOk()
            ->assertSee('Hapus');
        $this->get("/dosen/courses/{$course->id}/assignments/create")->assertOk();
        $this->get("/dosen/assignments/{$assignment->id}")->assertOk();
        $this->get("/dosen/assignments/{$assignment->id}/edit")->assertOk();
    }

    public function test_non_owner_cannot_open_material_or_assignment_management_pages(): void
    {
        $owner = User::factory()->create(['role' => 'dosen']);
        $other = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $owner->id]);
        $material = Material::create([
            'course_id' => $course->id,
            'uploaded_by' => $owner->id,
            'title' => 'Materi',
            'type' => 'link',
            'external_url' => 'https://example.test',
        ]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $owner->id,
        ]);

        $this->actingAs($other);

        $this->get("/dosen/courses/{$course->id}/materials")->assertForbidden();
        $this->get("/dosen/materials/{$material->id}/edit")->assertForbidden();
        $this->get("/dosen/courses/{$course->id}/assignments")->assertForbidden();
        $this->get("/dosen/assignments/{$assignment->id}/edit")->assertForbidden();
    }

    public function test_student_cannot_open_a_course_they_are_not_enrolled_in(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);

        $this->actingAs($student)->get("/courses/{$course->id}")->assertForbidden();
    }
}
