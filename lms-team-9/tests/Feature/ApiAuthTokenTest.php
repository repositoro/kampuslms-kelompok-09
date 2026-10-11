<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_a_sanitized_profile_and_logout_revokes_the_token(): void
    {
        $user = User::factory()->create([
            'email' => 'student@example.test',
            'password' => 'correct-password',
            'role' => 'mahasiswa',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'student@example.test',
            'password' => 'correct-password',
        ])->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.role', 'mahasiswa');

        $this->assertArrayNotHasKey('password', $login->json('data.user'));
        $this->assertArrayNotHasKey('remember_token', $login->json('data.user'));
        $token = $login->json('data.token');

        $this->withToken($token)->getJson('/api/v1/me')->assertOk();
        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertNotFound();
    }
}
