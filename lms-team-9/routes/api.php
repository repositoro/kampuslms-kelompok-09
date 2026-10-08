<?php

use App\Http\Controllers\Api\V1\AssignmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\GradeController;
use App\Http\Controllers\Api\V1\MaterialController;
use App\Http\Controllers\Api\V1\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    // -----------------------------------------------------------------
    // Auth (publik — tidak butuh token, tapi login dibatasi rate limit)
    // -----------------------------------------------------------------
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:login')
            ->name('login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('me', [AuthController::class, 'me'])->name('me');
        });
    });

    // -----------------------------------------------------------------
    // Endpoint terproteksi — butuh token Sanctum
    // -----------------------------------------------------------------
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {   //401 unauthorized

        // --- Admin: manajemen pengguna & mata kuliah ---
        Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {  //403 admin
            Route::apiResource('courses', CourseController::class);
        });

        // --- Dosen: materi & tugas ---
        Route::middleware('role:dosen')->group(function () {  //403 dosen/ 
            // Baca daftar course milik dosen (shared dengan admin)
            Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
            Route::get('courses/{course}', [CourseController::class, 'show'])->name('courses.show');

            Route::scopeBindings()->group(function () {
                Route::apiResource('courses.materials', MaterialController::class)->shallow();
                Route::apiResource('courses.assignments', AssignmentController::class)->shallow();
            });

            // Dosen menilai submission
            Route::post('submissions/{submission}/grade', [GradeController::class, 'store'])
                ->name('submissions.grade.store');
            Route::put('submissions/{submission}/grade', [GradeController::class, 'update'])
                ->name('submissions.grade.update');
            Route::get('submissions/{submission}/grade', [GradeController::class, 'show'])
                ->name('submissions.grade.show');

            // Dosen melihat semua submission di assignment miliknya
            Route::get('assignments/{assignment}/submissions', [SubmissionController::class, 'index'])
                ->name('assignments.submissions.index');
            Route::get('submissions/{submission}', [SubmissionController::class, 'show'])
                ->name('submissions.show.dosen');
        });

        // --- Mahasiswa: kumpulkan & lihat tugas/materi ---
        Route::middleware('role:mahasiswa')->group(function () {
            Route::get('courses', [CourseController::class, 'index'])->name('mahasiswa.courses.index');
            Route::get('courses/{course}', [CourseController::class, 'show'])->name('mahasiswa.courses.show');

            Route::scopeBindings()->group(function () {
                Route::apiResource('assignments.submissions', SubmissionController::class)
                    ->shallow()
                    ->only(['index', 'store', 'show', 'update', 'destroy']);
            });

            // Mahasiswa melihat nilai miliknya
            Route::get('submissions/{submission}/grade', [GradeController::class, 'show'])
                ->name('mahasiswa.submissions.grade.show');
        });

        Route::get('/debug/raw-users', function () {
            return response()->json(\App\Models\User::all());
        });
    });
});
