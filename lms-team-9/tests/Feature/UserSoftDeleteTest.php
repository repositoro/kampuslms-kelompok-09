<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_but_soft_deleted_user_cannot(): void
    {
        $active = User::factory()->create([
            'email' => 'active@example.test',
            'password' => 'valid-password',
            'role' => 'mahasiswa',
        ]);
        $deleted = User::factory()->create([
            'email' => 'deleted@example.test',
            'password' => 'valid-password',
            'role' => 'mahasiswa',
        ]);
        $deleted->delete();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'active@example.test',
            'password' => 'valid-password',
        ])->assertOk();
        $this->postJson('/api/v1/auth/login', [
            'email' => 'deleted@example.test',
            'password' => 'valid-password',
        ])->assertUnprocessable();

        $this->assertSoftDeleted('users', ['id' => $deleted->id]);
        $this->assertNotNull(User::find($active->id));
        $this->assertNull(User::find($deleted->id));
    }
}
