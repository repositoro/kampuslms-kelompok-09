<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Dosen\MaterialController;
use App\Http\Controllers\Dosen\AssignmentController;
use App\Http\Controllers\Mahasiswa\SubmissionController;

// Route Halaman Utama & Umum
Route::view('/', 'welcome')->name('dashboard');
Route::view('/tentang', 'tentang')->name('about');

// Route Courses Publik/Umum (Otomatis membuat nama 'courses.index', 'courses.show', dll)
Route::resource('courses', CourseController::class);

// Group Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('courses', CourseController::class);
});

// Group Dosen
Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::scopeBindings()->group(function () {
        Route::resource('courses.materials', MaterialController::class)->shallow();
        Route::resource('courses.assignments', AssignmentController::class)->shallow();
    });
});

// Group Mahasiswa
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::scopeBindings()->group(function () {
        Route::resource('assignments.submissions', SubmissionController::class)->shallow();
    });
});