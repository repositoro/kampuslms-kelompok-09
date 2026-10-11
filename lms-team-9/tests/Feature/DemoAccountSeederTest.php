<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class DemoAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeding_rejects_missing_or_public_demo_password(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config([
            'app.demo_account_password' => null,
        ]);
        try {
            app(DemoAccountSeeder::class)->run();
            $this->fail('Production seeding must reject the default demo password.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('DEMO_ACCOUNT_PASSWORD', $exception->getMessage());
        }

        config(['app.demo_account_password' => 'password']);
        $this->expectException(RuntimeException::class);
        app(DemoAccountSeeder::class)->run();
    }

    public function test_demo_accounts_use_the_configured_secret(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config([
            'app.demo_account_password' => 'private-test-secret',
        ]);

        app(DemoAccountSeeder::class)->run();

        $admin = User::where('email', 'admin@kampuslms.test')->firstOrFail();
        $this->assertTrue(Hash::check('private-test-secret', $admin->password));
        $this->assertFalse(Hash::check('password', $admin->password));
    }
}
