<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiErrorFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_errors_use_the_api_contract_format_in_indonesian(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Data yang diberikan tidak valid.')
            ->assertJsonStructure(['message', 'errors' => ['email', 'password']]);
    }

    public function test_authorization_errors_use_the_api_contract_message(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        Sanctum::actingAs($student);

        $this->getJson("/api/v1/courses/{$course->id}")
            ->assertForbidden()
            ->assertExactJson(['message' => 'Anda tidak memiliki akses ke sumber daya ini.']);
    }
}
