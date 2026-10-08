<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseController extends Controller
{
    /**
     * Daftar semua mata kuliah (publik bagi yang sudah login).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $courses = Course::with('lecturer')
            ->withCount(['materials', 'assignments', 'students'])
            ->latest()
            ->paginate(15);

        return CourseResource::collection($courses);
    }

    /**
     * Detail satu mata kuliah.
     */
    public function show(Course $course): CourseResource
    {
        $course->loadCount(['materials', 'assignments', 'students'])->load('lecturer');
        return new CourseResource($course);
    }

    /**
     * Buat mata kuliah baru (hanya admin).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'        => ['required', 'string', 'max:20', 'unique:courses,code'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sks'         => ['required', 'integer', 'min:1', 'max:6'],
            'lecturer_id' => ['required', 'exists:users,id'],
            'status'      => ['required', 'in:draft,active,archived'],
        ]);

        $course = Course::create($validated);
        $course->load('lecturer');

        return response()->json(['data' => new CourseResource($course)], 201);
    }

    /**
     * Perbarui mata kuliah (hanya admin).
     */
    public function update(Request $request, Course $course): CourseResource
    {
        $validated = $request->validate([
            'code'        => ['sometimes', 'string', 'max:20', 'unique:courses,code,'.$course->id],
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sks'         => ['sometimes', 'integer', 'min:1', 'max:6'],
            'lecturer_id' => ['sometimes', 'exists:users,id'],
            'status'      => ['sometimes', 'in:draft,active,archived'],
        ]);

        $course->update($validated);
        $course->load('lecturer');

        return new CourseResource($course);
    }

    /**
     * Hapus mata kuliah (hanya admin).
     */
    public function destroy(Course $course): JsonResponse
    {
        $course->delete();
        return response()->json(null, 204);
    }
}
