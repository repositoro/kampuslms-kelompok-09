<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Tampilkan daftar seluruh mata kuliah (admin).
     */
    public function index(Request $request): View
    {
        $query = Course::with('lecturer')->latest();

        if ($request->filled('search')) {
            $cari = $request->search;
            $query->where(function ($q) use ($cari) {
                $q->where('code', 'like', '%' . $cari . '%')
                  ->orWhere('name', 'like', '%' . $cari . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $courses = $query->paginate(15)->withQueryString();

        return view('admin.courses.index', compact('courses'));
    }

    /**
     * Tampilkan formulir tambah mata kuliah.
     */
    public function create(): View
    {
        $lecturers = User::where('role', 'dosen')->get();

        return view('admin.courses.create', compact('lecturers'));
    }

    /**
     * Simpan mata kuliah baru.
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        Course::create($request->all());

        return redirect()->route('admin.courses.index')
            ->with('success', 'Mata Kuliah berhasil ditambahkan!');
    }

    /**
     * Tampilkan detail mata kuliah.
     */
    public function show(Course $course): View
    {
        $course->load('lecturer', 'materials', 'assignments');

        return view('admin.courses.show', compact('course'));
    }

    /**
     * Tampilkan formulir edit mata kuliah.
     */
    public function edit(Course $course): View
    {
        $lecturers = User::where('role', 'dosen')->get();

        return view('admin.courses.edit', compact('course', 'lecturers'));
    }

    /**
     * Perbarui data mata kuliah.
     */
    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->validated());

        return redirect()->route('admin.courses.index')
            ->with('success', 'Mata Kuliah berhasil diperbarui!');
    }

    /**
     * Hapus mata kuliah.
     */
    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Mata Kuliah berhasil dihapus!');
    }
}
