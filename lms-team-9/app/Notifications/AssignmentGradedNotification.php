<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AssignmentGradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Grade $grade) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $submission = $this->grade->submission;
        $assignment = $submission->assignment;

        return [
            'assignment_id' => $assignment->id,
            'submission_id' => $submission->id,
            'title'         => $assignment->title,
            'url'           => route('mahasiswa.assignments.show', $assignment, false),
        ];
    }
}