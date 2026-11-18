<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Assignment;
use App\Models\Submission;
use App\Services\SubmissionService;
use Illuminate\Support\Facades\Gate;

class SubmissionController extends Controller
{
    public function store(StoreSubmissionRequest $request, Assignment $assignment, SubmissionService $submissionService)
    {
        // Otorisasi eksplisit di controller: Pastikan mahasiswa terdaftar di mata kuliah dan tugas published
        Gate::authorize('create', [Submission::class, $assignment]);

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
