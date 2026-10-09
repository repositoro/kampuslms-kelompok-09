<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Grade;
use App\Http\Resources\GradeResource;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function store(StoreGradeRequest $request, Submission $submission)
    {
        $course = $submission->assignment->course;
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $data = $request->validated();
        $data['graded_by'] = $request->user()->id;
        $grade = $submission->grade()->create($data);
        return new GradeResource($grade);
    }

    public function update(UpdateGradeRequest $request, Submission $submission)
    {
        $course = $submission->assignment->course;
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $grade = $submission->grade;
        if (!$grade) abort(404);
        
        $grade->update($request->validated());
        return new GradeResource($grade);
    }

    public function show(Request $request, Submission $submission)
    {
        $grade = $submission->grade;
        if (!$grade) abort(404);
        return new GradeResource($grade);
    }
}