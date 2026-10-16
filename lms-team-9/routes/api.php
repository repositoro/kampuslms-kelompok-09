<?php

use App\Http\Controllers\Api\V1\AssignmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\GradeController;
use App\Http\Controllers\Api\V1\MaterialController;
use App\Http\Controllers\Api\V1\SubmissionController;
use App\Http\Controllers\MaterialDownloadController;
use App\Http\Controllers\SubmissionDownloadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')->name('auth.login');

    // Protected umum (semua role yang login)
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');

        // Baca course: semua role boleh
        Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

        // Nested read
        Route::scopeBindings()->group(function () {
            Route::get('/courses/{course}/materials', [MaterialController::class, 'index'])->name('courses.materials.index');
            Route::get('/materials/{material}', [MaterialController::class, 'show'])->name('materials.show');
            Route::get('/materials/{material}/download', MaterialDownloadController::class)->name('materials.download');

            Route::get('/courses/{course}/assignments', [AssignmentController::class, 'index'])->name('courses.assignments.index');
            Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');

            Route::get('/assignments/{assignment}/submissions', [SubmissionController::class, 'index'])->name('assignments.submissions.index');
            Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
        });

        Route::get('/submissions/{submission}/download', SubmissionDownloadController::class)
            ->name('submissions.download');

        Route::get('/submissions/{submission}/grade', [GradeController::class, 'show'])->name('submissions.grade.show');

        // Mahasiswa boleh mengumpulkan tugas
        Route::post('/assignments/{assignment}/submissions', [SubmissionController::class, 'store'])
            ->name('assignments.submissions.store');

        // ====== KHUSUS ADMIN ======
        Route::middleware('role:admin')->group(function () {
            Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
            Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
            Route::patch('/courses/{course}', [CourseController::class, 'update']);
            Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        });

        // ====== KHUSUS DOSEN ======
        Route::middleware('role:dosen')->group(function () {
            Route::post('/courses/{course}/materials', [MaterialController::class, 'store'])->name('courses.materials.store');
            Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
            Route::patch('/materials/{material}', [MaterialController::class, 'update']);
            Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

            Route::post('/courses/{course}/assignments', [AssignmentController::class, 'store'])->name('courses.assignments.store');
            Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
            Route::patch('/assignments/{assignment}', [AssignmentController::class, 'update']);
            Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

            Route::post('/submissions/{submission}/grade', [GradeController::class, 'store'])->name('submissions.grade.store');
            Route::put('/submissions/{submission}/grade', [GradeController::class, 'update'])->name('submissions.grade.update');
        });
    });
});