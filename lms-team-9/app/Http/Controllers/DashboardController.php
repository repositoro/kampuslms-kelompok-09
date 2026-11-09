<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use App\Support\DashboardCache;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'admin' => $this->adminDashboard(),
            'dosen' => $this->dosenDashboard($user),
            'mahasiswa' => $this->mahasiswaDashboard($user),
            default => abort(403),
        };
    }

    private function adminDashboard()
    {
        $stats = DashboardCache::remember('admin:stats', fn () => [
            'total_users' => User::count(),
            'total_courses' => Course::count(),
            'total_dosen' => User::where('role', 'dosen')->count(),
            'total_mhs' => User::where('role', 'mahasiswa')->count(),
        ]);

        return view('dashboard.admin', compact('stats'));
    }

    private function dosenDashboard(User $user)
    {
        // Ambil daftar mata kuliah diampu beserta jumlah mahasiswa & tugas
        $coursesPage = request()->integer('courses_page', 1);
        $courses = DashboardCache::remember(
            "lecturer:{$user->id}:courses:{$coursesPage}",
            fn () => $user->taughtCourses()
                ->withCount(['students', 'assignments'])
                ->orderBy('name')
                ->paginate(10, ['*'], 'courses_page')
                ->withQueryString(),
        );

        // Jumlah submission tugas mahasiswa yang belum diberi nilai
        $pendingGradesCount = DashboardCache::remember(
            "lecturer:{$user->id}:pending-grades",
            fn () => Submission::whereHas('assignment.course', function ($q) use ($user) {
                $q->where('lecturer_id', $user->id);
            })->doesntHave('grade')->count(),
        );

        return view('dashboard.dosen', compact('courses', 'pendingGradesCount'));
    }

    private function mahasiswaDashboard(User $user)
    {
        // Ambil mata kuliah yang diikuti
        $coursesPage = request()->integer('courses_page', 1);
        $courses = DashboardCache::remember(
            "student:{$user->id}:courses:{$coursesPage}",
            fn () => $user->courses()
                ->with('lecturer')
                ->orderBy('courses.name')
                ->paginate(10, ['courses.*'], 'courses_page')
                ->withQueryString(),
        );

        // Ambil 5 tugas mendatang dari mata kuliah yang diikuti
        $upcomingAssignments = Assignment::whereHas('course.students', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })
            ->with([
                'course',
                'submissions' => fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->with('grade'),
            ])
            ->where('status', 'published')
            ->where('due_at', '>=', now())
            ->orderBy('due_at', 'asc')
            ->take(5)
            ->get();

        return view('dashboard.mahasiswa', compact('courses', 'upcomingAssignments'));
    }
}
