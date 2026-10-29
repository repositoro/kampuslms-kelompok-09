<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    /**
     * Memeriksa apakah Dosen/Admin dapat melihat daftar pengumpulan tugas.
     */
    public function viewAny(User $user, Assignment $assignment): bool
    {
        $assignment->loadMissing('course');

        return $user->role === 'admin'
            || ($user->role === 'dosen' && $assignment->course?->lecturer_id === $user->id);
    }

    /**
     * Memeriksa apakah User dapat melihat/mengunduh berkas jawaban tugas tertentu.
     */
    public function view(User $user, Submission $submission): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        $submission->loadMissing('assignment.course');

        if ($user->role === 'dosen') {
            return $submission->assignment?->course?->lecturer_id === $user->id;
        }

        // Mahasiswa pemilik tugas yang terdaftar di mata kuliah diizinkan mengunduh/melihat
        return $user->role === 'mahasiswa'
            && $submission->user_id === $user->id
            && (bool) $submission->assignment?->course?->students()->whereKey($user->id)->exists();
    }

    /**
     * Memeriksa apakah Mahasiswa dapat membuat pengumpulan baru.
     */
    public function create(User $user, Assignment $assignment): bool
    {
        $assignment->loadMissing('course');

        return $user->role === 'mahasiswa'
            && ($assignment->status ?? 'published') === 'published'
            && (bool) $assignment->course?->students()->whereKey($user->id)->exists();
    }

    /**
     * Memeriksa apakah Mahasiswa dapat memperbarui pengumpulan tugas.
     */
    public function update(User $user, Submission $submission): bool
    {
        return $user->role === 'mahasiswa'
            && $submission->user_id === $user->id
            && $this->create($user, $submission->assignment);
    }
}