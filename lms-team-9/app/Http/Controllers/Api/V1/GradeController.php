<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeRequest;
use App\Http\Resources\GradeResource;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GradeController extends Controller
{
    public function store(GradeRequest $request, Submission $submission)
    {
        return $this->upsert($request, $submission);
    }

    public function update(GradeRequest $request, Submission $submission)
    {
        return $this->upsert($request, $submission);
    }

    private function upsert(GradeRequest $request, Submission $submission)
    {
        Gate::authorize('create', [Grade::class, $submission]);
        $created = ! $submission->grade()->exists();
        $data = $request->validated();

        $grade = Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by' => $request->user()->id,
                'score' => $data['score'],
                'feedback' => $data['feedback'] ?? null,
                'graded_at' => now(),
            ],
        );

        \App\Events\GradeRecorded::dispatch($grade);

        return (new GradeResource($grade))
            ->response()
            ->setStatusCode($created ? 201 : 200);
    }

    public function show(Request $request, Submission $submission)
    {
        $grade = $submission->grade;
        if (! $grade) {
            abort(404);
        }
        Gate::authorize('view', $grade);

        return new GradeResource($grade);
    }
}
