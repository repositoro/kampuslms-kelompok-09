<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Support\DashboardCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    // Mendaftarkan (enroll) mahasiswa ke suatu mata kuliah
    public function store(Request $request, Course $course)
    {
        // Otorisasi: Hanya Admin atau Dosen pengampu mata kuliah yang diizinkan
        Gate::authorize('manageEnrollment', $course);

        // Validasi ID mahasiswa yang dipilih
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'mahasiswa')->whereNull('deleted_at'),
            ],
        ]);

        if ($course->students()->where('users.id', $validated['user_id'])->exists()) {
            return redirect()->back()->withErrors([
                'user_id' => 'Mahasiswa sudah terdaftar di mata kuliah ini.',
            ]);
        }

        // Menambahkan record ke tabel pivot course_user beserta timestamp
        $course->students()->attach($validated['user_id'], [
            'enrolled_at' => now(),
        ]);
        DashboardCache::invalidate();

        return redirect()->back()->with('success', 'Mahasiswa berhasil didaftarkan.');
    }

    // Mengeluarkan (unenroll) mahasiswa dari mata kuliah
    public function destroy(Course $course, User $student)
    {
        // Otorisasi: Hanya Admin atau Dosen pengampu mata kuliah yang diizinkan
        Gate::authorize('manageEnrollment', $course);

        if ($student->role !== 'mahasiswa') {
            return redirect()->back()->withErrors([
                'student' => 'Hanya mahasiswa yang dapat dikeluarkan dari mata kuliah.',
            ]);
        }

        // Menghapus hubungan relasi di tabel pivot course_user
        $course->students()->detach($student->id);
        DashboardCache::invalidate();

        return redirect()->back()->with('success', 'Mahasiswa berhasil dikeluarkan dari mata kuliah.');
    }
}
