<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class GradeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public Grade $grade) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        // PENTING (Milestone M3 Poin 8): Payload notifikasi DILARANG memuat nilai (score)
        // maupun feedback untuk mencegah kebocoran data sensitif pada tabel notifications.
        return [
            'type'             => 'grade_recorded',
            'grade_id'         => $this->grade->id,
            'submission_id'    => $this->grade->submission_id,
            'assignment_id'    => $this->grade->submission->assignment_id,
            'assignment_title' => $this->grade->submission->assignment->title,
            'course_name'      => $this->grade->submission->assignment->course->name,
            'message'          => 'Nilai tugas "' . $this->grade->submission->assignment->title . '" telah keluar.',
            'url'              => route('dosen.assignments.show', $this->grade->submission->assignment_id),
        ];
    }

    public function failed(?\Throwable $exception = null): void
    {
        Log::error('Gagal memproses pengiriman notifikasi GradeNotification.', [
            'grade_id' => $this->grade->id,
            'error'    => $exception?->getMessage(),
        ]);
    }
}
