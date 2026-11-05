<?php

namespace App\Listeners;

use App\Events\GradeRecorded;
use App\Notifications\GradeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendGradeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 10;

    public function handle(GradeRecorded $event): void
    {
        // Pencegahan lazy loading violation saat berjalan di antrean background worker
        $event->grade->loadMissing('submission.student');
        $student = $event->grade->submission?->student;

        if ($student) {
            $student->notify(new GradeNotification($event->grade));
        }
    }

    public function failed(GradeRecorded $event, \Throwable $exception): void
    {
        Log::error('SendGradeNotification listener gagal diproses.', [
            'grade_id' => $event->grade->id,
            'error'    => $exception->getMessage(),
        ]);
    }
}
