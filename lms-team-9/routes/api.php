<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\MaterialController;
use App\Http\Controllers\Api\V1\AssignmentController;
use App\Http\Controllers\Api\V1\SubmissionController;
use App\Http\Controllers\Api\V1\GradeController;

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public routes (Auth)
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('auth.login');

    // Protected routes
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

        // Route Debug Peragaan Kebocoran Model Mentah
        Route::get('/debug/raw-users', function () {
            return response()->json(\App\Models\User::all());
        });

        // Course endpoints
        Route::apiResource('courses', CourseController::class);

        // Nested endpoints with shallow and scope bindings
        Route::scopeBindings()->group(function () {
            Route::apiResource('courses.materials', MaterialController::class)->shallow();
            Route::apiResource('courses.assignments', AssignmentController::class)->shallow();
            // Submissions (only index, store, show)
            Route::apiResource('assignments.submissions', SubmissionController::class)->shallow()->only(['index', 'store', 'show']);
        });

        // Grade (store, update, show)
        Route::post('/submissions/{submission}/grade', [GradeController::class, 'store'])->name('submissions.grade.store');
        Route::put('/submissions/{submission}/grade', [GradeController::class, 'update'])->name('submissions.grade.update');
        Route::get('/submissions/{submission}/grade', [GradeController::class, 'show'])->name('submissions.grade.show');
    });
});