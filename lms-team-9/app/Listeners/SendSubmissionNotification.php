<?php

namespace App\Listeners;

use App\Events\SubmissionSubmitted;
use App\Notifications\SubmissionSubmittedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendSubmissionNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 10;

    public function handle(SubmissionSubmitted $event): void
    {
        // Pencegahan lazy loading violation saat berjalan di antrean background worker
        $event->submission->loadMissing('assignment.course.lecturer');
        $lecturer = $event->submission->assignment?->course?->lecturer;

        if ($lecturer) {
            $lecturer->notify(new SubmissionSubmittedNotification($event->submission));
        }
    }

    public function failed(SubmissionSubmitted $event, \Throwable $exception): void
    {
        Log::error('SendSubmissionNotification listener gagal diproses.', [
            'submission_id' => $event->submission->id,
            'error'         => $exception->getMessage(),
        ]);
    }
}
