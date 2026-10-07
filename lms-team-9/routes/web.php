<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('dashboard');
Route::view('/tentang', 'tentang')->name('about');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('courses', CourseController::class);
});

Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::scopeBindings()->group(function () {
        Route::resource('courses.materials', \App\Http\Controllers\Dosen\MaterialController::class)->shallow();
        Route::resource('courses.assignments', \App\Http\Controllers\Dosen\AssignmentController::class)->shallow();
    });
});

Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::scopeBindings()->group(function () {
        Route::resource('assignments.submissions', \App\Http\Controllers\Mahasiswa\SubmissionController::class)->shallow();
    });
});