<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Assignment $assignment)
    {
        // TODO(minggu 7): pindahkan ke Policy
        $submissions = $assignment->submissions()->where('user_id', auth()->id())->get();
        return view('mahasiswa.submissions.index', compact('assignment', 'submissions'));
    }

    public function create(Assignment $assignment)
    {
        // TODO(minggu 7): pindahkan ke Policy
        return view('mahasiswa.submissions.create', compact('assignment'));
    }

    public function store(Request $request, Assignment $assignment)
    {
        // TODO(minggu 7): pindahkan ke Policy
        $validated = $request->validate(['file_path' => 'required']);
        $validated['user_id'] = auth()->id();
        $assignment->submissions()->create($validated);
        
        return redirect()->route('mahasiswa.assignments.submissions.index', $assignment);
    }

    public function show(Submission $submission)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($submission->user_id === auth()->id(), 403);
        return view('mahasiswa.submissions.show', compact('submission'));
    }

    public function edit(Submission $submission)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($submission->user_id === auth()->id(), 403);
        return view('mahasiswa.submissions.edit', compact('submission'));
    }

    public function update(Request $request, Submission $submission)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($submission->user_id === auth()->id(), 403);
        
        $validated = $request->validate(['file_path' => 'required']);
        $submission->update($validated);
        
        return redirect()->route('mahasiswa.assignments.submissions.index', $submission->assignment_id);
    }

    public function destroy(Submission $submission)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($submission->user_id === auth()->id(), 403);
        
        $submission->delete();
        
        return redirect()->route('mahasiswa.assignments.submissions.index', $submission->assignment_id);
    }
}
