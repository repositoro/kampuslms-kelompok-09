<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Notifications\Notification;

class GradeNotification extends Notification
{
    public function __construct(public Grade $grade) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        // PENTING (Milestone M3 Poin 8): Payload notifikasi DILARANG memuat nilai (score)
        // maupun feedback untuk mencegah kebocoran data sensitif pada tabel notifications.
        $this->grade->loadMissing('submission.assignment.course');

        $submission = $this->grade->submission;
        $assignment = $submission?->assignment;
        $course = $assignment?->course;

        return [
            'type'             => 'grade_recorded',
            'grade_id'         => $this->grade->id,
            'submission_id'    => $this->grade->submission_id,
            'assignment_id'    => $submission?->assignment_id,
            'assignment_title' => $assignment?->title ?? 'Tugas',
            'course_name'      => $course?->name ?? 'Mata Kuliah',
            'title'            => 'Nilai Tugas Diberikan',
            'message'          => 'Nilai tugas "' . ($assignment?->title ?? 'Tugas') . '" telah keluar.',
            'url'              => $assignment ? route('dosen.assignments.show', $assignment) : '#',
        ];
    }
}
