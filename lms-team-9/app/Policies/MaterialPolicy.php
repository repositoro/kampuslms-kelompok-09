<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function view(User $user, Material $material): bool
    {
        $course = $material->relationLoaded('course') ? $material->course : $material->course()->first();

        return $course ? $user->can('view', $course) : false;
    }

    public function create(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen' && $course->lecturer_id === $user->id);
    }

    public function update(User $user, Material $material): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role !== 'dosen') {
            return false;
        }

        $course = $material->relationLoaded('course') ? $material->course : $material->course()->first();

        return $course && $course->lecturer_id === $user->id;
    }

    public function delete(User $user, Material $material): bool
    {
        return $this->update($user, $material);
    }
}
