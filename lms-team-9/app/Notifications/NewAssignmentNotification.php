<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Notifications\Notification;

class NewAssignmentNotification extends Notification
{
    public function __construct(public Assignment $assignment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->assignment->loadMissing('course');

        return [
            'type'            => 'assignment_published',
            'assignment_id'   => $this->assignment->id,
            'course_id'       => $this->assignment->course_id,
            'course_name'     => $this->assignment->course?->name ?? 'Mata Kuliah',
            'title'           => $this->assignment->title,
            'due_at'          => $this->assignment->due_at?->toIso8601String(),
            'message'         => 'Tugas baru: ' . $this->assignment->title,
            'url'             => route('dosen.assignments.show', $this->assignment),
        ];
    }
}
