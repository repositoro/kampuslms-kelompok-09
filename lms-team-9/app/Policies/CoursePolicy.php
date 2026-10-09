<?php
namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        if ($user->role === 'admin') return true;
        if ($user->role === 'dosen') return $course->lecturer_id === $user->id;
        return $course->students()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Course $course): bool
    {
        return $user->role === 'admin' || $course->lecturer_id === $user->id;
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }
}