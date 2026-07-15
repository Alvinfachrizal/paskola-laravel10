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

// ─── PPDB Publik (tidak butuh login) ───────────────────────────────────────
Route::prefix('ppdb')->name('ppdb.')->group(function () {
    // Landing page portal PPDB
    Route::get('/', [\App\Http\Controllers\Ppdb\PpdbPublicController::class, 'index'])->name('index');

    // Form pendaftaran baru
    Route::get('/daftar', [\App\Http\Controllers\Ppdb\PpdbPublicController::class, 'showForm'])->name('register.form');
    Route::post('/daftar', [\App\Http\Controllers\Ppdb\PpdbPublicController::class, 'store'])->name('register.store');

    // Halaman sukses pendaftaran (tampil kode registrasi)
    Route::get('/sukses', [\App\Http\Controllers\Ppdb\PpdbPublicController::class, 'success'])->name('register.success');

    // Login ulang: cek status pendaftaran (kode + tanggal lahir)
    Route::get('/cek-status', [\App\Http\Controllers\Ppdb\PpdbPublicController::class, 'cekStatusForm'])->name('cek-status.form');
    Route::post('/cek-status', [\App\Http\Controllers\Ppdb\PpdbPublicController::class, 'cekStatus'])->name('cek-status.submit');

    // Halaman detail status (setelah login ulang berhasil, disimpan di session)
    Route::get('/status/{registration_code}', [\App\Http\Controllers\Ppdb\PpdbPublicController::class, 'showStatus'])->name('status');
});

// ─── PPDB Admin Panel (hanya Admin & Super Admin) ──────────────────────────
Route::prefix('admin/ppdb')->name('admin.ppdb.')->middleware(['auth', 'role:Super Admin|Admin'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Ppdb\PpdbAdminController::class, 'index'])->name('index');
    Route::get('/gelombang', [\App\Http\Controllers\Ppdb\PpdbAdminController::class, 'waves'])->name('waves');
    Route::post('/gelombang', [\App\Http\Controllers\Ppdb\PpdbAdminController::class, 'storeWave'])->name('waves.store');
    Route::put('/gelombang/{wave}', [\App\Http\Controllers\Ppdb\PpdbAdminController::class, 'updateWave'])->name('waves.update');

    // Detail & verifikasi dokumen per pendaftar
    Route::get('/pendaftar/{applicant}', [\App\Http\Controllers\Ppdb\PpdbAdminController::class, 'show'])->name('applicants.show');
    Route::post('/pendaftar/{applicant}/dokumen/{document}/verifikasi', [\App\Http\Controllers\Ppdb\PpdbAdminController::class, 'verifyDocument'])->name('documents.verify');

    // Input nilai seleksi & override status
    Route::post('/pendaftar/{applicant}/skor', [\App\Http\Controllers\Ppdb\PpdbAdminController::class, 'storeScore'])->name('scores.store');
    Route::post('/pendaftar/{applicant}/status', [\App\Http\Controllers\Ppdb\PpdbAdminController::class, 'updateStatus'])->name('applicants.status');

    // Daftar ulang → buat siswa resmi
    Route::post('/pendaftar/{applicant}/daftar-ulang', [\App\Http\Controllers\Ppdb\PpdbAdminController::class, 'processReregistration'])->name('reregistration.process');

    // Rekap kebutuhan seragam
    Route::get('/rekap-seragam', [\App\Http\Controllers\Ppdb\PpdbAdminController::class, 'uniformRecap'])->name('uniform-recap');
});

require __DIR__.'/auth.php';
