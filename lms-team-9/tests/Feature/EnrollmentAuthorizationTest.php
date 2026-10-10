<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_or_course_lecturer_can_enroll_a_student_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $secondStudent = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);

        $this->actingAs($admin)->get("/courses/{$course->id}")
            ->assertOk()
            ->assertSee('Peserta')
            ->assertSee('Daftarkan mahasiswa')
            ->assertSee("value=\"{$student->id}\"", false);

        $this->from("/courses/{$course->id}")->actingAs($admin)->post(
            "/courses/{$course->id}/enrollments",
            ['user_id' => $student->id],
        )->assertRedirect();

        $this->assertDatabaseHas('course_user', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'enrolled_at' => now()->toDateTimeString(),
        ]);

        $this->actingAs($lecturer)->get("/courses/{$course->id}")
            ->assertOk()
            ->assertSee('Peserta')
            ->assertSee('Daftarkan mahasiswa');

        $this->actingAs($admin)->get("/courses/{$course->id}")
            ->assertOk()
            ->assertSee($student->name)
            ->assertDontSee("value=\"{$student->id}\"", false);

        $this->from("/courses/{$course->id}")->actingAs($lecturer)->post(
            "/courses/{$course->id}/enrollments",
            ['user_id' => $secondStudent->id],
        )->assertRedirect();
        $this->assertDatabaseHas('course_user', [
            'course_id' => $course->id,
            'user_id' => $secondStudent->id,
        ]);

        $this->from("/courses/{$course->id}")->actingAs($lecturer)->post(
            "/courses/{$course->id}/enrollments",
            ['user_id' => $student->id],
        )->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('course_user', 2);

        $this->from("/courses/{$course->id}")->actingAs($admin)->delete(
            "/courses/{$course->id}/enrollments/{$student->id}",
        )->assertRedirect();
        $this->assertDatabaseMissing('course_user', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_non_owner_cannot_enroll_and_non_student_role_is_rejected(): void
    {
        $owner = User::factory()->create(['role' => 'dosen']);
        $otherLecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $owner->id]);

        $this->actingAs($otherLecturer)->post("/courses/{$course->id}/enrollments", [
            'user_id' => $student->id,
        ])->assertForbidden();

        $this->actingAs($owner)->post("/courses/{$course->id}/enrollments", [
            'user_id' => $otherLecturer->id,
        ])->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('course_user', 0);

        $this->actingAs($student)->get("/courses/{$course->id}")->assertForbidden();
        $this->actingAs($student)->post("/courses/{$course->id}/enrollments", [
            'user_id' => $student->id,
        ])->assertForbidden();
    }
}
