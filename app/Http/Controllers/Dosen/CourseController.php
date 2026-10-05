<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Tampilkan daftar mata kuliah yang diampu oleh dosen yang sedang login.
     */
    public function index(Request $request): View
    {
        $query = Course::where('lecturer_id', $request->user()->id)->latest();

        if ($request->filled('search')) {
            $cari = $request->search;
            $query->where(function ($q) use ($cari) {
                $q->where('code', 'like', '%' . $cari . '%')
                  ->orWhere('name', 'like', '%' . $cari . '%');
            });
        }

        $courses = $query->paginate(15)->withQueryString();

        return view('dosen.courses.index', compact('courses'));
    }

    /**
     * Tampilkan detail mata kuliah.
     * abort_unless: dosen hanya bisa melihat mata kuliah yang dia ampu.
     */
    public function show(Request $request, Course $course): View
    {
        // Pemeriksaan kepemilikan sementara — akan diganti Policy di minggu 7
        abort_unless($course->lecturer_id === $request->user()->id, 403);

        $course->load('materials', 'assignments', 'students');

        return view('dosen.courses.show', compact('course'));
    }
}
