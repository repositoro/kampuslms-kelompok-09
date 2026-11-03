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
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubmissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_and_resubmit_privately_without_duplicate_records(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$lecturer, $student, $course, $assignment] = $this->createPublishedAssignment();
        $this->actingAs($student)
            ->get("/dosen/assignments/{$assignment->id}")
            ->assertOk()
            ->assertSee('Kumpul Tugas')
            ->assertSee('name="file"', false)
            ->assertSee('name="note"', false);
        $this->get("/courses/{$course->id}")
            ->assertOk()
            ->assertSee('Belum mengumpulkan');

        $this->post("/mahasiswa/assignments/{$assignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('first.pdf', 30, 'application/pdf'),
            'note' => 'Versi pertama',
        ])->assertRedirect(route('dosen.assignments.show', $assignment));

        $submission = Submission::where('assignment_id', $assignment->id)->firstOrFail();
        $firstPath = $submission->file_path;
        $this->assertSame($student->id, $submission->user_id);
        $this->assertFalse($submission->is_late);
        Storage::disk('local')->assertExists($firstPath);
        Storage::disk('public')->assertMissing($firstPath);

        $this->post("/mahasiswa/assignments/{$assignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('replacement.pdf', 40, 'application/pdf'),
            'note' => 'Versi kedua',
        ])->assertRedirect(route('dosen.assignments.show', $assignment));

        $submission->refresh();
        $this->assertSame(1, Submission::where('assignment_id', $assignment->id)->count());
        $this->assertSame('replacement.pdf', $submission->original_name);
        $this->assertSame('Versi kedua', $submission->note);
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($submission->file_path);
    }

    public function test_late_submission_is_recorded_when_allowed_and_rejected_when_disabled(): void
    {
        [$lecturer, $student, $course, $lateAllowed] = $this->createPublishedAssignment([
            'due_at' => now()->subMinute(),
            'allow_late' => true,
        ]);
        $this->actingAs($student)
            ->get("/dosen/assignments/{$lateAllowed->id}")
            ->assertOk()
            ->assertSee('Kumpul Tugas');
        $this->post("/mahasiswa/assignments/{$lateAllowed->id}/submissions", [
            'file' => UploadedFile::fake()->create('late.pdf', 10, 'application/pdf'),
        ])->assertRedirect();
        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $lateAllowed->id,
            'user_id' => $student->id,
            'is_late' => true,
        ]);

        $lateDisallowed = Assignment::factory()->state(['status' => 'published'])->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'due_at' => now()->subMinute(),
            'allow_late' => false,
        ]);
        $this->get("/dosen/assignments/{$lateDisallowed->id}")
            ->assertOk()
            ->assertDontSee('Kumpul Tugas')
            ->assertSee('Batas waktu pengumpulan telah lewat.');
        $this->from("/dosen/assignments/{$lateDisallowed->id}")
            ->post("/mahasiswa/assignments/{$lateDisallowed->id}/submissions", [
                'file' => UploadedFile::fake()->create('too-late.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('submissions', ['assignment_id' => $lateDisallowed->id]);
    }

    public function test_unenrolled_and_draft_submissions_are_forbidden_and_invalid_types_are_rejected(): void
    {
        [$lecturer, $student, $course, $assignment] = $this->createPublishedAssignment();
        $otherStudent = User::factory()->create(['role' => 'mahasiswa']);
        $draft = Assignment::factory()->draft()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);

        $this->actingAs($otherStudent)
            ->get("/dosen/assignments/{$assignment->id}")
            ->assertForbidden();
        $this->post("/mahasiswa/assignments/{$assignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('no-access.pdf', 10, 'application/pdf'),
        ])->assertForbidden();

        $this->actingAs($student)
            ->get("/dosen/assignments/{$draft->id}")
            ->assertForbidden();
        $this->post("/mahasiswa/assignments/{$draft->id}/submissions", [
            'file' => UploadedFile::fake()->create('draft.pdf', 10, 'application/pdf'),
        ])->assertForbidden();

        $this->from("/dosen/assignments/{$assignment->id}")
            ->post("/mahasiswa/assignments/{$assignment->id}/submissions", [
                'file' => UploadedFile::fake()->create('script.php', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('submissions', ['assignment_id' => $assignment->id]);
    }

    public function test_student_can_download_only_their_own_submission(): void
    {
        Storage::fake('local');
        [$lecturer, $student, $course, $assignment] = $this->createPublishedAssignment();
        $otherStudent = User::factory()->create(['role' => 'mahasiswa']);
        $course->students()->attach($otherStudent->id, ['enrolled_at' => now()]);
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/'.$assignment->id.'/owner.pdf',
            'original_name' => 'owner.pdf',
            'file_size' => 10,
            'submitted_at' => now(),
        ]);
        Storage::disk('local')->put($submission->file_path, 'private');

        $this->get("/submissions/{$submission->id}/download")
            ->assertRedirect(route('login'));

        $this->actingAs($otherStudent)
            ->get("/dosen/assignments/{$assignment->id}")
            ->assertOk()
            ->assertDontSee('owner.pdf');
        $this->get("/submissions/{$submission->id}/download")->assertForbidden();

        $this->actingAs($student)
            ->get("/submissions/{$submission->id}/download")
            ->assertOk();
    }

    public function test_api_submission_upload_returns_created_then_updated_and_uses_private_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [, $student, , $assignment] = $this->createPublishedAssignment();
        Sanctum::actingAs($student);

        $response = $this->post("/api/v1/assignments/{$assignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('api.pdf', 20, 'application/pdf'),
            'note' => 'API submission',
        ], ['Accept' => 'application/json'])->assertCreated()
            ->assertJsonPath('data.user_id', $student->id)
            ->assertJsonPath('data.original_name', 'api.pdf')
            ->assertJsonPath('data.is_late', false);

        $submission = Submission::findOrFail($response->json('data.id'));
        Storage::disk('local')->assertExists($submission->file_path);
        Storage::disk('public')->assertMissing($submission->file_path);

        $this->post("/api/v1/assignments/{$assignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('api-retry.pdf', 20, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('data.id', $submission->id)
            ->assertJsonPath('data.original_name', 'api-retry.pdf');
        $this->assertSame(1, Submission::where('assignment_id', $assignment->id)->count());
        $this->get($response->json('data.file_url'))->assertOk();

        $this->post("/api/v1/assignments/{$assignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('script.php', 20, 'text/plain'),
        ], ['Accept' => 'application/json'])->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_graded_submission_cannot_be_replaced(): void
    {
        [, $student, , $assignment] = $this->createPublishedAssignment();
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/graded.pdf',
            'original_name' => 'graded.pdf',
            'file_size' => 10,
            'submitted_at' => now(),
        ]);
        Grade::create([
            'submission_id' => $submission->id,
            'graded_by' => $assignment->created_by,
            'score' => 90,
            'graded_at' => now(),
        ]);

        $this->actingAs($student)->post("/mahasiswa/assignments/{$assignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('file');
        $this->assertSame('graded.pdf', $submission->fresh()->original_name);
    }

    private function createPublishedAssignment(array $attributes = []): array
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $assignment = Assignment::factory()->state(['status' => 'published'])->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            ...$attributes,
        ]);

        return [$lecturer, $student, $course, $assignment];
    }
}
