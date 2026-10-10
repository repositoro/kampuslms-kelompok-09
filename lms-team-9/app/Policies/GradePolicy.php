<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;

class GradePolicy
{
    public function view(User $user, Grade $grade): bool
    {
        return $user->can('view', $grade->submission);
    }

    public function create(User $user, Submission $submission): bool
    {
        return $user->role === 'dosen'
            && $submission->assignment->course->lecturer_id === $user->id;
    }

    public function update(User $user, Grade $grade): bool
    {
        return $this->create($user, $grade->submission);
    }

    public function viewCourse(User $user, Course $course): bool
    {
        return $user->role === 'dosen' && $course->lecturer_id === $user->id;
    }
}
