<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Resources\CourseResource;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::with('lecturer')->withCount('materials')->paginate();
        return CourseResource::collection($courses);
    }

    public function store(StoreCourseRequest $request)
    {
        abort_unless($request->user()->role === 'admin' || $request->user()->role === 'dosen', 403, 'This action is unauthorized.');
        
        // TODO(minggu 7): pindahkan ke Policy
        $data = $request->validated();
        if ($request->user()->role === 'dosen') {
            $data['lecturer_id'] = $request->user()->id;
        }

        $course = Course::create($data);
        return new CourseResource($course);
    }

    public function show(Course $course)
    {
        $course->load('lecturer');
        return new CourseResource($course);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($request->user()->role === 'admin' || ($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id), 403, 'This action is unauthorized.');
        
        $course->update($request->validated());
        return new CourseResource($course);
    }

    public function destroy(Request $request, Course $course)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($request->user()->role === 'admin' || ($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id), 403, 'This action is unauthorized.');
        
        $course->delete();
        return response()->json(null, 204);
    }
}