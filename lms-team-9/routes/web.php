<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Dosen\MaterialController;
use App\Http\Controllers\Dosen\AssignmentController;
use App\Http\Controllers\Mahasiswa\SubmissionController;

// Route Halaman Publik
Route::view('/', 'welcome');
Route::view('/tentang', 'tentang')->name('about');

// Route Tamu (Hanya dapat diakses jika BELUM login)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

// Route Terproteksi Autentikasi (Wajib Login)
Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Dashboard Utama
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Mata Kuliah Utama
    Route::resource('courses', CourseController::class);

    // Group Admin
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Group Dosen
    Route::middleware(['role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
        Route::scopeBindings()->group(function () {
            Route::resource('courses.materials', MaterialController::class)->shallow();
            Route::resource('courses.assignments', AssignmentController::class)->shallow();
        });
    });

    // Group Mahasiswa
    Route::middleware(['role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::scopeBindings()->group(function () {
            Route::resource('assignments.submissions', SubmissionController::class)->shallow();
        });
    });

});