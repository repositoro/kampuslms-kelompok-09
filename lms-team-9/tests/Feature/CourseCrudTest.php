<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourseCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_courses_with_contract_fields_in_web_and_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lecturer = User::factory()->create(['role' => 'dosen']);

        $this->actingAs($admin)->post('/courses', [
            'code' => 'SI2514024-WEB',
            'name' => 'Pemrograman Web',
            'description' => 'Kelas web',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ])->assertRedirect();

        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/courses', [
            'code' => 'SI2514024-API',
            'name' => 'Pemrograman Web API',
            'description' => 'Kelas API',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Pemrograman Web API')
            ->assertJsonPath('data.code', 'SI2514024-API');

        $this->assertDatabaseCount('courses', 2);
    }

    public function test_non_admin_cannot_create_courses(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $anotherLecturer = User::factory()->create(['role' => 'dosen']);
        Sanctum::actingAs($lecturer);

        $this->postJson('/api/v1/courses', [
            'code' => 'DENIED',
            'name' => 'Tidak boleh',
            'sks' => 3,
            'lecturer_id' => $anotherLecturer->id,
        ])->assertForbidden();

        $this->actingAs($lecturer)->post('/courses', [
            'code' => 'DENIED-WEB',
            'name' => 'Tidak boleh',
            'sks' => 3,
            'lecturer_id' => $anotherLecturer->id,
        ])->assertForbidden();

        $this->assertSame(0, Course::count());
    }

    public function test_admin_can_update_and_delete_courses_through_the_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        Sanctum::actingAs($admin);

        $this->putJson("/api/v1/courses/{$course->id}", [
            'name' => 'Nama Baru',
        ])->assertOk()->assertJsonPath('data.name', 'Nama Baru');

        $this->deleteJson("/api/v1/courses/{$course->id}")->assertNoContent();
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_non_admin_cannot_update_or_delete_courses(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        Sanctum::actingAs($lecturer);

        $this->putJson("/api/v1/courses/{$course->id}", [
            'name' => 'Tidak boleh',
        ])->assertForbidden();

        $this->deleteJson("/api/v1/courses/{$course->id}")->assertForbidden();
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }
}
