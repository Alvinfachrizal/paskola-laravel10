<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Hak akses penuh (Create, Edit, Delete) hanya untuk Super Admin & Admin
    Route::middleware(['role:Super Admin|Admin'])->group(function () {
        Route::resource('school-years', \App\Http\Controllers\SchoolYearController::class);
        Route::resource('majors', \App\Http\Controllers\MajorController::class);
        Route::resource('subjects', \App\Http\Controllers\SubjectController::class);
        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::post('users/{user}/impersonate', [\App\Http\Controllers\UserController::class, 'impersonate'])->name('users.impersonate');
        
        // Rute untuk modifikasi data (tidak boleh diakses Kepsek & Guru)
        Route::resource('classes', \App\Http\Controllers\SchoolClassController::class)->except(['index', 'show']);
        Route::resource('teachers', \App\Http\Controllers\TeacherController::class)->except(['index', 'show']);
        Route::resource('students', \App\Http\Controllers\StudentController::class)->except(['index', 'show']);
    });

    // Hak akses baca (View) untuk Kelas & Guru bisa diakses oleh Admin & Kepala Sekolah
    Route::middleware(['role:Super Admin|Admin|Kepala Sekolah'])->group(function () {
        Route::resource('classes', \App\Http\Controllers\SchoolClassController::class)->only(['index', 'show']);
        Route::resource('teachers', \App\Http\Controllers\TeacherController::class)->only(['index', 'show']);
    });

    // Hak akses baca (View) untuk Data Siswa bisa diakses Admin, Kepala Sekolah, dan Guru
    Route::middleware(['role:Super Admin|Admin|Kepala Sekolah|Guru'])->group(function () {
        Route::resource('students', \App\Http\Controllers\StudentController::class)->only(['index', 'show']);
    });

    // Modul LMS (Materi, Tugas, Pengumpulan)
    Route::middleware(['role:Super Admin|Admin|Kepala Sekolah|Guru|Siswa'])->group(function () {
        Route::resource('lms-materials', \App\Http\Controllers\LmsMaterialController::class);
        Route::resource('lms-assignments', \App\Http\Controllers\LmsAssignmentController::class);
        Route::resource('lms-submissions', \App\Http\Controllers\LmsSubmissionController::class);
    });
});

// Stop impersonation route (accessible from any role if impersonating)
Route::post('/admin/users/stop-impersonate', [\App\Http\Controllers\UserController::class, 'stopImpersonate'])->middleware('auth')->name('admin.users.stop-impersonate');

require __DIR__.'/auth.php';
