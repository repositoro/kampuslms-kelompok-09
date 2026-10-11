<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContentDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_owner_and_admin_see_material_delete_forms_and_owner_removes_private_file(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Tugas setelah penghapusan materi',
        ]);
        Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);
        $material = Material::create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'File privat',
            'type' => 'file',
            'file_path' => 'materials/lesson.pdf',
            'original_name' => 'lesson.pdf',
            'file_size' => 10,
            'mime_type' => 'application/pdf',
        ]);
        Storage::disk('local')->put($material->file_path, 'private');

        $this->actingAs($lecturer)
            ->get("/dosen/courses/{$course->id}/materials")
            ->assertOk()
            ->assertSee('Hapus')
            ->assertSee('name="_method" value="DELETE"', false)
            ->assertSee('name="_token"', false);
        $this->actingAs($admin)
            ->get("/dosen/materials/{$material->id}")
            ->assertOk()
            ->assertSee('Hapus Materi');

        $this->actingAs($lecturer)
            ->followingRedirects()
            ->delete("/dosen/materials/{$material->id}")
            ->assertOk()
            ->assertSee('Tugas setelah penghapusan materi');
        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
        Storage::disk('local')->assertMissing('materials/lesson.pdf');
    }

    public function test_assignment_delete_confirmation_reports_cascades_and_owner_removes_submission_files(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => "submissions/{$assignment->id}/answer.pdf",
            'original_name' => 'answer.pdf',
            'file_size' => 10,
            'submitted_at' => now(),
        ]);
        $grade = Grade::create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 90,
            'graded_at' => now(),
        ]);
        Storage::disk('local')->put($submission->file_path, 'private submission');

        $this->actingAs($lecturer)
            ->get("/dosen/assignments/{$assignment->id}")
            ->assertOk()
            ->assertSee('Hapus Tugas')
            ->assertSee('1 pengumpulan dan 1 nilai akan ikut terhapus.')
            ->assertSee('name="_method" value="DELETE"', false);

        Sanctum::actingAs($lecturer);
        $this->deleteJson("/api/v1/assignments/{$assignment->id}")->assertNoContent();
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
        Storage::disk('local')->assertMissing($submission->file_path);
    }

    public function test_admin_and_owner_can_delete_assignments_but_other_users_cannot(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $otherLecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);

        $this->actingAs($student)
            ->get("/dosen/assignments/{$assignment->id}")
            ->assertOk()
            ->assertDontSee('Hapus Tugas');
        $this->actingAs($otherLecturer)
            ->delete("/dosen/assignments/{$assignment->id}")
            ->assertForbidden();

        foreach ([$otherLecturer, $student] as $user) {
            Sanctum::actingAs($user);
            $this->deleteJson("/api/v1/assignments/{$assignment->id}")->assertForbidden();
        }
        $this->assertDatabaseHas('assignments', ['id' => $assignment->id]);

        Sanctum::actingAs($admin);
        $this->deleteJson("/api/v1/assignments/{$assignment->id}")->assertNoContent();
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
    }
}
