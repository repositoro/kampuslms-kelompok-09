<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    public function index(Course $course)
    {
        Gate::authorize('view', $course);
        $user = auth()->user();
        $assignments = $course->assignments()->with('course');
        if ($user->role === 'mahasiswa') {
            // Mahasiswa hanya melihat tugas published. Jumlah pengumpulan yang
            // ditampilkan juga dibatasi pada pengumpulan miliknya sendiri.
            $assignments
                ->where('status', 'published')
                ->with(['submissions' => fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->with('grade')])
                ->withCount(['submissions' => fn ($query) => $query->where('user_id', $user->id)]);
        } else {
            $assignments->withCount(['submissions', 'grades']);
        }
        $assignments = $assignments->latest()->paginate(15);

        return view('dosen.assignments.index', compact('course', 'assignments'));
    }

    // Menampilkan formulir pembuat tugas baru
    public function create(Course $course)
    {
        // Otorisasi: Dosen hanya bisa menambah tugas di kelasnya sendiri
        Gate::authorize('create', [Assignment::class, $course]);

        return view('dosen.assignments.create', compact('course'));
    }

    // Menyimpan data tugas perkuliahan (metadata) ke database
    public function store(StoreAssignmentRequest $request, Course $course)
    {
        $course->assignments()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('courses.show', $course)
            ->with('success', 'Tugas berhasil dibuat.');
    }

    // Menampilkan detail tugas beserta pengumpulan (submissions) mahasiswa
    public function show(Assignment $assignment)
    {
        Gate::authorize('view', $assignment);
        $assignment->load('course');
        $user = auth()->user();
        $submission = null;
        $canSubmit = false;

        if ($user->role === 'mahasiswa') {
            $submission = $assignment->submissions()
                ->where('user_id', $user->id)
                ->with('grade')
                ->first();
            $canSubmit = Gate::allows('create', [Submission::class, $assignment])
                && ($assignment->allow_late || now()->lessThanOrEqualTo($assignment->due_at))
                && ! $submission?->grade;
        } else {
            $assignment->load(['submissions.student', 'submissions.grade']);
            $submissionCount = $assignment->submissions->count();
            $gradedSubmissionCount = $assignment->submissions->filter(fn ($submission) => $submission->grade !== null)->count();
        }
        $submissionCount ??= 0;
        $gradedSubmissionCount ??= 0;

        return view('dosen.assignments.show', compact(
            'assignment',
            'submission',
            'canSubmit',
            'submissionCount',
            'gradedSubmissionCount',
        ));
    }

    // Monitoring pengumpulan tugas pada satu assignment (hanya admin dan dosen pengampu)
    public function submissions(Assignment $assignment)
    {
        Gate::authorize('viewAny', [Submission::class, $assignment]);

        $assignment->load(['submissions.student', 'submissions.grade']);

        return view('dosen.assignments.submissions', compact('assignment'));
    }

    // Menampilkan formulir edit tugas (Shallow Route)
    public function edit(Assignment $assignment)
    {
        // Otorisasi IDOR: Memastikan dosen hanya bisa mengedit tugas kelas miliknya
        Gate::authorize('update', $assignment);

        return view('dosen.assignments.edit', compact('assignment'));
    }

    // Memperbarui metadata tugas perkuliahan
    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    {
        $assignment->update($request->validated());

        return redirect()->route('courses.show', $assignment->course_id)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    // Menghapus tugas perkuliahan
    public function destroy(Assignment $assignment)
    {
        // Otorisasi IDOR
        Gate::authorize('delete', $assignment);

        $courseId = $assignment->course_id;
        $assignment->delete();

        return redirect()->route('courses.show', $courseId)
            ->with('success', 'Tugas berhasil dihapus.');
    }
}
