<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\GradeController;

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
    });
});
