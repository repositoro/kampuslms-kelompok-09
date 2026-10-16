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

        // Gunakan back() agar mahasiswa kembali ke halaman sebelumnya dengan pesan sukses
        return back()->with(
            'success',
            $created ? 'Tugas berhasil dikumpulkan.' : 'Pengumpulan tugas berhasil diperbarui.'
        );
    }
}