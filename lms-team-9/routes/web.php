<?php

use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────
// Controller Imports
// ─────────────────────────────────────────────────────
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Dosen\CourseController as DosenCourseController;
use App\Http\Controllers\Dosen\MaterialController as DosenMaterialController;
use App\Http\Controllers\Dosen\AssignmentController as DosenAssignmentController;
use App\Http\Controllers\Dosen\GradeController as DosenGradeController;
use App\Http\Controllers\Mahasiswa\CourseController as MahasiswaCourseController;
use App\Http\Controllers\Mahasiswa\MaterialController as MahasiswaMaterialController;
use App\Http\Controllers\Mahasiswa\AssignmentController as MahasiswaAssignmentController;
use App\Http\Controllers\Mahasiswa\SubmissionController as MahasiswaSubmissionController;

// ─────────────────────────────────────────────────────
// Halaman Publik (tanpa autentikasi)
// ─────────────────────────────────────────────────────
Route::view('/', 'welcome')->name('welcome');
Route::view('/tentang', 'tentang')->name('about');

// Route Auth & Dashboard
Route::view('/login', 'auth.login')->name('login');
Route::view('/dashboard', 'home')->middleware('auth')->name('dashboard');

// 🥷🏼 ──────────────────────────────────────────────────
// JALUR PINTAS LOGIN & LOGOUT (HAPUS DI MINGGU 7 NANTI)
// ─────────────────────────────────────────────────────
Route::get('/login-sebagai/{id}', function ($id) {
    auth()->loginUsingId($id);
    return redirect('/dashboard'); // Langsung balik ke dashboard setelah login
});

Route::get('/logout-cepat', function () {
    auth()->logout();
    return redirect('/');
});
// ─────────────────────────────────────────────────────


// ═════════════════════════════════════════════════════
// ADMIN ROUTES
// ═════════════════════════════════════════════════════
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::resource('users', AdminUserController::class);
        Route::resource('courses', AdminCourseController::class);

    });

// ═════════════════════════════════════════════════════
// DOSEN ROUTES
// ═════════════════════════════════════════════════════
Route::prefix('dosen')
    ->name('dosen.')
    ->middleware(['auth', 'role:dosen'])
    ->group(function () {

        Route::resource('courses', DosenCourseController::class)
            ->only(['index', 'show']);

        Route::scopeBindings()->group(function () {
            Route::resource('courses.materials', DosenMaterialController::class)
                ->shallow();
        });

        Route::scopeBindings()->group(function () {
            Route::resource('courses.assignments', DosenAssignmentController::class)
                ->shallow();
        });

        Route::resource('submissions.grades', DosenGradeController::class)
            ->shallow()
            ->only(['create', 'store', 'edit', 'update']);

    });

// ═════════════════════════════════════════════════════
// MAHASISWA ROUTES
// ═════════════════════════════════════════════════════
Route::prefix('mahasiswa')
    ->name('mahasiswa.')
    ->middleware(['auth', 'role:mahasiswa'])
    ->group(function () {

        Route::resource('courses', MahasiswaCourseController::class)
            ->only(['index', 'show']);

        Route::scopeBindings()->group(function () {
            Route::resource('courses.materials', MahasiswaMaterialController::class)
                ->shallow()
                ->only(['index', 'show']);
        });

        Route::scopeBindings()->group(function () {
            Route::resource('courses.assignments', MahasiswaAssignmentController::class)
                ->shallow()
                ->only(['index', 'show']);
        });

        Route::resource('assignments.submissions', MahasiswaSubmissionController::class)
            ->shallow()
            ->only(['create', 'store', 'show']);

    });

// ─────────────────────────────────────────────────────
// Backward-compatibility aliases
// ─────────────────────────────────────────────────────
Route::redirect('/courses', '/admin/courses')->name('courses.index');
Route::redirect('/users', '/admin/users')->name('users.index');