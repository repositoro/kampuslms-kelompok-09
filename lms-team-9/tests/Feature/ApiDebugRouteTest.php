<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiDebugRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_raw_user_debug_endpoint_is_removed_for_authenticated_users(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'mahasiswa']));

        $this->getJson('/api/v1/debug/raw-users')->assertNotFound();
        $this->getJson('/api/v1/me')->assertOk();
    }
}
