<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialResource;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaterialController extends Controller
{
    /**
     * Daftar materi dalam satu course (dosen pemilik course).
     */
    public function index(Course $course): AnonymousResourceCollection
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($course->lecturer_id === auth()->id(), 403);

        $materials = $course->materials()->with('uploader')->paginate(15);
        return MaterialResource::collection($materials);
    }

    /**
     * Buat materi baru di bawah course.
     */
    public function store(Request $request, Course $course): JsonResponse
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($course->lecturer_id === auth()->id(), 403);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'type'         => ['required', 'in:file,link'],
            'file_path'    => ['required_if:type,file', 'nullable', 'string'],
            'original_name'=> ['nullable', 'string'],
            'file_size'    => ['nullable', 'integer'],
            'mime_type'    => ['nullable', 'string'],
            'external_url' => ['required_if:type,link', 'nullable', 'url'],
        ]);

        $validated['uploaded_by'] = auth()->id();
        $material = $course->materials()->create($validated);
        $material->load('uploader');

        return response()->json(['data' => new MaterialResource($material)], 201);
    }

    /**
     * Detail materi (shallow — akses langsung via ID).
     */
    public function show(Material $material): MaterialResource
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($material->course->lecturer_id === auth()->id(), 403);

        $material->load(['uploader', 'course.lecturer']);
        return new MaterialResource($material);
    }

    /**
     * Perbarui materi.
     */
    public function update(Request $request, Material $material): MaterialResource
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($material->course->lecturer_id === auth()->id(), 403);

        $validated = $request->validate([
            'title'        => ['sometimes', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'type'         => ['sometimes', 'in:file,link'],
            'file_path'    => ['nullable', 'string'],
            'original_name'=> ['nullable', 'string'],
            'file_size'    => ['nullable', 'integer'],
            'mime_type'    => ['nullable', 'string'],
            'external_url' => ['nullable', 'url'],
        ]);

        $material->update($validated);
        $material->load('uploader');
        return new MaterialResource($material);
    }

    /**
     * Hapus materi.
     */
    public function destroy(Material $material): JsonResponse
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($material->course->lecturer_id === auth()->id(), 403);

        $material->delete();
        return response()->json(null, 204);
    }
}
