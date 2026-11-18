<?php

namespace Tests\Feature;

use App\Models\AcademicEvent;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicCalendarFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_academic_event_visible_on_the_calendar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.academic-events.store'), [
                'title' => 'Orientasi Mahasiswa',
                'description' => 'Acara orientasi kampus',
                'location' => 'Aula utama',
                'start_at' => '2026-10-12T09:00',
                'end_at' => '2026-10-12T11:00',
            ])
            ->assertRedirect(route('calendar.index', ['month' => '2026-10']))
            ->assertSessionHas('success');

        $event = AcademicEvent::query()->firstOrFail();
        $this->assertSame($admin->id, $event->created_by);
        $this->assertSame('Aula utama', $event->location);

        $this->actingAs(User::factory()->create(['role' => 'mahasiswa']))
            ->get('/calendar?month=2026-10')
            ->assertOk()
            ->assertSee('Orientasi Mahasiswa')
            ->assertSee('Aula utama');
    }

    public function test_non_admin_cannot_create_academic_events(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);

        $this->actingAs($lecturer)
            ->get(route('admin.academic-events.create'))
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->post(route('admin.academic-events.store'), [
                'title' => 'Acara tidak diizinkan',
                'start_at' => '2026-10-12T09:00',
                'end_at' => '2026-10-12T11:00',
            ])
            ->assertForbidden();
    }

    public function test_academic_event_requires_end_time_after_start_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.academic-events.create'))
            ->post(route('admin.academic-events.store'), [
                'title' => 'Rentang waktu tidak valid',
                'start_at' => '2026-10-12T11:00',
                'end_at' => '2026-10-12T09:00',
            ])
            ->assertRedirect(route('admin.academic-events.create'))
            ->assertSessionHasErrors('end_at');
    }

    public function test_ics_export_includes_all_accessible_assignments_and_global_events(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $enrolledCourse = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $otherCourse = Course::factory()->create([
            'lecturer_id' => User::factory()->create(['role' => 'dosen'])->id,
        ]);
        $student->courses()->attach($enrolledCourse);
        Assignment::factory()->create([
            'course_id' => $enrolledCourse->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
            'due_at' => '2026-10-12 10:00:00',
            'title' => 'Tugas, pertama',
        ]);
        Assignment::factory()->create([
            'course_id' => $enrolledCourse->id,
            'created_by' => $lecturer->id,
            'status' => 'draft',
            'due_at' => '2026-10-13 10:00:00',
            'title' => 'Tugas draf rahasia',
        ]);
        Assignment::factory()->create([
            'course_id' => $otherCourse->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
            'due_at' => '2026-10-14 10:00:00',
            'title' => 'Tugas kursus lain',
        ]);
        AcademicEvent::create([
            'created_by' => $lecturer->id,
            'title' => 'Ujian Tengah Semester',
            'description' => 'Bawa kartu mahasiswa',
            'location' => 'Ruang A, Gedung 1',
            'start_at' => '2026-10-20 08:00:00',
            'end_at' => '2026-10-20 10:00:00',
        ]);

        $response = $this->actingAs($student)
            ->get(route('calendar.export'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="kampuslms-kalender.ics"');

        $calendar = $response->getContent();
        $this->assertStringContainsString("SUMMARY:Tenggat tugas: Tugas\\, pertama\r\n", $calendar);
        $this->assertStringContainsString("DTSTART:20261012T100000Z\r\n", $calendar);
        $this->assertStringContainsString("SUMMARY:Ujian Tengah Semester\r\n", $calendar);
        $this->assertStringContainsString("LOCATION:Ruang A\\, Gedung 1\r\n", $calendar);
        $this->assertStringNotContainsString('Tugas draf rahasia', $calendar);
        $this->assertStringNotContainsString('Tugas kursus lain', $calendar);
        $this->assertStringContainsString("END:VCALENDAR\r\n", $calendar);
    }

    public function test_ics_export_folds_long_utf8_lines(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['lecturer_id' => $admin->id]);
        $longTitle = str_repeat('Kalender akademik ', 8);
        Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $admin->id,
            'title' => $longTitle,
            'due_at' => '2026-10-12 10:00:00',
        ]);

        $calendar = $this->actingAs($admin)
            ->get(route('calendar.export'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString("\r\n ", $calendar);
        foreach (explode("\r\n", $calendar) as $line) {
            $this->assertLessThanOrEqual(75, strlen($line));
        }
    }
}
