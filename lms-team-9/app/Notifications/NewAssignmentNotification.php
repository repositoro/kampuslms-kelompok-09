<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class NewAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public Assignment $assignment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'assignment_published',
            'assignment_id'   => $this->assignment->id,
            'course_id'       => $this->assignment->course_id,
            'course_name'     => $this->assignment->course->name,
            'title'           => $this->assignment->title,
            'due_at'          => $this->assignment->due_at?->toIso8601String(),
            'message'         => 'Tugas baru: ' . $this->assignment->title,
            'url'             => route('dosen.assignments.show', $this->assignment),
        ];
    }

    public function failed(?\Throwable $exception = null): void
    {
        Log::error('Gagal memproses pengiriman notifikasi NewAssignmentNotification.', [
            'assignment_id' => $this->assignment->id,
            'error'         => $exception?->getMessage(),
        ]);
    }
}
