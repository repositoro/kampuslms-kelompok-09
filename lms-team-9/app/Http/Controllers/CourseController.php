<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Penyaringan di level query menggunakan relasi Bagian 4.3
        $courses = (match ($user->role) {
            'admin'     => Course::query(),
            'dosen'     => $user->taughtCourses(),
            'mahasiswa' => $user->courses(),
            default     => abort(403),
        })->with('lecturer')->paginate(15);

        return view('courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        Gate::authorize('view', $course);
        return view('courses.show', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        Gate::authorize('update', $course);
        $course->update($request->all());
        return redirect()->route('courses.show', $course);
    }

    public function destroy(Course $course)
    {
        Gate::authorize('delete', $course);
        $course->delete();
        return redirect()->route('courses.index');
    }
}