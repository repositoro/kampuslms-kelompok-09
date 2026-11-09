<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceBudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_pages_stay_within_their_query_budget(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $material = Material::create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Materi performa',
            'type' => 'link',
            'external_url' => 'https://example.test/material',
        ]);
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/performance.pdf',
            'original_name' => 'performance.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
        ]);
        Grade::create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 80,
            'graded_at' => now(),
        ]);

        $recordQueries = false;
        $queryCount = 0;
        DB::listen(function () use (&$recordQueries, &$queryCount): void {
            if ($recordQueries) {
                $queryCount++;
            }
        });

        $measure = function (User $user, string $url) use (&$recordQueries, &$queryCount): int {
            $queryCount = 0;
            $recordQueries = true;
            $this->actingAs($user)->get($url)->assertOk();
            $recordQueries = false;

            return $queryCount;
        };

        $counts = [
            'dashboard_admin' => $measure($admin, '/dashboard'),
            'users_index' => $measure($admin, '/admin/users'),
            'courses_index_admin' => $measure($admin, '/courses'),
            'course_show_lecturer' => $measure($lecturer, "/courses/{$course->id}"),
            'dashboard_lecturer' => $measure($lecturer, '/dashboard'),
            'materials_index' => $measure($lecturer, "/dosen/courses/{$course->id}/materials"),
            'assignments_index' => $measure($lecturer, "/dosen/courses/{$course->id}/assignments"),
            'assignment_show' => $measure($lecturer, "/dosen/assignments/{$assignment->id}"),
            'submissions_index' => $measure($lecturer, "/dosen/assignments/{$assignment->id}/submissions"),
            'grade_summary_course' => $measure($lecturer, "/dosen/courses/{$course->id}/grades"),
            'dashboard_student' => $measure($student, '/dashboard'),
            'courses_index_student' => $measure($student, '/courses'),
            'grade_summary_student' => $measure($student, '/mahasiswa/grades'),
            'notifications_index' => $measure($student, '/notifications'),
        ];
        $beforeOptimization = [
            'dashboard_admin' => 16,
            'users_index' => 21,
            'courses_index_admin' => 13,
            'course_show_lecturer' => 26,
            'dashboard_lecturer' => 12,
            'materials_index' => 17,
            'assignments_index' => 17,
            'assignment_show' => 26,
            'submissions_index' => 17,
            'grade_summary_course' => 17,
            'dashboard_student' => 16,
            'courses_index_student' => 13,
            'grade_summary_student' => 15,
            'notifications_index' => 11,
        ];

        fwrite(STDERR, 'PERFORMANCE_AFTER '.json_encode($counts).PHP_EOL);
        $this->assertTrue(Model::preventsLazyLoading());
        foreach ($counts as $page => $queryCount) {
            $this->assertLessThan(
                $beforeOptimization[$page],
                $queryCount,
                "Expected fewer queries for {$page} than before optimization.",
            );
        }
        $this->assertLessThan(15, $counts['grade_summary_course']);
    }

    public function test_course_grade_summary_stays_below_fifteen_queries_with_many_grades(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);

        foreach (range(1, 20) as $index) {
            $assignment = Assignment::factory()->active()->create([
                'course_id' => $course->id,
                'created_by' => $lecturer->id,
            ]);
            $submission = Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'file_path' => "submissions/performance-{$index}.pdf",
                'original_name' => "performance-{$index}.pdf",
                'file_size' => 1024,
                'submitted_at' => now(),
            ]);
            Grade::create([
                'submission_id' => $submission->id,
                'graded_by' => $lecturer->id,
                'score' => 80,
                'graded_at' => now()->addSeconds($index),
            ]);
        }

        $recordQueries = false;
        $queryCount = 0;
        DB::listen(function () use (&$recordQueries, &$queryCount): void {
            if ($recordQueries) {
                $queryCount++;
            }
        });

        $recordQueries = true;
        $this->actingAs($lecturer)
            ->get("/dosen/courses/{$course->id}/grades")
            ->assertOk()
            ->assertSee('Berikutnya');
        $recordQueries = false;

        fwrite(STDERR, "PERFORMANCE_GRADE_SUMMARY_20_ROWS {$queryCount}".PHP_EOL);
        $this->assertLessThan(15, $queryCount);
    }

    public function test_upcoming_assignment_query_uses_its_composite_index(): void
    {
        $plan = DB::select(
            'EXPLAIN QUERY PLAN SELECT * FROM assignments
             WHERE course_id = ? AND status = ? AND due_at >= ?
             ORDER BY due_at ASC',
            [1, 'published', now()->toDateTimeString()],
        );
        $details = collect($plan)->pluck('detail')->implode("\n");

        fwrite(STDERR, "EXPLAIN_ASSIGNMENT_QUERY_PLAN {$details}".PHP_EOL);
        $this->assertStringContainsString('assignments_course_status_due_index', $details);
    }
}
