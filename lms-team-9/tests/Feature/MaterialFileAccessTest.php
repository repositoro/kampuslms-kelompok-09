<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MaterialFileAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_lecturer_can_upload_pdf_and_enrolled_student_can_download_privately(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        Sanctum::actingAs($lecturer);
        $response = $this->post("/api/v1/courses/{$course->id}/materials", [
            'title' => 'Slide',
            'description' => 'Materi kelas',
            'type' => 'file',
            'file' => UploadedFile::fake()->create('slide.pdf', 20, 'application/pdf'),
        ])->assertCreated();

        $materialId = $response->json('data.id');
        $material = Material::findOrFail($materialId);
        $this->assertSame($lecturer->id, $material->uploaded_by);
        Storage::disk('local')->assertExists($material->file_path);
        Storage::disk('public')->assertMissing($material->file_path);

        Sanctum::actingAs($student);
        $this->get($response->json('data.file_url'))->assertOk();
    }

    public function test_non_enrolled_student_cannot_download_and_invalid_file_type_is_rejected(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $material = Material::create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Privat',
            'type' => 'file',
            'file_path' => 'materials/secret.pdf',
            'original_name' => 'secret.pdf',
            'file_size' => 10,
            'mime_type' => 'application/pdf',
        ]);
        Storage::disk('local')->put('materials/secret.pdf', 'private content');

        Sanctum::actingAs($student);
        $this->get("/materials/{$material->id}/download")->assertForbidden();

        Sanctum::actingAs($lecturer);
        $this->post("/api/v1/courses/{$course->id}/materials", [
            'title' => 'Unsafe file',
            'type' => 'file',
            'file' => UploadedFile::fake()->create('script.php', 10, 'text/plain'),
        ])->assertUnprocessable();
    }

    public function test_material_updates_require_a_file_when_switching_to_file_and_delete_is_authorized(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $otherLecturer = User::factory()->create(['role' => 'dosen']);
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $material = Material::create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Link',
            'type' => 'link',
            'external_url' => 'https://example.test/slides',
        ]);

        Sanctum::actingAs($lecturer);
        $this->putJson("/api/v1/materials/{$material->id}", [
            'title' => 'Link diperbarui',
            'type' => 'link',
            'external_url' => 'https://example.test/updated',
        ])->assertOk()->assertJsonPath('data.title', 'Link diperbarui');

        $this->putJson("/api/v1/materials/{$material->id}", [
            'type' => 'file',
        ])->assertUnprocessable()->assertJsonValidationErrors('file');

        Sanctum::actingAs($otherLecturer);
        $this->deleteJson("/api/v1/materials/{$material->id}")->assertForbidden();
        $this->assertDatabaseHas('materials', ['id' => $material->id]);

        Sanctum::actingAs($admin);
        $this->deleteJson("/api/v1/materials/{$material->id}")->assertNoContent();
        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
    }

    public function test_external_material_links_only_allow_http_schemes_and_hide_unsafe_legacy_links(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);

        Sanctum::actingAs($lecturer);

        $this->postJson("/api/v1/courses/{$course->id}/materials", [
            'title' => 'Unsafe link',
            'type' => 'link',
            'external_url' => 'javascript://example.com/%0Aalert(1)',
        ])->assertUnprocessable()->assertJsonValidationErrors('external_url');

        $material = Material::create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Legacy unsafe link',
            'type' => 'link',
            'external_url' => 'javascript://example.com/%0Aalert(1)',
        ]);

        $this->putJson("/api/v1/materials/{$material->id}", [
            'type' => 'link',
            'external_url' => 'javascript://example.com/%0Aalert(1)',
        ])->assertUnprocessable()->assertJsonValidationErrors('external_url');

        $this->getJson("/api/v1/materials/{$material->id}")
            ->assertOk()
            ->assertJsonPath('data.external_url', null);
        $this->get("/dosen/materials/{$material->id}")
            ->assertOk()
            ->assertDontSee('Buka tautan')
            ->assertDontSee('href="javascript:', false);
    }
}
