<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index(Course $course)
    {
        Gate::authorize('view', $course);
        $materials = $course->materials()->latest()->paginate(15);

        return view('dosen.materials.index', compact('course', 'materials'));
    }

    // Menampilkan formulir tambah materi perkuliahan
    public function create(Course $course)
    {
        // Otorisasi: Pastikan user berhak mengubah/menambah materi pada mata kuliah ini
        Gate::authorize('create', [Material::class, $course]);

        return view('dosen.materials.create', compact('course'));
    }

    // Menyimpan data materi baru (metadata) ke database
    public function store(StoreMaterialRequest $request, Course $course)
    {
        $data = $request->safe()->except('file');
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['type'] = 'file';
            $data['file_path'] = $file->store('materials', 'local');
            $data['original_name'] = basename($file->getClientOriginalName());
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
        }
        $data['uploaded_by'] = $request->user()->id;
        $course->materials()->create($data);

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

    public function show(Material $material)
    {
        Gate::authorize('view', $material);

        return view('dosen.materials.show', compact('material'));
    }

    // Memperbarui metadata materi perkuliahan
    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $data = $request->safe()->except('file');
        $oldFilePath = $material->file_path;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['type'] = 'file';
            $data['file_path'] = $file->store('materials', 'local');
            $data['original_name'] = basename($file->getClientOriginalName());
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
        }
        if (($data['type'] ?? $material->type) === 'link') {
            $data['file_path'] = null;
            $data['original_name'] = null;
            $data['file_size'] = null;
            $data['mime_type'] = null;
        }
        $material->update($data);
        if ($oldFilePath && $material->file_path !== $oldFilePath) {
            Storage::disk('local')->delete($oldFilePath);
        }

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
        if ($material->file_path) {
            Storage::disk('local')->delete($material->file_path);
        }
        $material->delete();

        return redirect()->route('courses.show', $courseId)
            ->with('success', 'Materi berhasil dihapus.');
    }
}
