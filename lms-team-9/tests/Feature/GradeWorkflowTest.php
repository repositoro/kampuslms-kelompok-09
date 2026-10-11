<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GradeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_lecturer_can_grade_from_the_assignment_page(): void
    {
        [$lecturer, $student, $course, $assignment, $submission] = $this->createSubmission();

        $this->actingAs($lecturer)
            ->get("/dosen/assignments/{$assignment->id}")
            ->assertOk()
            ->assertSee('Pengumpulan')
            ->assertSee($student->name)
            ->assertSee('Beri Nilai')
            ->assertSee('name="score"', false)
            ->assertSee('name="feedback"', false);

        $this->put("/dosen/submissions/{$submission->id}/grade", [
            'score' => 80,
            'feedback' => 'Kerja bagus.',
        ])->assertRedirect(route('dosen.assignments.show', $assignment));

        $this->assertDatabaseHas('grades', [
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 80,
            'feedback' => 'Kerja bagus.',
        ]);
        $this->get("/dosen/assignments/{$assignment->id}")
            ->assertOk()
            ->assertSee('Ubah Nilai');
    }

    public function test_api_put_upserts_grade_and_returns_created_then_ok(): void
    {
        [$lecturer, , , $assignment, $submission] = $this->createSubmission(['max_score' => 50]);
        Sanctum::actingAs($lecturer);

        $this->putJson("/api/v1/submissions/{$submission->id}/grade", [
            'score' => 40,
            'feedback' => 'Pertama',
        ])->assertCreated()
            ->assertJsonPath('data.submission_id', $submission->id)
            ->assertJsonPath('data.graded_by', $lecturer->id);

        $this->putJson("/api/v1/submissions/{$submission->id}/grade", [
            'score' => 45,
            'feedback' => 'Diperbarui',
        ])->assertOk()
            ->assertJsonPath('data.score', '45.00')
            ->assertJsonPath('data.feedback', 'Diperbarui');

        $this->assertSame(1, Grade::where('submission_id', $submission->id)->count());
        $this->assertDatabaseHas('grades', [
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 45,
            'feedback' => 'Diperbarui',
        ]);
    }

    public function test_score_must_be_between_zero_and_assignment_max_score(): void
    {
        [$lecturer, , , , $submission] = $this->createSubmission(['max_score' => 50]);
        Sanctum::actingAs($lecturer);

        $this->putJson("/api/v1/submissions/{$submission->id}/grade", ['score' => 51])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('score');
        $this->putJson("/api/v1/submissions/{$submission->id}/grade", ['score' => -1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('score');
        $this->assertDatabaseMissing('grades', ['submission_id' => $submission->id]);
    }

    public function test_only_the_course_lecturer_can_grade_and_students_only_see_their_own_grade(): void
    {
        [$lecturer, $student, , $assignment, $submission] = $this->createSubmission();
        $otherLecturer = User::factory()->create(['role' => 'dosen']);
        $otherStudent = User::factory()->create(['role' => 'mahasiswa']);
        $admin = User::factory()->create(['role' => 'admin']);
        $grade = Grade::create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 80,
            'feedback' => 'Feedback privat.',
            'graded_at' => now(),
        ]);

        foreach ([$otherLecturer, $student, $admin] as $unauthorizedUser) {
            Sanctum::actingAs($unauthorizedUser);
            $this->putJson("/api/v1/submissions/{$submission->id}/grade", ['score' => 90])
                ->assertForbidden();
        }

        Sanctum::actingAs($student);
        $this->getJson("/api/v1/submissions/{$submission->id}/grade")
            ->assertOk()
            ->assertJsonPath('data.feedback', 'Feedback privat.');

        Sanctum::actingAs($otherStudent);
        $this->getJson("/api/v1/submissions/{$submission->id}/grade")->assertForbidden();
        $this->actingAs($otherStudent)
            ->get("/dosen/assignments/{$assignment->id}")
            ->assertForbidden();

        $this->actingAs($admin)
            ->get("/dosen/assignments/{$assignment->id}")
            ->assertOk()
            ->assertDontSee('Beri Nilai')
            ->assertSee($student->name);
        $this->assertDatabaseHas('grades', ['id' => $grade->id]);
    }

    public function test_grade_summary_pages_are_scoped_to_course_owner_and_student(): void
    {
        [$lecturer, $student, $course, , $submission] = $this->createSubmission();
        $otherLecturer = User::factory()->create(['role' => 'dosen']);
        $otherStudent = User::factory()->create(['role' => 'mahasiswa']);
        Grade::create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 75,
            'feedback' => 'Tuntas',
            'graded_at' => now(),
        ]);

        $this->actingAs($lecturer)
            ->get("/dosen/courses/{$course->id}/grades")
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Tuntas');
        $this->actingAs($otherLecturer)
            ->get("/dosen/courses/{$course->id}/grades")
            ->assertForbidden();

        $this->actingAs($student)->get('/mahasiswa/grades')->assertOk()->assertSee('Tuntas');
        $this->actingAs($otherStudent)->get('/mahasiswa/grades')->assertOk()->assertDontSee('Tuntas');
    }

    private function createSubmission(array $assignmentAttributes = []): array
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            ...$assignmentAttributes,
        ]);
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/'.$assignment->id.'/student.pdf',
            'original_name' => 'student.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
        ]);

        return [$lecturer, $student, $course, $assignment, $submission];
    }
}
