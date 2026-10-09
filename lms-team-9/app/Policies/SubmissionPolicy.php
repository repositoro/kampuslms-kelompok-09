<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Submission $submission): bool
    {
        if ($user->role === 'admin') return true;
        if ($user->role === 'dosen') return $submission->assignment->course->lecturer_id === $user->id;
        return $submission->user_id === $user->id;
    }

    public function create(User $user, Assignment $assignment): bool
    {
        return $user->role === 'mahasiswa' 
            && $assignment->course->students()->whereKey($user->id)->exists();
    }

    public function update(User $user, Submission $submission): bool
    {
        return $user->role === 'mahasiswa' && $submission->user_id === $user->id;
    }
}