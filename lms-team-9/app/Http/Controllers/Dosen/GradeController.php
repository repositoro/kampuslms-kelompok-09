<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeRequest;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Support\Facades\Gate;

class GradeController extends Controller
{
    public function update(GradeRequest $request, Submission $submission)
    {
        Gate::authorize('create', [Grade::class, $submission]);
        $data = $request->validated();

        Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by' => $request->user()->id,
                'score' => $data['score'],
                'feedback' => $data['feedback'] ?? null,
                'graded_at' => now(),
            ],
        );

        return redirect()->route('dosen.assignments.show', $submission->assignment)
            ->with('success', 'Nilai berhasil disimpan.');
    }
}
