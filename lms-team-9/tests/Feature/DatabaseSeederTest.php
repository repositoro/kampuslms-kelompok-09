<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_data_meets_required_counts_and_assignment_states(): void
    {
        $this->seed();

        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame(3, User::where('role', 'dosen')->count());
        $this->assertSame(30, User::where('role', 'mahasiswa')->count());
        $this->assertSame(5, Course::count());
        $this->assertSame(15, Assignment::count());
        $this->assertGreaterThanOrEqual(100, Submission::count());
        $this->assertSame(90, Grade::count());

        $this->assertSame(5, Course::has('students', '>=', 15)->count());
        $this->assertSame(5, Assignment::where('status', 'published')
            ->where('due_at', '<', now())->count());
        $this->assertSame(5, Assignment::where('status', 'published')
            ->where('due_at', '>', now())->count());
        $this->assertSame(5, Assignment::where('status', 'draft')->count());
        $this->assertDatabaseHas('users', ['email' => 'admin@kampuslms.test']);
        $this->assertDatabaseHas('users', ['email' => 'dosen@kampuslms.test']);
        $this->assertDatabaseHas('users', ['email' => 'mahasiswa@kampuslms.test']);
    }
}
