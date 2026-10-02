<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Grade;
use Illuminate\Http\Request;
use App\Http\Resources\GradeResource;

class GradeController extends Controller
{
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'dosen') {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $submission = Submission::findOrFail($id);
        $assignment = $submission->assignment;

        if ($assignment->created_by !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $validated = $request->validate([
            'score' => 'required|numeric',
            'feedback' => 'nullable|string',
        ]);

        $exists = Grade::where('submission_id', $submission->id)->exists();

        $grade = Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'score' => $validated['score'],
                'feedback' => $validated['feedback'] ?? null,
                'graded_by' => $user->id
            ]
        );

        $status = $exists ? 200 : 201;

        return response()->json([
            'data' => new GradeResource($grade->load('grader'))
        ], $status);
    }
}
