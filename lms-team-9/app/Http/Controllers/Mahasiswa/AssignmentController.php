<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    public function show(Assignment $assignment)
    {
        // Otorisasi: Pastikan mahasiswa terdaftar di mata kuliah terkait
        Gate::authorize('view', $assignment);

        // Load submission milik mahasiswa yang sedang login beserta nilainya
        $assignment->load(['course', 'submissions' => function ($query) {
            $query->where('user_id', auth()->id())->with('grade');
        }]);

        return view('mahasiswa.assignments.show', compact('assignment'));
    }
}