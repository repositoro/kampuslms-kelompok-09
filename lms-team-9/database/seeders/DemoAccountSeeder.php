<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('app.demo_account_password') ?? 'password';

        if (app()->environment('production') && ($password === 'password' || $password === '')) {
            throw new RuntimeException('Set DEMO_ACCOUNT_PASSWORD to a non-default value before seeding production.');
        }

        foreach ([
            ['admin@kampuslms.test', 'Admin Demo', 'admin', 'ADM001'],
            ['dosen@kampuslms.test', 'Dosen Demo', 'dosen', 'DSN001'],
            ['mahasiswa@kampuslms.test', 'Mahasiswa Demo', 'mahasiswa', 'MHS001'],
        ] as [$email, $name, $role, $nimNip]) {
            $user = User::firstOrNew(['email' => $email]);
            $user->name = $name;
            $user->password = Hash::make($password);
            $user->role = $role;
            $user->nim_nip = $nimNip;
            $user->save();
        }
    }
}
