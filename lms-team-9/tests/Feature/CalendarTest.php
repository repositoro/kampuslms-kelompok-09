<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_published_assignments_for_enrolled_courses_only(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $enrolledCourse = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $otherCourse = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $student->courses()->attach($enrolledCourse);
        $visibleAssignment = Assignment::factory()->create([
            'course_id' => $enrolledCourse->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
            'due_at' => '2026-10-12 10:00:00',
            'title' => 'Tugas yang terlihat',
        ]);
        Assignment::factory()->create([
            'course_id' => $enrolledCourse->id,
            'created_by' => $lecturer->id,
            'status' => 'draft',
            'due_at' => '2026-10-12 11:00:00',
            'title' => 'Tugas draf',
        ]);
        Assignment::factory()->create([
            'course_id' => $otherCourse->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
            'due_at' => '2026-10-12 12:00:00',
            'title' => 'Tugas kursus lain',
        ]);

        $this->actingAs($student)
            ->get('/calendar?month=2026-10')
            ->assertOk()
            ->assertSee('Tugas yang terlihat')
            ->assertDontSee('Tugas draf')
            ->assertDontSee('Tugas kursus lain')
            ->assertSee(route('mahasiswa.assignments.show', $visibleAssignment));
    }

    public function test_lecturer_can_filter_their_calendar_by_course(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $otherCourse = Course::factory()->create([
            'lecturer_id' => User::factory()->create(['role' => 'dosen'])->id,
        ]);
        Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'due_at' => '2026-10-12 10:00:00',
            'title' => 'Tugas kelas saya',
        ]);
        Assignment::factory()->create([
            'course_id' => $otherCourse->id,
            'created_by' => $lecturer->id,
            'due_at' => '2026-10-12 11:00:00',
            'title' => 'Tugas kelas lain',
        ]);

        $this->actingAs($lecturer)
            ->get('/calendar?month=2026-10&course_id='.$course->id)
            ->assertOk()
            ->assertSee('Oktober 2026')
            ->assertSee('Tugas kelas saya')
            ->assertDontSee('Tugas kelas lain');
    }

    public function test_calendar_does_not_allow_filtering_to_an_inaccessible_course(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create([
            'lecturer_id' => User::factory()->create(['role' => 'dosen'])->id,
        ]);

        $this->actingAs($student)
            ->get('/calendar?course_id='.$course->id)
            ->assertNotFound();
    }

    public function test_calendar_can_show_agenda_for_a_selected_day(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['lecturer_id' => $admin->id]);
        Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $admin->id,
            'due_at' => '2026-10-12 10:00:00',
            'title' => 'Agenda hari ini',
        ]);
        Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $admin->id,
            'due_at' => '2026-10-13 10:00:00',
            'title' => 'Agenda hari berikutnya',
        ]);

        $this->actingAs($admin)
            ->get('/calendar?view=day&date=2026-10-12')
            ->assertOk()
            ->assertSee('Senin, 12 Oktober 2026')
            ->assertSee('Agenda hari ini')
            ->assertDontSee('Agenda hari berikutnya')
            ->assertSee('calendar-grid--day');
    }

    public function test_calendar_rejects_an_invalid_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/calendar?month=2026-13')
            ->assertSessionHasErrors('month');
    }
}
