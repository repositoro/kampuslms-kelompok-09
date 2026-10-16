<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_loads_upcoming_assignment_course_names(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $course = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'name' => 'Pemrograman Web',
        ]);

        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        // Catatan: Jika active() belum didefinisikan di AssignmentFactory, hapus ->active()
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Tugas Mendatang',
        ]);

        // 1. Tes Tampilan Mahasiswa
        $this->actingAs($student)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Tugas Mendatang')
            ->assertSee('Pemrograman Web');

        // 2. Tes Tampilan Dosen (Switch login ke Dosen)
        $this->actingAs($lecturer)
            ->get(route('dosen.assignments.show', $assignment))
            ->assertOk()
            ->assertSee('Pengumpulan mahasiswa') // Pastikan teks ini SAMA PERSIS dengan Blade dosen
            ->assertDontSee('Kumpul Tugas');
    }
}