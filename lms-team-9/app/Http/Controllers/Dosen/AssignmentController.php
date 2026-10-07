<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Assignment;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Course $course)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($course->lecturer_id === auth()->id(), 403);
        $assignments = $course->assignments;
        return view('dosen.assignments.index', compact('course', 'assignments'));
    }

    public function create(Course $course)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($course->lecturer_id === auth()->id(), 403);
        return view('dosen.assignments.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($course->lecturer_id === auth()->id(), 403);
        
        $validated = $request->validate(['title' => 'required']);
        $course->assignments()->create($validated);
        
        return redirect()->route('dosen.courses.assignments.index', $course);
    }

    public function show(Assignment $assignment)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);
        return view('dosen.assignments.show', compact('assignment'));
    }

    public function edit(Assignment $assignment)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);
        return view('dosen.assignments.edit', compact('assignment'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);
        
        $validated = $request->validate(['title' => 'required']);
        $assignment->update($validated);
        
        return redirect()->route('dosen.courses.assignments.index', $assignment->course_id);
    }

    public function destroy(Assignment $assignment)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);
        
        $assignment->delete();
        
        return redirect()->route('dosen.courses.assignments.index', $assignment->course_id);
    }
}
