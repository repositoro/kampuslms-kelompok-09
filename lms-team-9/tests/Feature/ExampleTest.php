<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_web_login_opens_the_authenticated_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin-login@example.test',
            'password' => 'correct-password',
            'role' => 'admin',
        ]);

        $this->post('/login', [
            'email' => 'admin-login@example.test',
            'password' => 'correct-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk();
    }

    public function test_invalid_web_login_is_rejected(): void
    {
        User::factory()->create([
            'email' => 'admin-login@example.test',
            'password' => 'correct-password',
            'role' => 'admin',
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'admin-login@example.test',
            'password' => 'wrong-password',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
