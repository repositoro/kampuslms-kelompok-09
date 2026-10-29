<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SubmissionSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public Submission $submission) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'             => 'submission_submitted',
            'submission_id'    => $this->submission->id,
            'assignment_id'    => $this->submission->assignment_id,
            'assignment_title' => $this->submission->assignment->title,
            'course_name'      => $this->submission->assignment->course->name,
            'student_name'     => $this->submission->student?->name ?? 'Mahasiswa',
            'is_late'          => (bool) $this->submission->is_late,
            'title'            => 'Pengumpulan Tugas Baru',
            'message'          => ($this->submission->student?->name ?? 'Mahasiswa') . ' telah mengumpulkan tugas "' . $this->submission->assignment->title . '"' . ($this->submission->is_late ? ' (Terlambat)' : '') . '.',
            'url'              => route('dosen.assignments.submissions', $this->submission->assignment_id),
        ];
    }

    public function failed(?\Throwable $exception = null): void
    {
        Log::error('Gagal memproses pengiriman SubmissionSubmittedNotification.', [
            'submission_id' => $this->submission->id,
            'error'         => $exception?->getMessage(),
        ]);
    }
}
