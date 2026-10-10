<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GradeSummaryController extends Controller
{
    public function course(Course $course)
    {
        Gate::authorize('viewCourse', [Grade::class, $course]);

        $grades = Grade::with(['submission.student', 'submission.assignment'])
            ->whereHas('submission.assignment', fn ($query) => $query->where('course_id', $course->id))
            ->latest('graded_at')
            ->get();

        return view('dosen.grades.index', compact('course', 'grades'));
    }

    public function student(Request $request)
    {
        $grades = Grade::with(['submission.assignment.course'])
            ->whereHas('submission', fn ($query) => $query->where('user_id', $request->user()->id))
            ->latest('graded_at')
            ->get();

        return view('mahasiswa.grades.index', compact('grades'));
    }
}
