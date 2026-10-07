<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Course $course)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($course->lecturer_id === auth()->id(), 403);
        $materials = $course->materials;
        return view('dosen.materials.index', compact('course', 'materials'));
    }

    public function create(Course $course)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($course->lecturer_id === auth()->id(), 403);
        return view('dosen.materials.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($course->lecturer_id === auth()->id(), 403);
        
        $validated = $request->validate(['title' => 'required']);
        $course->materials()->create($validated);
        
        return redirect()->route('dosen.courses.materials.index', $course);
    }

    public function show(Material $material)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($material->course->lecturer_id === auth()->id(), 403);
        return view('dosen.materials.show', compact('material'));
    }

    public function edit(Material $material)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($material->course->lecturer_id === auth()->id(), 403);
        return view('dosen.materials.edit', compact('material'));
    }

    public function update(Request $request, Material $material)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($material->course->lecturer_id === auth()->id(), 403);
        
        $validated = $request->validate(['title' => 'required']);
        $material->update($validated);
        
        return redirect()->route('dosen.courses.materials.index', $material->course_id);
    }

    public function destroy(Material $material)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($material->course->lecturer_id === auth()->id(), 403);
        
        $material->delete();
        
        return redirect()->route('dosen.courses.materials.index', $material->course_id);
    }
}
