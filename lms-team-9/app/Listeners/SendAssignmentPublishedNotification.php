<?php

namespace App\Listeners;

use App\Events\AssignmentPublished;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendAssignmentPublishedNotification implements ShouldQueue
{
    public int $tries = 3;
    public int $backoff = 10;

    public function handle(AssignmentPublished $event): void
    {
        $students = $event->assignment->course->students;

        if ($students->isNotEmpty()) {
            Notification::send($students, new NewAssignmentNotification($event->assignment));
        }
    }

    public function failed(AssignmentPublished $event, Throwable $exception): void
    {
        Log::error("Gagal mengirim notifikasi tugas baru (ID Assignment: {$event->assignment->id}): " . $exception->getMessage());
    }
}