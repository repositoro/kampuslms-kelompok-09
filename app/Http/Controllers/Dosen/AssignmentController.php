<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    /**
     * Tampilkan daftar tugas dalam suatu mata kuliah.
     */
    public function index(Request $request, Course $course): View
    {
        abort_unless($course->lecturer_id === $request->user()->id, 403);

        $assignments = $course->assignments()->latest()->paginate(15);

        return view('dosen.assignments.index', compact('course', 'assignments'));
    }

    /**
     * Tampilkan formulir tambah tugas.
     */
    public function create(Request $request, Course $course): View
    {
        abort_unless($course->lecturer_id === $request->user()->id, 403);

        return view('dosen.assignments.create', compact('course'));
    }

    /**
     * Simpan tugas baru.
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless($course->lecturer_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'due_at'       => 'required|date|after:now',
            'max_score'    => 'required|integer|min:0|max:100',
            'allow_late'   => 'boolean',
        ]);

        $course->assignments()->create([
            ...$validated,
            'created_by' => $request->user()->id,
            'status'     => 'draft',
        ]);

        return redirect()->route('dosen.courses.assignments.index', $course)
            ->with('success', 'Tugas berhasil ditambahkan!');
    }

    /**
     * Tampilkan detail tugas beserta daftar submission.
     * Shallow: diakses langsung via assignment ID.
     */
    public function show(Request $request, Assignment $assignment): View
    {
        // Pemeriksaan kepemilikan sementara — akan diganti Policy di minggu 7
        abort_unless($assignment->course->lecturer_id === $request->user()->id, 403);

        $assignment->load('course', 'submissions.student', 'submissions.grade');

        return view('dosen.assignments.show', compact('assignment'));
    }

    /**
     * Tampilkan formulir edit tugas.
     */
    public function edit(Request $request, Assignment $assignment): View
    {
        abort_unless($assignment->course->lecturer_id === $request->user()->id, 403);

        return view('dosen.assignments.edit', compact('assignment'));
    }

    /**
     * Perbarui data tugas.
     */
    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_unless($assignment->course->lecturer_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'due_at'       => 'required|date',
            'max_score'    => 'required|integer|min:0|max:100',
            'allow_late'   => 'boolean',
            'status'       => 'required|in:draft,published,archived',
        ]);

        $assignment->update($validated);

        return redirect()->route('dosen.assignments.show', $assignment)
            ->with('success', 'Tugas berhasil diperbarui!');
    }

    /**
     * Hapus tugas.
     */
    public function destroy(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_unless($assignment->course->lecturer_id === $request->user()->id, 403);

        $course = $assignment->course;
        $assignment->delete();

        return redirect()->route('dosen.courses.assignments.index', $course)
            ->with('success', 'Tugas berhasil dihapus!');
    }
}
