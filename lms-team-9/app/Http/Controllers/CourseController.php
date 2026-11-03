<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Penyaringan di level query menggunakan relasi Bagian 4.3
        $courses = (match ($user->role) {
            'admin' => Course::query(),
            'dosen' => $user->taughtCourses(),
            'mahasiswa' => $user->courses(),
            default => abort(403),
        })->with('lecturer')->paginate(15)->withQueryString();

        return view('courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        Gate::authorize('view', $course);

        $user = request()->user();
        $course->load('lecturer');
        $canManageEnrollment = Gate::allows('manageEnrollment', $course);
        $students = $canManageEnrollment
            ? $course->students()
                ->where('role', 'mahasiswa')
                ->orderBy('name')
                ->paginate(10, ['users.id', 'users.name', 'users.email'], 'students_page')
                ->withQueryString()
            : collect();
        $availableStudents = $canManageEnrollment
            ? User::query()
                ->where('role', 'mahasiswa')
                ->whereDoesntHave('courses', fn ($query) => $query->where('courses.id', $course->id))
                ->orderBy('name')
                ->paginate(10, ['id', 'name', 'email'], 'available_students_page')
                ->withQueryString()
            : collect();
        $materials = $course->materials()
            ->latest()
            ->paginate(10, ['*'], 'materials_page')
            ->withQueryString();
        $assignmentsQuery = $course->assignments();
        if ($user->role === 'mahasiswa') {
            $assignmentsQuery
                ->where('status', 'published')
                ->with(['submissions' => fn ($submissions) => $submissions
                    ->where('user_id', $user->id)
                    ->with('grade')]);
        }
        $assignments = $assignmentsQuery
            ->latest()
            ->paginate(10, ['*'], 'assignments_page')
            ->withQueryString();

        return view('courses.show', compact(
            'course',
            'canManageEnrollment',
            'availableStudents',
            'students',
            'materials',
            'assignments',
        ));
    }

    public function create()
    {
        Gate::authorize('create', Course::class);
        $lecturers = User::where('role', 'dosen')->orderBy('name')
            ->paginate(15, ['id', 'name'], 'lecturers_page')
            ->withQueryString();

        return view('courses.create', compact('lecturers'));
    }

    public function store(StoreCourseRequest $request)
    {
        Gate::authorize('create', Course::class);
        $course = Course::create($request->validated());

        return redirect()->route('courses.show', $course);
    }

    public function edit(Course $course)
    {
        Gate::authorize('update', $course);
        $lecturers = User::where('role', 'dosen')->orderBy('name')
            ->paginate(15, ['id', 'name'], 'lecturers_page')
            ->withQueryString();

        return view('courses.edit', compact('course', 'lecturers'));
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        Gate::authorize('update', $course);
        $course->update($request->validated());

        return redirect()->route('courses.show', $course);
    }

    public function destroy(Course $course)
    {
        Gate::authorize('delete', $course);
        $course->delete();

        return redirect()->route('courses.index');
    }
}
