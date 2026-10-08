<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SecurityAndAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_ke_admin_403()
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $response = $this->actingAs($mahasiswa)->get('/admin/courses');

        $response->assertStatus(403);
    }

    public function test_dosen_b_ke_material_dosen_a_403()
    {
        $dosenA = User::factory()->create(['role' => 'dosen']);
        $dosenB = User::factory()->create(['role' => 'dosen']);

        $courseA = Course::factory()->create(['lecturer_id' => $dosenA->id]);
        $materialA = Material::create(['course_id' => $courseA->id, 'title' => 'Title', 'content' => 'Content', 'uploaded_by' => $dosenA->id, 'type' => 'file', 'file_path' => 'dummy']);

        $response = $this->actingAs($dosenB)->get(route('dosen.courses.materials.show', [$courseA, $materialA]));

        $response->assertStatus(403);
    }

    public function test_mahasiswa_b_ke_submission_mahasiswa_a_403()
    {
        $mahasiswaA = User::factory()->create(['role' => 'mahasiswa']);
        $mahasiswaB = User::factory()->create(['role' => 'mahasiswa']);

        $dosen = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $assignment = Assignment::create(['course_id' => $course->id, 'title' => 'Tugas', 'description' => 'Desc', 'due_at' => now()->addDays(2), 'max_score' => 100, 'created_by' => $dosen->id, 'instructions' => 'inst']);

        // Enroll mahasiswa A dan B
        $mahasiswaA->courses()->attach($course->id);
        $mahasiswaB->courses()->attach($course->id);

        $submissionA = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $mahasiswaA->id,
            'file_path' => 'dummy.pdf',
            'original_name' => 'dummy.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
            'is_late' => false,
        ]);

        $response = $this->actingAs($mahasiswaB)->get(route('mahasiswa.assignments.submissions.show', [$submissionA]));

        $response->assertStatus(403);
    }

    public function test_parent_salah_di_nested_route_404()
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $course1 = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $course2 = Course::factory()->create(['lecturer_id' => $dosen->id]);

        $material = Material::create(['course_id' => $course1->id, 'title' => 'Title', 'content' => 'Content', 'uploaded_by' => $dosen->id, 'type' => 'file', 'file_path' => 'dummy']);

        // Coba akses material dari course1 dengan prefix course2
        $response = $this->actingAs($dosen)->get("/dosen/courses/{$course2->id}/materials/{$material->id}");

        $response->assertStatus(404);
    }

    public function test_halaman_403_tidak_memuat_nama_id_user_lain()
    {
        $dosenA = User::factory()->create(['role' => 'dosen', 'name' => 'Rahasia Dosen A']);
        $dosenB = User::factory()->create(['role' => 'dosen', 'name' => 'Dosen Pengakses']);

        $courseA = Course::factory()->create(['lecturer_id' => $dosenA->id]);
        $materialA = Material::create(['course_id' => $courseA->id, 'title' => 'Title', 'content' => 'Content', 'uploaded_by' => $dosenA->id, 'type' => 'file', 'file_path' => 'dummy']);

        $response = $this->actingAs($dosenB)->get(route('dosen.courses.materials.show', [$courseA, $materialA]));

        $response->assertStatus(403);
        
        $response->assertSee('403');
        $response->assertSee('Akses Ditolak');
        $response->assertDontSee($dosenA->name);
        $response->assertDontSee((string)$dosenA->id);
    }
}
