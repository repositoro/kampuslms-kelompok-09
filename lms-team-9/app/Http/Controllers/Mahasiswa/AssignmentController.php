<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    /**
     * Tampilkan daftar tugas dalam mata kuliah yang diikuti.
     */
    public function index(Request $request, Course $course): View
    {
        abort_unless(
            $request->user()->courses()->where('courses.id', $course->id)->exists(),
            403
        );

        $assignments = $course->assignments()
            ->where('status', 'published')
            ->latest()
            ->paginate(15);

        return view('mahasiswa.assignments.index', compact('course', 'assignments'));
    }

    /**
     * Tampilkan detail tugas.
     * Shallow: diakses langsung via assignment ID.
     */
    public function show(Request $request, Assignment $assignment): View
    {
        abort_unless(
            $request->user()->courses()->where('courses.id', $assignment->course_id)->exists(),
            403
        );

        // Ambil submission milik mahasiswa ini untuk tugas ini (jika ada)
        $mySubmission = $assignment->submissions()
            ->where('user_id', $request->user()->id)
            ->with('grade')
            ->first();

        $assignment->load('course');

        return view('mahasiswa.assignments.show', compact('assignment', 'mySubmission'));
    }
}
