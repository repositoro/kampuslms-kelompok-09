<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SubmissionService
{
    /**
     * @return array{0: Submission, 1: bool}
     */
    public function store(Assignment $assignment, User $user, UploadedFile $file, ?string $note): array
    {
        $submission = $assignment->submissions()->firstOrNew(['user_id' => $user->id]);
        $oldFilePath = $submission->file_path;
        
        // Simpan ke storage/app/private/submissions/{assignment_id}/
        $filePath = $file->store("submissions/{$assignment->id}", 'local');
        $submittedAt = now();

        $submission->fill([
            'file_path'     => $filePath,
            'original_name' => basename($file->getClientOriginalName()),
            'file_size'     => $file->getSize(),
            'mime_type'     => $file->getMimeType(),
            'note'          => $note,
            'submitted_at'  => $submittedAt,
            'is_late'       => $submittedAt->greaterThan($assignment->due_at),
        ]);
        $submission->save();
        
        $created = $submission->wasRecentlyCreated;

        // Hapus berkas lama jika mahasiswa melakukan pembaruan (resubmit)
        if ($oldFilePath && $oldFilePath !== $filePath) {
            Storage::disk('local')->delete($oldFilePath);
        }

        return [$submission->load(['student', 'grade']), $created];
    }
}