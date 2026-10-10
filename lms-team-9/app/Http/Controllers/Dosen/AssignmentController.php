<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    // Menampilkan formulir pembuat tugas baru
    public function create(Course $course)
    {
        // Otorisasi: Dosen hanya bisa menambah tugas di kelasnya sendiri
        Gate::authorize('update', $course);

        return view('dosen.assignments.create', compact('course'));
    }

    // Menyimpan data tugas perkuliahan (metadata) ke database
    public function store(Request $request, Course $course)
    {
        // Otorisasi IDOR: Cegah manipulasi course_id dari input URL
        Gate::authorize('update', $course);

        // Validasi metadata tugas
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at'      => ['required', 'date', 'after:now'],
            'status'      => ['required', 'in:draft,published'],
        ]);

        // Simpan tugas melalui relasi Course
        $course->assignments()->create($validated);

        return redirect()->route('courses.show', $course)
            ->with('success', 'Tugas berhasil dibuat.');
    }

    // Menampilkan detail tugas beserta pengumpulan (submissions) mahasiswa
    public function show(Assignment $assignment)
    {
        // Otorisasi: Validasi hak akses melihat tugas
        Gate::authorize('view', $assignment);

        // Eager loading relasi submission, mahasiswa, dan nilai untuk optimalisasi query
        $assignment->load(['submissions.student', 'submissions.grade']);

        return view('dosen.assignments.show', compact('assignment'));
    }

    // Menampilkan formulir edit tugas (Shallow Route)
    public function edit(Assignment $assignment)
    {
        // Otorisasi IDOR: Memastikan dosen hanya bisa mengedit tugas kelas miliknya
        Gate::authorize('update', $assignment);

        return view('dosen.assignments.edit', compact('assignment'));
    }

    // Memperbarui metadata tugas perkuliahan
    public function update(Request $request, Assignment $assignment)
    {
        // Otorisasi IDOR
        Gate::authorize('update', $assignment);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at'      => ['required', 'date'],
            'status'      => ['required', 'in:draft,published'],
        ]);

        $assignment->update($validated);

        return redirect()->route('courses.show', $assignment->course_id)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    // Menghapus tugas perkuliahan
    public function destroy(Assignment $assignment)
    {
        // Otorisasi IDOR
        Gate::authorize('delete', $assignment);

        $courseId = $assignment->course_id;
        $assignment->delete();

        return redirect()->route('courses.show', $courseId)
            ->with('success', 'Tugas berhasil dihapus.');
    }
}