<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssignmentCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_lecturer_can_create_and_update_assignment_using_schema_fields(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        Sanctum::actingAs($lecturer);

        $response = $this->postJson("/api/v1/courses/{$course->id}/assignments", [
            'title' => 'Tugas API',
            'instructions' => 'Kerjakan tugas.',
            'due_at' => now()->addDays(2)->toISOString(),
            'max_score' => 50,
            'allow_late' => false,
            'status' => 'published',
            'created_by' => 999,
        ])->assertCreated()
            ->assertJsonPath('data.instructions', 'Kerjakan tugas.')
            ->assertJsonPath('data.status', 'published');

        $assignmentId = $response->json('data.id');
        $this->assertDatabaseHas('assignments', [
            'id' => $assignmentId,
            'created_by' => $lecturer->id,
            'max_score' => 50,
            'allow_late' => false,
        ]);

        $this->putJson("/api/v1/assignments/{$assignmentId}", [
            'instructions' => 'Instruksi diperbarui.',
            'due_at' => now()->addDays(4)->toISOString(),
        ])->assertOk()
            ->assertJsonPath('data.instructions', 'Instruksi diperbarui.');

        $this->deleteJson("/api/v1/assignments/{$assignmentId}")->assertNoContent();
        $this->assertDatabaseMissing('assignments', ['id' => $assignmentId]);
    }

    public function test_other_lecturer_cannot_create_or_update_course_assignments(): void
    {
        $owner = User::factory()->create(['role' => 'dosen']);
        $other = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $owner->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $owner->id,
        ]);
        Sanctum::actingAs($other);

        $this->postJson("/api/v1/courses/{$course->id}/assignments", [
            'title' => 'Tugas',
            'instructions' => 'Tidak boleh.',
            'due_at' => now()->addDay()->toISOString(),
            'status' => 'draft',
        ])->assertForbidden();
        $this->putJson("/api/v1/assignments/{$assignment->id}", [
            'title' => 'Diubah tanpa izin',
        ])->assertForbidden();
        $this->deleteJson("/api/v1/assignments/{$assignment->id}")->assertForbidden();
        $this->assertDatabaseHas('assignments', ['id' => $assignment->id]);
    }
}
