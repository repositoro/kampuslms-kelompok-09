<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardViewByRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_only_for_admin_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Admin')
            ->assertSee('Total pengguna')
            ->assertDontSee('Dashboard Dosen')
            ->assertDontSee('Dashboard Mahasiswa');
    }

    public function test_lecturer_dashboard_renders_only_for_lecturer_role(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);

        $this->actingAs($lecturer)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Dosen')
            ->assertSee('Pengumpulan belum dinilai')
            ->assertDontSee('Dashboard Admin')
            ->assertDontSee('Dashboard Mahasiswa');
    }

    public function test_student_dashboard_renders_only_for_student_role(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($student)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Mahasiswa')
            ->assertSee('Tugas mendekati tenggat')
            ->assertDontSee('Dashboard Admin')
            ->assertDontSee('Dashboard Dosen');
    }
}
