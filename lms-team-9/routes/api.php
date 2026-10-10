<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\GradeController;

<<<<<<< Updated upstream
Route::prefix('v1')->group(function () {
    // Rate limiter for login specifically
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    
    // Authenticated routes
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        
        // Courses
        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{id}', [CourseController::class, 'show']);
        Route::get('/courses/{id}/materials', [CourseController::class, 'materials']);
        Route::get('/courses/{id}/assignments', [CourseController::class, 'assignments']);
        
        // Assignments
        Route::post('/assignments', [AssignmentController::class, 'store']);
        Route::put('/assignments/{id}', [AssignmentController::class, 'update']);
        Route::patch('/assignments/{id}', [AssignmentController::class, 'update']);
        Route::delete('/assignments/{id}', [AssignmentController::class, 'destroy']);
        Route::get('/assignments/{id}/submissions', [AssignmentController::class, 'submissions']);
        Route::post('/assignments/{id}/submissions', [AssignmentController::class, 'submit']);
        
        // Grade
        Route::put('/submissions/{id}/grade', [GradeController::class, 'update']);
=======
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
>>>>>>> Stashed changes
    });
});
