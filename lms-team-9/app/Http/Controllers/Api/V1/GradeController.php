<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GradeResource;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GradeController extends Controller
{
    /**
     * Dosen memberi nilai pada submission.
     */
    public function store(Request $request, Submission $submission): JsonResponse
    {
        // TODO(minggu 7): pindahkan ke Policy
        // Dosen hanya boleh menilai submission pada assignment miliknya
        abort_unless($submission->assignment->course->lecturer_id === auth()->id(), 403);

        $validated = $request->validate([
            'score'     => ['required', 'numeric', 'min:0', 'max:' . $submission->assignment->max_score],
            'feedback'  => ['nullable', 'string'],
            'graded_at' => ['required', 'date'],
        ]);

        $validated['graded_by']    = auth()->id();
        $validated['submission_id']= $submission->id;

        $grade = Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            $validated
        );

        $grade->load('grader');
        return response()->json(['data' => new GradeResource($grade)], 201);
    }

    /**
     * Perbarui nilai.
     */
    public function update(Request $request, Submission $submission): GradeResource
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($submission->assignment->course->lecturer_id === auth()->id(), 403);

        $grade = $submission->grade;
        abort_unless($grade !== null, 404);

        $validated = $request->validate([
            'score'     => ['sometimes', 'numeric', 'min:0', 'max:' . $submission->assignment->max_score],
            'feedback'  => ['nullable', 'string'],
            'graded_at' => ['sometimes', 'date'],
        ]);

        $grade->update($validated);
        $grade->load('grader');
        return new GradeResource($grade);
    }

    /**
     * Lihat nilai satu submission.
     */
    public function show(Submission $submission): GradeResource
    {
        $user = auth()->user();

        // TODO(minggu 7): pindahkan ke Policy
        if ($user->role === 'mahasiswa') {
            abort_unless($submission->user_id === $user->id, 403);
        } else {
            abort_unless($submission->assignment->course->lecturer_id === $user->id, 403);
        }

        $grade = $submission->grade;
        abort_unless($grade !== null, 404);
        $grade->load('grader');

        return new GradeResource($grade);
    }
}
