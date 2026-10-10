<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function viewAny(User $user, Assignment $assignment): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen' && $assignment->course->lecturer_id === $user->id);
    }

    public function view(User $user, Submission $submission): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'dosen') {
            return $submission->assignment->course->lecturer_id === $user->id;
        }

        return $user->role === 'mahasiswa'
            && $submission->user_id === $user->id
            && $this->create($user, $submission->assignment);
    }

    public function create(User $user, Assignment $assignment): bool
    {
        return $user->role === 'mahasiswa'
            && $assignment->status === 'published'
            && $assignment->course->students()->whereKey($user->id)->exists();
    }

    public function update(User $user, Submission $submission): bool
    {
        return $user->role === 'mahasiswa'
            && $submission->user_id === $user->id
            && $this->create($user, $submission->assignment);
    }
}
