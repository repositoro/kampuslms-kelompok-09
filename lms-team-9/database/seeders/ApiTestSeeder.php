<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApiTestSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            ['name' => 'Admin Test', 'password' => Hash::make('password'), 'role' => 'admin', 'nim_nip' => 'ADM001']
        );

        // Dosen A (pemilik course 1)
        $dosenA = User::firstOrCreate(
            ['email' => 'dosena@test.com'],
            ['name' => 'Dosen A', 'password' => Hash::make('password'), 'role' => 'dosen', 'nim_nip' => 'DSN001']
        );

        // Dosen B (tidak memiliki course 1)
        $dosenB = User::firstOrCreate(
            ['email' => 'dosenb@test.com'],
            ['name' => 'Dosen B', 'password' => Hash::make('password'), 'role' => 'dosen', 'nim_nip' => 'DSN002']
        );

        // Mahasiswa A
        $mahasiswaA = User::firstOrCreate(
            ['email' => 'mhsa@test.com'],
            ['name' => 'Mahasiswa A', 'password' => Hash::make('password'), 'role' => 'mahasiswa', 'nim_nip' => 'MHS001']
        );

        // Mahasiswa B
        $mahasiswaB = User::firstOrCreate(
            ['email' => 'mhsb@test.com'],
            ['name' => 'Mahasiswa B', 'password' => Hash::make('password'), 'role' => 'mahasiswa', 'nim_nip' => 'MHS002']
        );

        // Course milik Dosen A
        $course = Course::firstOrCreate(
            ['code' => 'TST101'],
            [
                'name'        => 'Mata Kuliah Test',
                'description' => 'Untuk pengujian API',
                'sks'         => 3,
                'lecturer_id' => $dosenA->id,
                'status'      => 'active',
            ]
        );

        // Enroll Mahasiswa A ke course
        $course->students()->syncWithoutDetaching([
            $mahasiswaA->id => ['enrolled_at' => now()],
        ]);

        // Material di course Dosen A
        $material = Material::firstOrCreate(
            ['course_id' => $course->id, 'title' => 'Materi Awal'],
            [
                'uploaded_by' => $dosenA->id,
                'description' => 'Materi pertama',
                'type'        => 'link',
                'external_url'=> 'https://example.com',
            ]
        );

        // Assignment di course Dosen A
        $assignment = Assignment::firstOrCreate(
            ['course_id' => $course->id, 'title' => 'Tugas Awal'],
            [
                'created_by'   => $dosenA->id,
                'instructions' => 'Kerjakan tugas ini.',
                'due_at'       => now()->addDays(7),
                'status'       => 'published',
            ]
        );

        // Submission Mahasiswa A
        $submission = Submission::firstOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => $mahasiswaA->id],
            [
                'file_path'     => 'submissions/test.pdf',
                'original_name' => 'test.pdf',
                'file_size'     => 12345,
                'submitted_at'  => now(),
            ]
        );

        $this->command->info('API Test Seeder berhasil dijalankan.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin',      'admin@test.com',  'password'],
                ['Dosen A',    'dosena@test.com', 'password'],
                ['Dosen B',    'dosenb@test.com', 'password'],
                ['Mahasiswa A','mhsa@test.com',   'password'],
                ['Mahasiswa B','mhsb@test.com',   'password'],
            ]
        );
    }
}
