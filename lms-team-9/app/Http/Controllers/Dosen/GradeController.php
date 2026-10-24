<?php

namespace App\Http\Controllers\Dosen;

use App\Events\GradeGiven;
use App\Http\Controllers\Controller;
use App\Http\Requests\GradeRequest;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Support\Facades\Gate;

class GradeController extends Controller
{
    public function update(GradeRequest $request, Submission $submission)
    {
        // Otorisasi Dosen
        Gate::authorize('create', [Grade::class, $submission]);

        $data = $request->validated();

        // Gunakan relasi $submission->grade() agar lebih simetris dan rapi
        $grade = $submission->grade()->updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by' => $request->user()->id,
                'score'     => $data['score'],
                'feedback'  => $data['feedback'] ?? null,
                'graded_at' => now(),
            ]
        );

        GradeGiven::dispatch($grade);

        // Gunakan back() agar dosen tetap berada di halaman tempat dia mengisi nilai 
        // (baik dari halaman detail tugas maupun halaman monitoring pengumpulan)
        return back()->with('success', 'Nilai berhasil disimpan.');
    }
}