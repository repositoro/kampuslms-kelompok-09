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
use Illuminate\Support\Str;
use Tests\TestCase;

class PaginationAndLazyLoadingTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_lists_are_paginated_and_render_without_lazy_loading(): void
    {
        $this->assertTrue(Model::preventsLazyLoading());

        $admin = User::factory()->create(['role' => 'admin']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $enrolledStudents = User::factory(15)->create(['role' => 'mahasiswa']);
        $availableStudents = User::factory(15)->create(['role' => 'mahasiswa']);
        $students = collect([$student])->concat($enrolledStudents);

        foreach ($students as $enrolledStudent) {
            $course->students()->attach($enrolledStudent->id, ['enrolled_at' => now()]);
        }

        $firstAssignment = null;
        foreach (range(1, 16) as $index) {
            Material::create([
                'course_id' => $course->id,
                'uploaded_by' => $lecturer->id,
                'title' => "Materi {$index}",
                'type' => 'link',
                'external_url' => "https://example.test/material-{$index}",
            ]);

            $assignment = Assignment::factory()->active()->create([
                'course_id' => $course->id,
                'created_by' => $lecturer->id,
            ]);
            $firstAssignment ??= $assignment;
            $submission = $this->createGradedSubmission($assignment, $student, $lecturer, $index);

            if ($index === 1) {
                foreach ($enrolledStudents as $studentIndex => $otherStudent) {
                    $this->createGradedSubmission(
                        $assignment,
                        $otherStudent,
                        $lecturer,
                        $studentIndex + 100,
                    );
                }
            }
        }

        foreach (range(1, 15) as $index) {
            User::factory()->create(['role' => 'dosen']);
            $additionalCourse = Course::factory()->create(['lecturer_id' => $lecturer->id]);
            $additionalCourse->students()->attach($student->id, ['enrolled_at' => now()]);
        }
        foreach (range(1, 16) as $index) {
            $student->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => 'performance_test',
                'data' => ['title' => "Notifikasi {$index}"],
            ]);
        }

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Berikutnya');
        $this->get('/courses')
            ->assertOk()
            ->assertSee('Berikutnya');
        $this->get('/courses/create')
            ->assertOk()
            ->assertSee('lecturers_page=2');

        $this->actingAs($lecturer)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Berikutnya');
        $this->get("/courses/{$course->id}")
            ->assertOk()
            ->assertSee('students_page=2')
            ->assertSee('available_students_page=2')
            ->assertSee('materials_page=2')
            ->assertSee('assignments_page=2');
        $this->get("/dosen/courses/{$course->id}/materials")
            ->assertOk()
            ->assertSee('page=2');
        $this->get("/dosen/courses/{$course->id}/assignments")
            ->assertOk()
            ->assertSee('page=2');
        $this->get("/dosen/assignments/{$firstAssignment->id}")
            ->assertOk()
            ->assertSee('page=2');
        $this->get("/dosen/assignments/{$firstAssignment->id}/submissions")
            ->assertOk()
            ->assertSee('page=2');
        $this->get("/dosen/courses/{$course->id}/grades")
            ->assertOk()
            ->assertSee('page=2');

        $this->actingAs($student)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Berikutnya');
        $this->get('/courses')
            ->assertOk()
            ->assertSee('Berikutnya');
        $this->get('/mahasiswa/grades')
            ->assertOk()
            ->assertSee('Berikutnya');
        $this->get('/notifications')
            ->assertOk()
            ->assertSee('page=2');

        $this->assertCount(15, $availableStudents);
        $this->assertDatabaseCount('materials', 16);
    }

    private function createGradedSubmission(
        Assignment $assignment,
        User $student,
        User $lecturer,
        int $index,
    ): Submission {
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => "submissions/{$assignment->id}/student-{$index}.pdf",
            'original_name' => "student-{$index}.pdf",
            'file_size' => 1024,
            'submitted_at' => now(),
        ]);
        Grade::create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 80,
            'graded_at' => now()->addSeconds($index),
        ]);

        return $submission;
    }
}
