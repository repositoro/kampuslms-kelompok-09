<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Assignment;
use App\Services\SubmissionService;

class SubmissionController extends Controller
{
    public function store(StoreSubmissionRequest $request, Assignment $assignment, SubmissionService $submissionService)
    {
        [$submission, $created] = $submissionService->store(
            $assignment,
            $request->user(),
            $request->file('file'),
            $request->validated('note'),
        );

        // Redirect ke halaman detail tugas (halaman kumpul) supaya mahasiswa
        // langsung melihat status pengumpulan yang baru disimpan.
        return redirect()->route('dosen.assignments.show', $assignment)
            ->with('success', $created ? 'Tugas berhasil dikumpulkan.' : 'Pengumpulan tugas berhasil diperbarui.');
    }
}