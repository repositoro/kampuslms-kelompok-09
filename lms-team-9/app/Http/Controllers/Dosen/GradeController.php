<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    /**
     * Tampilkan formulir penilaian untuk suatu submission.
     */
    public function create(Request $request, Submission $submission): View
    {
        // Pemeriksaan kepemilikan sementara — akan diganti Policy di minggu 7
        abort_unless(
            $submission->assignment->course->lecturer_id === $request->user()->id,
            403
        );

        // Jangan izinkan double-grading — redirect ke edit jika sudah dinilai
        if ($submission->grade) {
            return redirect()->route('dosen.grades.edit', $submission->grade);
        }

        $submission->load('student', 'assignment');

        return view('dosen.grades.create', compact('submission'));
    }

    /**
     * Simpan nilai baru.
     */
    public function store(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless(
            $submission->assignment->course->lecturer_id === $request->user()->id,
            403
        );

        $validated = $request->validate([
            'score'    => 'required|numeric|min:0|max:' . $submission->assignment->max_score,
            'feedback' => 'nullable|string|max:2000',
        ]);

        Grade::create([
            'submission_id' => $submission->id,
            'graded_by'     => $request->user()->id,
            'score'         => $validated['score'],
            'feedback'      => $validated['feedback'] ?? null,
            'graded_at'     => now(),
        ]);

        return redirect()->route('dosen.assignments.show', $submission->assignment)
            ->with('success', 'Nilai berhasil disimpan!');
    }

    /**
     * Tampilkan formulir edit nilai.
     */
    public function edit(Request $request, Grade $grade): View
    {
        abort_unless(
            $grade->submission->assignment->course->lecturer_id === $request->user()->id,
            403
        );

        $grade->load('submission.student', 'submission.assignment');

        return view('dosen.grades.edit', compact('grade'));
    }

    /**
     * Perbarui nilai.
     */
    public function update(Request $request, Grade $grade): RedirectResponse
    {
        abort_unless(
            $grade->submission->assignment->course->lecturer_id === $request->user()->id,
            403
        );

        $validated = $request->validate([
            'score'    => 'required|numeric|min:0|max:' . $grade->submission->assignment->max_score,
            'feedback' => 'nullable|string|max:2000',
        ]);

        $grade->update([
            ...$validated,
            'graded_at' => now(),
        ]);

        return redirect()->route('dosen.assignments.show', $grade->submission->assignment)
            ->with('success', 'Nilai berhasil diperbarui!');
    }
}
