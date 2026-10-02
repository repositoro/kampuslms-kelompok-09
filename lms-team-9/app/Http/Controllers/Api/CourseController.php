<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Resources\CourseResource;
use App\Http\Resources\MaterialResource;
use App\Http\Resources\AssignmentResource;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'dosen') {
            $courses = Course::where('lecturer_id', $user->id)
                ->with(['lecturer'])
                ->withCount(['materials', 'assignments'])
                ->get();
        } else {
            $courses = $user->courses()
                ->with(['lecturer'])
                ->withCount(['materials', 'assignments'])
                ->get();
        }
        return CourseResource::collection($courses);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $course = Course::with(['lecturer'])->withCount(['materials', 'assignments'])->findOrFail($id);

        if ($user->role === 'dosen' && $course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        if ($user->role === 'mahasiswa' && !$course->students()->where('user_id', $user->id)->exists()) {
             return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        return new CourseResource($course);
    }

    public function materials(Request $request, $id)
    {
        $user = $request->user();
        $course = Course::findOrFail($id);

        if ($user->role === 'dosen' && $course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        if ($user->role === 'mahasiswa' && !$course->students()->where('user_id', $user->id)->exists()) {
             return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $materials = $course->materials()->with('uploader')->get();
        return MaterialResource::collection($materials);
    }

    public function assignments(Request $request, $id)
    {
        $user = $request->user();
        $course = Course::findOrFail($id);

        if ($user->role === 'dosen' && $course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        if ($user->role === 'mahasiswa' && !$course->students()->where('user_id', $user->id)->exists()) {
             return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $query = $course->assignments();

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $assignments = $query->paginate(10);
        return AssignmentResource::collection($assignments);
    }
}
