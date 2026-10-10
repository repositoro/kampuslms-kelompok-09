<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MaterialController extends Controller
{
    // Menampilkan formulir tambah materi perkuliahan
    public function create(Course $course)
    {
        // Otorisasi: Pastikan user berhak mengubah/menambah materi pada mata kuliah ini
        Gate::authorize('update', $course);

        return view('dosen.materials.create', compact('course'));
    }

    // Menyimpan data materi baru (metadata) ke database
    public function store(Request $request, Course $course)
    {
        // Otorisasi IDOR: Verifikasi hak akses dosen terhadap mata kuliah sasaran
        Gate::authorize('update', $course);

        // Validasi metadata materi (unggah berkas fisik diimplementasikan di Minggu 9)
        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'content'  => ['nullable', 'string'],
            'link_url' => ['nullable', 'url', 'max:255'],
        ]);

        // Simpan materi melalui relasi Course
        $course->materials()->create($validated);

        return redirect()->route('courses.show', $course)
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    // Menampilkan formulir edit materi (Shallow Route: hanya menerima $material)
    public function edit(Material $material)
    {
        // Otorisasi IDOR: Dosen hanya boleh mengedit materi miliknya sendiri
        Gate::authorize('update', $material);

        return view('dosen.materials.edit', compact('material'));
    }

    // Memperbarui metadata materi perkuliahan
    public function update(Request $request, Material $material)
    {
        // Otorisasi IDOR: Pastikan materi milik mata kuliah dosen yang bersangkutan
        Gate::authorize('update', $material);

        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'content'  => ['nullable', 'string'],
            'link_url' => ['nullable', 'url', 'max:255'],
        ]);

        $material->update($validated);

        // Pengalihan kembali ke detail mata kuliah menggunakan course_id materi
        return redirect()->route('courses.show', $material->course_id)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    // Menghapus materi perkuliahan dari database
    public function destroy(Material $material)
    {
        // Otorisasi IDOR: Hanya pembuat/admin yang diizinkan menghapus
        Gate::authorize('delete', $material);

        $courseId = $material->course_id;
        $material->delete();

        return redirect()->route('courses.show', $courseId)
            ->with('success', 'Materi berhasil dihapus.');
    }
}