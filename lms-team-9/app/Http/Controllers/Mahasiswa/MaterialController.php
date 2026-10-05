<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    /**
     * Tampilkan daftar materi dalam mata kuliah yang diikuti.
     */
    public function index(Request $request, Course $course): View
    {
        // Pemeriksaan kepemilikan sementara — akan diganti Policy di minggu 7
        abort_unless(
            $request->user()->courses()->where('courses.id', $course->id)->exists(),
            403
        );

        $materials = $course->materials()->latest()->paginate(15);

        return view('mahasiswa.materials.index', compact('course', 'materials'));
    }

    /**
     * Tampilkan detail materi.
     * Shallow: diakses langsung via material ID.
     */
    public function show(Request $request, Material $material): View
    {
        abort_unless(
            $request->user()->courses()->where('courses.id', $material->course_id)->exists(),
            403
        );

        $material->load('course', 'uploader');

        return view('mahasiswa.materials.show', compact('material'));
    }
}
