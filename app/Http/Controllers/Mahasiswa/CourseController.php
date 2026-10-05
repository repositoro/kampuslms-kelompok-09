<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Tampilkan daftar mata kuliah yang diikuti mahasiswa.
     */
    public function index(Request $request): View
    {
        $courses = $request->user()
            ->courses()
            ->withPivot('enrolled_at')
            ->latest('course_user.enrolled_at')
            ->paginate(15);

        return view('mahasiswa.courses.index', compact('courses'));
    }

    /**
     * Tampilkan detail mata kuliah.
     * abort_unless: mahasiswa hanya bisa melihat mata kuliah yang dia ikuti.
     */
    public function show(Request $request, Course $course): View
    {
        // Pemeriksaan kepemilikan sementara — akan diganti Policy di minggu 7
        abort_unless(
            $request->user()->courses()->where('courses.id', $course->id)->exists(),
            403
        );

        $course->load('lecturer', 'materials', 'assignments');

        return view('mahasiswa.courses.show', compact('course'));
    }
}
