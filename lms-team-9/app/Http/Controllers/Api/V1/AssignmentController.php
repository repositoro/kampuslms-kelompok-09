<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AssignmentController extends Controller
{
    /**
     * Daftar tugas dalam satu course (dosen pemilik course).
     */
    public function index(Course $course): AnonymousResourceCollection
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($course->lecturer_id === auth()->id(), 403);

        $assignments = $course->assignments()
            ->withCount('submissions')
            ->paginate(15);

        return AssignmentResource::collection($assignments);
    }

    /**
     * Buat tugas baru di bawah course.
     */
    public function store(Request $request, Course $course): JsonResponse
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($course->lecturer_id === auth()->id(), 403);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string'],
            'due_at'       => ['required', 'date'],
            'max_score'    => ['nullable', 'integer', 'min:0', 'max:100'],
            'allow_late'   => ['nullable', 'boolean'],
            'status'       => ['required', 'in:draft,published'],
        ]);

        $validated['created_by'] = auth()->id();
        $assignment = $course->assignments()->create($validated);

        return response()->json(['data' => new AssignmentResource($assignment)], 201);
    }

    /**
     * Detail tugas (shallow — akses langsung via ID).
     */
    public function show(Assignment $assignment): AssignmentResource
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);

        $assignment->load('course')->loadCount('submissions');
        return new AssignmentResource($assignment);
    }

    /**
     * Perbarui tugas.
     */
    public function update(Request $request, Assignment $assignment): AssignmentResource
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);

        $validated = $request->validate([
            'title'        => ['sometimes', 'string', 'max:255'],
            'instructions' => ['sometimes', 'string'],
            'due_at'       => ['sometimes', 'date'],
            'max_score'    => ['nullable', 'integer', 'min:0', 'max:100'],
            'allow_late'   => ['nullable', 'boolean'],
            'status'       => ['sometimes', 'in:draft,published'],
        ]);

        $assignment->update($validated);
        $assignment->loadCount('submissions');
        return new AssignmentResource($assignment);
    }

    /**
     * Hapus tugas.
     */
    public function destroy(Assignment $assignment): JsonResponse
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);

        $assignment->delete();
        return response()->json(null, 204);
    }
}
