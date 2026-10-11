<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_is_limited_after_five_attempts_per_minute(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'api-limit@example.test',
                'password' => 'incorrect',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'api-limit@example.test',
            'password' => 'incorrect',
        ])->assertStatus(429);
    }

    public function test_web_login_is_limited_after_five_attempts_per_minute(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', [
                'email' => 'web-limit@example.test',
                'password' => 'incorrect',
            ])->assertRedirect();
        }

        $this->post('/login', [
            'email' => 'web-limit@example.test',
            'password' => 'incorrect',
        ])->assertStatus(429);
    }
}
