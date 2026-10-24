<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_absolute_notification_url_redirects_on_the_current_host(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\\Notifications\\NewAssignmentNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'url' => 'http://localhost:8000/mahasiswa/assignments/19',
            ], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        URL::forceRootUrl('http://127.0.0.1:8000');

        $this->actingAs($user)
            ->patch("/notifications/{$notificationId}/read")
            ->assertRedirect('http://127.0.0.1:8000/mahasiswa/assignments/19');
    }
}
