<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Course::with('lecturer')->latest();

        // Filter pencarian berdasarkan kode atau nama
        if ($request->filled('search')) {
            $cari = $request->search;
            $query->where(function ($q) use ($cari) {
                $q->where('code', 'like', '%' . $cari . '%')
                  ->orWhere('name', 'like', '%' . $cari . '%');
            });
        }

        // Filter status (draft / active / archived)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Paginate 15 per halaman, pertahankan query string (search & status)
        $courses = $query->paginate(15)->withQueryString();

        return view('courses.index', compact('courses'));
    }

    public function create(): View
    {
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.create', compact('lecturers'));
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        Course::create($request->validated());

        return redirect()->route('courses.index')
            ->with('success', 'Mata Kuliah berhasil ditambahkan!');
    }

    public function show(Course $course): View
    {
        $course->load('lecturer');

        return view('courses.show', compact('course'));
    }

    public function edit(Course $course): View
    {
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.edit', compact('course', 'lecturers'));
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->validated());

        return redirect()->route('courses.index')
            ->with('success', 'Mata Kuliah berhasil diperbarui!');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('courses.index')
            ->with('success', 'Mata Kuliah berhasil dihapus!');
    }
}