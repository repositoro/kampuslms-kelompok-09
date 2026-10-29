<?php

namespace App\Listeners;

use App\Events\AssignmentPublished;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendAssignmentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 10;

    public function handle(AssignmentPublished $event): void
    {
        // Pencegahan N+1: Ambil seluruh mahasiswa terdaftar secara langsung
        $students = $event->assignment->course->students;

        if ($students->isNotEmpty()) {
            Notification::send($students, new NewAssignmentNotification($event->assignment));
        }
    }

    public function failed(AssignmentPublished $event, \Throwable $exception): void
    {
        Log::error('SendAssignmentNotification listener gagal diproses.', [
            'assignment_id' => $event->assignment->id,
            'error'         => $exception->getMessage(),
        ]);
    }
}
