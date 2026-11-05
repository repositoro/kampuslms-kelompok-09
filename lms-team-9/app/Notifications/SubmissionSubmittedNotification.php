<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Notifications\Notification;

class SubmissionSubmittedNotification extends Notification
{
    public function __construct(public Submission $submission) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->submission->loadMissing(['student', 'assignment.course']);

        $studentName = $this->submission->student?->name ?? 'Mahasiswa';
        $assignmentTitle = $this->submission->assignment?->title ?? 'Tugas';
        $courseName = $this->submission->assignment?->course?->name ?? 'Mata Kuliah';

        return [
            'type'             => 'submission_submitted',
            'submission_id'    => $this->submission->id,
            'assignment_id'    => $this->submission->assignment_id,
            'assignment_title' => $assignmentTitle,
            'course_name'      => $courseName,
            'student_name'     => $studentName,
            'is_late'          => (bool) $this->submission->is_late,
            'title'            => 'Pengumpulan Tugas Baru',
            'message'          => $studentName . ' telah mengumpulkan tugas "' . $assignmentTitle . '"' . ($this->submission->is_late ? ' (Terlambat)' : '') . '.',
            'url'              => route('dosen.assignments.submissions', $this->submission->assignment_id),
        ];
    }
}
