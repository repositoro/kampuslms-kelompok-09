<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    /**
     * Tampilkan daftar materi dalam suatu mata kuliah.
     * abort_unless: dosen hanya bisa kelola materi mata kuliah yang dia ampu.
     */
    public function index(Request $request, Course $course): View
    {
        abort_unless($course->lecturer_id === $request->user()->id, 403);

        $materials = $course->materials()->latest()->paginate(15);

        return view('dosen.materials.index', compact('course', 'materials'));
    }

    /**
     * Tampilkan formulir tambah materi.
     */
    public function create(Request $request, Course $course): View
    {
        abort_unless($course->lecturer_id === $request->user()->id, 403);

        return view('dosen.materials.create', compact('course'));
    }

    /**
     * Simpan materi baru ke database.
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless($course->lecturer_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:file,link,text',
            'file'        => 'nullable|file|max:10240',
            'external_url'=> 'nullable|url|max:2048',
        ]);

        $data = [
            'course_id'   => $course->id,
            'uploaded_by'  => $request->user()->id,
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type'        => $validated['type'],
            'external_url'=> $validated['external_url'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path']     = $file->store('materials', 'public');
            $data['original_name'] = $file->getClientOriginalName();
            $data['file_size']     = $file->getSize();
            $data['mime_type']     = $file->getMimeType();
        }

        Material::create($data);

        return redirect()->route('dosen.courses.materials.index', $course)
            ->with('success', 'Materi berhasil ditambahkan!');
    }

    /**
     * Tampilkan detail materi.
     * Shallow: material diakses langsung via ID tanpa course parent.
     */
    public function show(Request $request, Material $material): View
    {
        // Pemeriksaan kepemilikan sementara — akan diganti Policy di minggu 7
        abort_unless($material->course->lecturer_id === $request->user()->id, 403);

        $material->load('course', 'uploader');

        return view('dosen.materials.show', compact('material'));
    }

    /**
     * Tampilkan formulir edit materi.
     */
    public function edit(Request $request, Material $material): View
    {
        abort_unless($material->course->lecturer_id === $request->user()->id, 403);

        return view('dosen.materials.edit', compact('material'));
    }

    /**
     * Perbarui data materi.
     */
    public function update(Request $request, Material $material): RedirectResponse
    {
        abort_unless($material->course->lecturer_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:file,link,text',
            'file'        => 'nullable|file|max:10240',
            'external_url'=> 'nullable|url|max:2048',
        ]);

        $data = [
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type'        => $validated['type'],
            'external_url'=> $validated['external_url'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path']     = $file->store('materials', 'public');
            $data['original_name'] = $file->getClientOriginalName();
            $data['file_size']     = $file->getSize();
            $data['mime_type']     = $file->getMimeType();
        }

        $material->update($data);

        return redirect()->route('dosen.materials.show', $material)
            ->with('success', 'Materi berhasil diperbarui!');
    }

    /**
     * Hapus materi.
     */
    public function destroy(Request $request, Material $material): RedirectResponse
    {
        abort_unless($material->course->lecturer_id === $request->user()->id, 403);

        $course = $material->course;
        $material->delete();

        return redirect()->route('dosen.courses.materials.index', $course)
            ->with('success', 'Materi berhasil dihapus!');
    }
}
