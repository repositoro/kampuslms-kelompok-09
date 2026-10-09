<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Http\Resources\SubmissionResource;
use App\Http\Requests\StoreSubmissionRequest;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Assignment $assignment)
    {
        $submissions = $assignment->submissions()->with(['user', 'grade'])->paginate();
        return SubmissionResource::collection($submissions);
    }

    public function store(StoreSubmissionRequest $request, Assignment $assignment)
    {
        abort_unless($request->user()->role === 'mahasiswa', 403, 'This action is unauthorized.');
        
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $submission = $assignment->submissions()->create($data);
        return new SubmissionResource($submission);
    }

    public function show(Request $request, Submission $submission)
    {
        $course = $submission->assignment->course;
        abort_unless(
            $request->user()->role === 'admin' || 
            ($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id) ||
            ($request->user()->role === 'mahasiswa' && $submission->user_id === $request->user()->id),
            403, 'This action is unauthorized.'
        );
        $submission->load(['user', 'grade']);
        return new SubmissionResource($submission);
    }
}