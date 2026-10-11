<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    public function index(Course $course)
    {
        Gate::authorize('view', $course);
        $assignments = $course->assignments();

        if (request()->user()->role === 'mahasiswa') {
            $assignments->where('status', 'published');
        }

        $assignments = $assignments->paginate();

        return AssignmentResource::collection($assignments);
    }

    public function store(StoreAssignmentRequest $request, Course $course)
    {
        $assignment = $course->assignments()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return new AssignmentResource($assignment);
    }

    public function show(Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        return new AssignmentResource($assignment);
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    {
        $assignment->update($request->validated());

        return new AssignmentResource($assignment);
    }

    public function destroy(Assignment $assignment)
    {
        Gate::authorize('deleteApi', $assignment);

        $assignment->delete();

        return response()->json(null, 204);
    }
}
