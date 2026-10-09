<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Assignment;
use App\Http\Resources\AssignmentResource;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Course $course)
    {
        $assignments = $course->assignments()->paginate();
        return AssignmentResource::collection($assignments);
    }

    public function store(StoreAssignmentRequest $request, Course $course)
    {
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $assignment = $course->assignments()->create($request->validated());
        return new AssignmentResource($assignment);
    }

    public function show(Assignment $assignment)
    {
        return new AssignmentResource($assignment);
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    {
        $course = $assignment->course;
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $assignment->update($request->validated());
        return new AssignmentResource($assignment);
    }

    public function destroy(Request $request, Assignment $assignment)
    {
        $course = $assignment->course;
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $assignment->delete();
        return response()->json(null, 204);
    }
}