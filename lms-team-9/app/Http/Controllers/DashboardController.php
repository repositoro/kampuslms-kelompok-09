<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'admin'     => $this->adminDashboard(),
            'dosen'     => $this->dosenDashboard($user),
            'mahasiswa' => $this->mahasiswaDashboard($user),
            default     => abort(403),
        };
    }

    private function adminDashboard()
    {
        $stats = [
            'total_users'   => User::count(),
            'total_courses' => Course::count(),
            'total_dosen'   => User::where('role', 'dosen')->count(),
            'total_mhs'     => User::where('role', 'mahasiswa')->count(),
        ];

        return view('dashboard.admin', compact('stats'));
    }

    private function dosenDashboard(User $user)
    {
        // Ambil daftar mata kuliah diampu beserta jumlah mahasiswa & tugas
        $courses = $user->taughtCourses()
            ->withCount(['students', 'assignments'])
            ->get();

        // Jumlah submission tugas mahasiswa yang belum diberi nilai
        $pendingGradesCount = Submission::whereHas('assignment.course', function ($q) use ($user) {
            $q->where('lecturer_id', $user->id);
        })->doesntHave('grade')->count();

        return view('dashboard.dosen', compact('courses', 'pendingGradesCount'));
    }

    private function mahasiswaDashboard(User $user)
    {
        // Ambil mata kuliah yang diikuti
        $courses = $user->courses()->with('lecturer')->get();

        // Ambil 5 tugas mendatang dari mata kuliah yang diikuti
        $upcomingAssignments = Assignment::whereHas('course.students', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })
        ->where('status', 'published')
        ->where('due_at', '>=', now())
        ->orderBy('due_at', 'asc')
        ->take(5)
        ->get();

        return view('dashboard.mahasiswa', compact('courses', 'upcomingAssignments'));
    }
}