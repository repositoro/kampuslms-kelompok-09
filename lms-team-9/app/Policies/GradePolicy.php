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
        $grade->loadMissing('submission');

        return $grade->submission ? $user->can('view', $grade->submission) : false;
    }

    public function create(User $user, Submission $submission): bool
    {
        if ($user->role !== 'dosen') {
            return false;
        }

        $submission->loadMissing('assignment.course');

        return $submission->assignment?->course?->lecturer_id === $user->id;
    }

    public function update(User $user, Grade $grade): bool
    {
        $grade->loadMissing('submission');

        return $grade->submission ? $this->create($user, $grade->submission) : false;
    }

    public function viewCourse(User $user, Course $course): bool
    {
        return $user->role === 'dosen' && $course->lecturer_id === $user->id;
    }
}
