<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dosen\AssignmentController;
use App\Http\Controllers\Dosen\GradeController;
use App\Http\Controllers\Dosen\MaterialController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GradeSummaryController;
use App\Http\Controllers\Mahasiswa\AssignmentController as MahasiswaAssignmentController;
use App\Http\Controllers\Mahasiswa\SubmissionController;
use App\Http\Controllers\MaterialDownloadController;
use App\Http\Controllers\SubmissionDownloadController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

    // Endpoint Unduh Berkas Privat (Diakses via Controller & Policy)
    Route::get('/materials/{material}/download', MaterialDownloadController::class)
        ->name('materials.download');
    Route::get('/submissions/{submission}/download', SubmissionDownloadController::class)
        ->name('submissions.download');

    // Dashboard Utama
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Mata Kuliah Utama
    Route::resource('courses', CourseController::class);
    Route::post('/courses/{course}/enrollments', [EnrollmentController::class, 'store'])
        ->name('courses.enrollments.store');
    Route::delete('/courses/{course}/enrollments/{student}', [EnrollmentController::class, 'destroy'])
        ->name('courses.enrollments.destroy');

    // Group Admin
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Group Dosen
    // Prefix "dosen" hanya penanda area UI, bukan penentu hak akses. Karena itu
    // middleware role dipasang per aksi, bukan satu blok untuk seluruh grup:
    //   - halaman baca dipakai bersama oleh admin, dosen pengampu, dan mahasiswa
    //     terdaftar; keputusan akhir tetap diambil policy (CoursePolicy,
    //     MaterialPolicy, AssignmentPolicy).
    //   - halaman pengelolaan hanya untuk admin dan dosen pengampu.
    //   - rekap nilai dan pemberian nilai hanya untuk dosen pengampu.
    Route::prefix('dosen')->name('dosen.')->group(function () {
        Route::scopeBindings()->group(function () {
            // Halaman baca bersama: semua role boleh masuk, policy yang menyaring.
            Route::middleware('role:dosen,admin,mahasiswa')->group(function () {
                Route::resource('courses.materials', MaterialController::class)->shallow()->only(['index']);
                Route::resource('courses.assignments', AssignmentController::class)->shallow()->only(['index']);
                Route::get('/materials/{material}', [MaterialController::class, 'show'])
                    ->name('materials.show');
                Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])
                    ->name('assignments.show');
            });

            // Area pengelolaan materi & tugas: admin atau dosen pengampu.
            Route::middleware('role:dosen,admin')->group(function () {
                Route::resource('courses.materials', MaterialController::class)->shallow()->except(['index', 'show']);
                Route::resource('courses.assignments', AssignmentController::class)->shallow()->except(['index', 'show']);

                // Monitoring Pengumpulan Tugas & Penilaian (Langkah 5)
                Route::get('/assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])
                    ->name('assignments.submissions');
            });

            // Rekap nilai dan pemberian nilai: hanya dosen pengampu mata kuliah.
            Route::middleware('role:dosen')->group(function () {
                Route::get('/courses/{course}/grades', [GradeSummaryController::class, 'course'])
                    ->name('courses.grades.index');
                Route::put('/submissions/{submission}/grade', [GradeController::class, 'update'])
                    ->name('submissions.grade.update');
            });
        });
    });

    // Group Mahasiswa
    Route::middleware(['role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::scopeBindings()->group(function () {
            Route::get('/assignments/{assignment}', [MahasiswaAssignmentController::class, 'show'])
                ->name('assignments.show');
            Route::post('/assignments/{assignment}/submissions', [SubmissionController::class, 'store'])
                ->name('assignments.submissions.store');
            Route::get('/grades', [GradeSummaryController::class, 'student'])
                ->name('grades.index');
        });
    });

});