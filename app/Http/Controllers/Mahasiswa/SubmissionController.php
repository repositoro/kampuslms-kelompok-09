<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    /**
     * Tampilkan formulir pengumpulan tugas.
     */
    public function create(Request $request, Assignment $assignment): View
    {
        // Pastikan mahasiswa terdaftar di course-nya
        abort_unless(
            $request->user()->courses()->where('courses.id', $assignment->course_id)->exists(),
            403
        );

        // Cegah double-submit
        $existing = $assignment->submissions()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return redirect()->route('mahasiswa.submissions.show', $existing)
                ->with('error', 'Kamu sudah mengumpulkan tugas ini.');
        }

        $assignment->load('course');

        return view('mahasiswa.submissions.create', compact('assignment'));
    }

    /**
     * Simpan submission baru.
     */
    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_unless(
            $request->user()->courses()->where('courses.id', $assignment->course_id)->exists(),
            403
        );

        $validated = $request->validate([
            'file' => 'required|file|max:10240',
            'note' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $isLate = $assignment->due_at && now()->isAfter($assignment->due_at);

        // Cek apakah terlambat dan tugas tidak mengizinkan keterlambatan
        if ($isLate && ! $assignment->allow_late) {
            return back()->with('error', 'Batas waktu pengumpulan sudah lewat.');
        }

        Submission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => $request->user()->id,
            'file_path'     => $file->store('submissions', 'public'),
            'original_name' => $file->getClientOriginalName(),
            'file_size'     => $file->getSize(),
            'note'          => $validated['note'] ?? null,
            'submitted_at'  => now(),
            'is_late'       => $isLate,
        ]);

        return redirect()->route('mahasiswa.assignments.show', $assignment)
            ->with('success', 'Tugas berhasil dikumpulkan!');
    }

    /**
     * Tampilkan detail submission milik mahasiswa.
     */
    public function show(Request $request, Submission $submission): View
    {
        // Pemeriksaan kepemilikan sementara — akan diganti Policy di minggu 7
        abort_unless($submission->user_id === $request->user()->id, 403);

        $submission->load('assignment.course', 'grade');

        return view('mahasiswa.submissions.show', compact('submission'));
    }
}
