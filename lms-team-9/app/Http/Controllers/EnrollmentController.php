<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EnrollmentController extends Controller
{
    // Mendaftarkan (enroll) mahasiswa ke suatu mata kuliah
    public function store(Request $request, Course $course)
    {
        // Otorisasi: Hanya Admin atau Dosen pengampu mata kuliah yang diizinkan
        Gate::authorize('update', $course);

        // Validasi ID mahasiswa yang dipilih
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // Mencegah penambahan berulang (duplicate entry) pada tabel pivot
        if ($course->students()->where('users.id', $validated['user_id'])->exists()) {
            return redirect()->back()->with('error', 'Mahasiswa sudah terdaftar di mata kuliah ini.');
        }

        // Menambahkan record ke tabel pivot course_user beserta timestamp
        $course->students()->attach($validated['user_id'], [
            'enrolled_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Mahasiswa berhasil didaftarkan.');
    }

    // Mengeluarkan (unenroll) mahasiswa dari mata kuliah
    public function destroy(Course $course, User $student)
    {
        // Otorisasi: Hanya Admin atau Dosen pengampu mata kuliah yang diizinkan
        Gate::authorize('update', $course);

        // Menghapus hubungan relasi di tabel pivot course_user
        $course->students()->detach($student->id);

        return redirect()->back()->with('success', 'Mahasiswa berhasil dikeluarkan dari mata kuliah.');
    }
}