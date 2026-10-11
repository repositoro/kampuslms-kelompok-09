<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Submission;
use App\Services\SubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SubmissionController extends Controller
{
    public function index(Assignment $assignment)
    {
        Gate::authorize('viewAny', [Submission::class, $assignment]);
        $submissions = $assignment->submissions()->with(['student', 'grade'])->paginate();

        return SubmissionResource::collection($submissions);
    }

    public function store(
        StoreSubmissionRequest $request,
        Assignment $assignment,
        SubmissionService $submissionService,
    ) {
        Gate::authorize('create', [Submission::class, $assignment]);

        [$submission, $created] = $submissionService->store(
            $assignment,
            $request->user(),
            $request->file('file'),
            $request->validated('note'),
        );

        return (new SubmissionResource($submission))
            ->response()
            ->setStatusCode($created ? 201 : 200);
    }

    public function show(Request $request, Submission $submission)
    {
        Gate::authorize('view', $submission);
        $submission->load(['student', 'grade']);

        return new SubmissionResource($submission);
    }
}
