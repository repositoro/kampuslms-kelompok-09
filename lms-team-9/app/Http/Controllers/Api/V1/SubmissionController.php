<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubmissionController extends Controller
{
    /**
     * Daftar submission:
     * - Mahasiswa: hanya submission miliknya di assignment ini.
     * - Dosen pemilik assignment: semua submission.
     */
    public function index(Assignment $assignment): AnonymousResourceCollection
    {
        $user = auth()->user();

        if ($user->role === 'mahasiswa') {
            $submissions = $assignment->submissions()
                ->where('user_id', $user->id)
                ->with(['student', 'grade'])
                ->paginate(15);
        } else {
            // Dosen — pastikan assignment miliknya
            // TODO(minggu 7): pindahkan ke Policy
            abort_unless($assignment->course->lecturer_id === $user->id, 403);
            $submissions = $assignment->submissions()
                ->with(['student', 'grade'])
                ->paginate(15);
        }

        return SubmissionResource::collection($submissions);
    }

    /**
     * Mahasiswa mengumpulkan tugas.
     */
    public function store(Request $request, Assignment $assignment): JsonResponse
    {
        // TODO(minggu 7): pindahkan ke Policy
        // Pastikan mahasiswa terdaftar di course ini
        abort_unless(
            auth()->user()->courses()->where('courses.id', $assignment->course_id)->exists(),
            403
        );

        $validated = $request->validate([
            'file_path'     => ['required', 'string'],
            'original_name' => ['required', 'string'],
            'file_size'     => ['required', 'integer'],
            'note'          => ['nullable', 'string'],
            'submitted_at'  => ['required', 'date'],
        ]);

        $validated['user_id']  = auth()->id();
        $validated['is_late']  = now()->isAfter($assignment->due_at);

        $submission = $assignment->submissions()->create($validated);
        $submission->load(['student', 'grade']);

        return response()->json(['data' => new SubmissionResource($submission)], 201);
    }

    /**
     * Detail submission (shallow).
     */
    public function show(Submission $submission): SubmissionResource
    {
        $user = auth()->user();

        // TODO(minggu 7): pindahkan ke Policy
        if ($user->role === 'mahasiswa') {
            abort_unless($submission->user_id === $user->id, 403);
        } else {
            // Dosen hanya boleh melihat submission pada assignment miliknya
            abort_unless($submission->assignment->course->lecturer_id === $user->id, 403);
        }

        $submission->load(['student', 'assignment.course', 'grade.grader']);
        return new SubmissionResource($submission);
    }

    /**
     * Mahasiswa memperbarui submission (sebelum deadline).
     */
    public function update(Request $request, Submission $submission): SubmissionResource
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($submission->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'file_path'     => ['sometimes', 'string'],
            'original_name' => ['sometimes', 'string'],
            'file_size'     => ['sometimes', 'integer'],
            'note'          => ['nullable', 'string'],
        ]);

        $submission->update($validated);
        $submission->load(['student', 'grade']);
        return new SubmissionResource($submission);
    }

    /**
     * Mahasiswa menarik submission.
     */
    public function destroy(Submission $submission): JsonResponse
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($submission->user_id === auth()->id(), 403);

        $submission->delete();
        return response()->json(null, 204);
    }
}
