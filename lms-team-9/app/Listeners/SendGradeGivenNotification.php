<?php

namespace App\Listeners;

use App\Events\GradeGiven;
use App\Notifications\AssignmentGradedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendGradeGivenNotification implements ShouldQueue
{
    public int $tries = 3;
    public int $backoff = 10;

    public function handle(GradeGiven $event): void
    {
        $student = $event->grade->submission->student;

        if ($student) {
            $student->notify(new AssignmentGradedNotification($event->grade));
        }
    }

    public function failed(GradeGiven $event, Throwable $exception): void
    {
        Log::error("Gagal mengirim notifikasi nilai (ID Grade: {$event->grade->id}): " . $exception->getMessage());
    }
}