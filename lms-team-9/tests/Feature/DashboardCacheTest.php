<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_admin_dashboard_cache_is_invalidated_when_a_user_is_created(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('<p class="stat-value">1</p>', false);

        User::factory()->create(['role' => 'mahasiswa']);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('<p class="stat-value">2</p>', false);
    }

    public function test_lecturer_dashboard_cache_is_invalidated_when_enrollment_changes(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);

        $this->actingAs($lecturer)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('0 mahasiswa');

        $this->post("/courses/{$course->id}/enrollments", ['user_id' => $student->id])
            ->assertRedirect();

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('1 mahasiswa');
    }
}
