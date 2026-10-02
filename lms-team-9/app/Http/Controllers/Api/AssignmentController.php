<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use Illuminate\Http\Request;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\SubmissionResource;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'dosen') {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string',
            'instructions' => 'nullable|string',
            'due_at' => 'required|date',
            'max_score' => 'required|numeric',
            'allow_late' => 'boolean',
            'status' => 'required|in:draft,published',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        if ($course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $assignment = Assignment::create(array_merge($validated, [
            'created_by' => $user->id
        ]));

        return new AssignmentResource($assignment);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $assignment = Assignment::findOrFail($id);
        
        if ($user->role !== 'dosen' || $assignment->created_by !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string',
            'instructions' => 'nullable|string',
            'due_at' => 'sometimes|required|date',
            'max_score' => 'sometimes|required|numeric',
            'allow_late' => 'boolean',
            'status' => 'sometimes|required|in:draft,published',
        ]);

        $assignment->update($validated);
        return new AssignmentResource($assignment);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $assignment = Assignment::findOrFail($id);
        
        if ($user->role !== 'dosen' || $assignment->created_by !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $assignment->delete();
        return response()->json(['message' => 'Berhasil dihapus']);
    }

    public function submissions(Request $request, $id)
    {
        $user = $request->user();
        $assignment = Assignment::findOrFail($id);
        
        if ($user->role !== 'dosen' || $assignment->created_by !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $submissions = $assignment->submissions()->with(['student', 'grade.grader'])->paginate(10);
        return SubmissionResource::collection($submissions);
    }

    public function submit(Request $request, $id)
    {
        $user = $request->user();
        $assignment = Assignment::findOrFail($id);

        if ($user->role !== 'mahasiswa') {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $course = $assignment->course;
        if (!$course->students()->where('user_id', $user->id)->exists()) {
             return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $request->validate([
            'file' => 'required|file',
            'note' => 'nullable|string'
        ]);

        $path = $request->file('file')->store('submissions', 'public');
        $isLate = now()->greaterThan($assignment->due_at);

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'file_path' => $path,
            'original_name' => $request->file('file')->getClientOriginalName(),
            'file_size' => $request->file('file')->getSize(),
            'note' => $request->input('note'),
            'submitted_at' => now(),
            'is_late' => $isLate,
        ]);

        return new SubmissionResource($submission->load('student'));
    }
}
