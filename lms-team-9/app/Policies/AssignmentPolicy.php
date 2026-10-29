<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;

class AssignmentPolicy
{
    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        $course = $assignment->relationLoaded('course') ? $assignment->course : $assignment->course()->first();

        if ($user->role === 'dosen') {
            return $course && $course->lecturer_id === $user->id;
        }

        return $user->role === 'mahasiswa'
            && $assignment->status === 'published'
            && $course !== null
            && $user->can('view', $course);
    }

    public function create(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen' && $course->lecturer_id === $user->id);
    }

    public function update(User $user, Assignment $assignment): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role !== 'dosen') {
            return false;
        }

        $course = $assignment->relationLoaded('course') ? $assignment->course : $assignment->course()->first();

        return $course && $course->lecturer_id === $user->id;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    public function deleteApi(User $user, Assignment $assignment): bool
    {
        return $this->delete($user, $assignment);
    }
}
