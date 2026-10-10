<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = match ($request->user()->role) {
            'admin' => Course::query(),
            'dosen' => $request->user()->taughtCourses(),
            'mahasiswa' => $request->user()->courses(),
            default => abort(403),
        };

        $courses = $courses->with('lecturer')->withCount(['materials', 'assignments'])->paginate();

        return CourseResource::collection($courses);
    }

    public function store(StoreCourseRequest $request)
    {
        Gate::authorize('create', Course::class);
        $course = Course::create($request->validated());

        return new CourseResource($course);
    }

    public function show(Course $course)
    {
        Gate::authorize('view', $course);
        $course->load('lecturer')->loadCount(['materials', 'assignments']);

        return new CourseResource($course);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        Gate::authorize('update', $course);
        $course->update($request->validated());

        return new CourseResource($course);
    }

    public function destroy(Request $request, Course $course)
    {
        Gate::authorize('delete', $course);
        $course->delete();

        return response()->json(null, 204);
    }
}
