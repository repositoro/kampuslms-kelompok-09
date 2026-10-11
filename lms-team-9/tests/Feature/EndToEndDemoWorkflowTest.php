<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EndToEndDemoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_admin_enrolls_student_then_submission_is_graded_and_deleted(): void
    {
        Storage::fake('local');
        $this->seed();

        $admin = User::where('email', 'admin@kampuslms.test')->firstOrFail();
        $lecturer = User::where('email', 'dosen@kampuslms.test')->firstOrFail();
        $student = User::where('email', 'mahasiswa@kampuslms.test')->firstOrFail();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);

        $this->actingAs($admin)->post("/courses/{$course->id}/enrollments", [
            'user_id' => $student->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('course_user', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);

        $this->actingAs($lecturer)->post("/dosen/courses/{$course->id}/assignments", [
            'title' => 'Demo E2E',
            'instructions' => 'Unggah file dan tunggu penilaian.',
            'due_at' => now()->addDay()->toDateTimeString(),
            'max_score' => 100,
            'allow_late' => true,
            'status' => 'published',
        ])->assertRedirect();
        $assignment = Assignment::where('course_id', $course->id)
            ->where('title', 'Demo E2E')
            ->firstOrFail();

        $this->actingAs($student)
            ->get("/dosen/assignments/{$assignment->id}")
            ->assertOk()
            ->assertSee('Kumpul Tugas');
        $this->post("/mahasiswa/assignments/{$assignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('demo.pdf', 20, 'application/pdf'),
            'note' => 'Hasil demo',
        ])->assertRedirect();
        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $student->id)
            ->firstOrFail();
        Storage::disk('local')->assertExists($submission->file_path);
        $this->get("/courses/{$course->id}")
            ->assertOk()
            ->assertSee('Terkumpul tepat waktu');
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Terkumpul tepat waktu');

        $this->actingAs($lecturer)->put("/dosen/submissions/{$submission->id}/grade", [
            'score' => 85,
            'feedback' => 'Baik.',
        ])->assertRedirect();
        $grade = Grade::where('submission_id', $submission->id)->firstOrFail();

        $this->actingAs($student)
            ->get("/dosen/assignments/{$assignment->id}")
            ->assertOk()
            ->assertSee('85.00')
            ->assertSee('Baik.');

        $this->actingAs($lecturer)
            ->delete("/dosen/assignments/{$assignment->id}")
            ->assertRedirect(route('courses.show', $course));
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
        Storage::disk('local')->assertMissing($submission->file_path);
    }
}
