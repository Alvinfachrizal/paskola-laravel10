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

    // ── LMS: semua role yang login dapat mengakses
    // Filter per-role ditangani di dalam controller masing-masing
    Route::middleware(['auth'])->group(function () {
        Route::resource('lms-materials', \App\Http\Controllers\LmsMaterialController::class);
        Route::resource('lms-assignments', \App\Http\Controllers\LmsAssignmentController::class);
        Route::resource('lms-submissions', \App\Http\Controllers\LmsSubmissionController::class)
            ->only(['index', 'show', 'store', 'update']);
    });

});

// ─── Modul Nilai & Rapor ─────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('grades')->name('grades.')->group(function () {

    // Input nilai & atur bobot: hanya Guru, Admin, Kepsek
    Route::middleware(['role:Super Admin|Admin|Kepala Sekolah|Guru'])->group(function () {
        Route::get('/input', [\App\Http\Controllers\Grades\StudentGradeController::class, 'index'])->name('input.index');
        Route::post('/input', [\App\Http\Controllers\Grades\StudentGradeController::class, 'store'])->name('input.store');
        Route::get('/bobot', [\App\Http\Controllers\Grades\GradeWeightController::class, 'index'])->name('weights.index');
        Route::post('/bobot', [\App\Http\Controllers\Grades\GradeWeightController::class, 'store'])->name('weights.store');
    });

    // Rapor — semua role login bisa akses (controller yang filter per role)
    Route::middleware(['role:Super Admin|Admin|Kepala Sekolah|Guru|Siswa|Ortu'])->group(function () {
        Route::get('/rapor', [\App\Http\Controllers\Grades\ReportCardController::class, 'index'])->name('report-cards.index');
    });

    // Update status rapor: hanya Guru (walas) + Admin/Kepsek
    Route::middleware(['role:Super Admin|Admin|Kepala Sekolah|Guru'])->group(function () {
        Route::post('/rapor/{report_card}/status', [\App\Http\Controllers\Grades\ReportCardController::class, 'updateStatus'])->name('report-cards.status');
    });

    // Publish rapor: hanya Admin/Kepsek
    Route::middleware(['role:Super Admin|Admin|Kepala Sekolah'])->group(function () {
        Route::post('/rapor/publish', [\App\Http\Controllers\Grades\ReportCardController::class, 'publishBatch'])->name('report-cards.publish');
    });
});

// Stop impersonation route (accessible from any role if impersonating)
Route::post('/admin/users/stop-impersonate', [\App\Http\Controllers\UserController::class, 'stopImpersonate'])->middleware('auth')->name('admin.users.stop-impersonate');

// ─── Kalender Akademik ────────────────────────────────────────────────────────
Route::middleware('auth')->prefix('calendar')->name('calendar.')->group(function () {

    // Tampilan kalender: semua role yang login
    Route::get('/', [\App\Http\Controllers\Calendar\AcademicEventController::class, 'index'])->name('index');

    // CRUD event: Admin, Kepsek, dan Guru
    Route::middleware(['role:Super Admin|Admin|Kepala Sekolah|Guru'])->group(function () {
        Route::post('/events', [\App\Http\Controllers\Calendar\AcademicEventController::class, 'store'])->name('events.store');
        Route::put('/events/{event}', [\App\Http\Controllers\Calendar\AcademicEventController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [\App\Http\Controllers\Calendar\AcademicEventController::class, 'destroy'])->name('events.destroy');
    });

    // Manajemen kategori: hanya Admin & Kepsek
    Route::middleware(['role:Super Admin|Admin|Kepala Sekolah'])->prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Calendar\EventCategoryController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Calendar\EventCategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [\App\Http\Controllers\Calendar\EventCategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [\App\Http\Controllers\Calendar\EventCategoryController::class, 'destroy'])->name('destroy');
    });
});

// ─── MODUL JADWAL PELAJARAN (TIMETABLE) ───

// Jadwal Saya (Guru & Siswa)
Route::middleware(['auth'])->prefix('timetable')->name('timetable.')->group(function () {
    Route::get('/my-schedule', [\App\Http\Controllers\Timetable\MyScheduleController::class, 'index'])->name('my-schedule');
});

Route::middleware(['role:Super Admin|Admin|Kepala Sekolah'])->prefix('timetable')->name('timetable.')->group(function () {
    // Setting (Hari & Waktu)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Timetable\SettingController::class, 'index'])->name('index');
        Route::post('/days', [\App\Http\Controllers\Timetable\SettingController::class, 'updateDays'])->name('days.update');
        Route::post('/timeslots', [\App\Http\Controllers\Timetable\SettingController::class, 'storeTimeSlot'])->name('timeslots.store');
        Route::delete('/timeslots/{id}', [\App\Http\Controllers\Timetable\SettingController::class, 'destroyTimeSlot'])->name('timeslots.destroy');
    });

    // Manajemen Ruangan (Rooms)
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Timetable\RoomController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Timetable\RoomController::class, 'store'])->name('store');
        Route::put('/{id}', [\App\Http\Controllers\Timetable\RoomController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Timetable\RoomController::class, 'destroy'])->name('destroy');
    });

    // Pembuatan Jadwal Pelajaran Utama (Schedules)
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/create', [\App\Http\Controllers\Timetable\ScheduleController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Timetable\ScheduleController::class, 'store'])->name('store');
        Route::delete('/{id}', [\App\Http\Controllers\Timetable\ScheduleController::class, 'destroy'])->name('destroy');
        Route::get('/get-teachers', [\App\Http\Controllers\Timetable\ScheduleController::class, 'getTeachersBySubject'])->name('get-teachers');
    });
});



// ─── MODUL KEUANGAN SEKOLAH ────────────────────────────────────────────────
Route::middleware(['auth', 'module.active:keuangan_sekolah'])->prefix('keuangan')->name('finance.')->group(function () {

    // Manajemen Jenis Tagihan (Master Data)
    Route::middleware(['role:Super Admin|Admin|Kepala Sekolah'])->prefix('jenis-tagihan')->name('bill-types.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Finance\BillTypeController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Finance\BillTypeController::class, 'store'])->name('store');
        Route::put('/{billType}', [\App\Http\Controllers\Finance\BillTypeController::class, 'update'])->name('update');
        Route::delete('/{billType}', [\App\Http\Controllers\Finance\BillTypeController::class, 'destroy'])->name('destroy');
        Route::patch('/{billType}/toggle', [\App\Http\Controllers\Finance\BillTypeController::class, 'toggleActive'])->name('toggle');
    });

    // Tagihan & Upload Bukti (Admin melihat semua, Siswa melihat miliknya)
    Route::get('tagihan', [\App\Http\Controllers\Finance\StudentBillController::class, 'index'])->name('bills.index');
    Route::get('tagihan/{bill}', [\App\Http\Controllers\Finance\StudentBillController::class, 'show'])->name('bills.show');
    Route::post('tagihan/{bill}/bayar', [\App\Http\Controllers\Finance\PaymentController::class, 'store'])->name('payments.store');

    // Verifikasi Pembayaran (Admin)
    Route::middleware(['role:Super Admin|Admin|Kepala Sekolah'])->group(function () {
        Route::get('pembayaran', [\App\Http\Controllers\Finance\PaymentController::class, 'index'])->name('payments.index');
        Route::post('pembayaran/{payment}/verifikasi', [\App\Http\Controllers\Finance\PaymentController::class, 'verify'])->name('payments.verify');

        // Dashboard Rekap Keuangan
        Route::get('rekap', [\App\Http\Controllers\Finance\FinanceReportController::class, 'index'])->name('reports.index');

        // Riwayat Keuangan Gabungan Per Siswa
        Route::get('riwayat/{student}', [\App\Http\Controllers\Finance\StudentFinanceController::class, 'show'])->name('history.show');
    });
});

// ─── PPDB Publik (tidak butuh login) ───────────────────────────────────────
Route::middleware(['module.active:ppdb'])->prefix('ppdb')->name('ppdb.')->group(function () {
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

    // Upload ulang dokumen yang ditolak (status need_revision)
    Route::get('/status/{registration_code}/upload-ulang', [\App\Http\Controllers\Ppdb\PpdbPublicController::class, 'showReupload'])->name('reupload.form');
    Route::post('/status/{registration_code}/upload-ulang', [\App\Http\Controllers\Ppdb\PpdbPublicController::class, 'storeReupload'])->name('reupload.store');
});

// ─── PPDB Admin Panel (hanya Admin & Super Admin) ──────────────────────────
Route::prefix('admin/ppdb')->name('admin.ppdb.')->middleware(['auth', 'role:Super Admin|Admin', 'module.active:ppdb'])->group(function () {
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

// ─── Halaman: Modul Nonaktif ────────────────────────────────────────────────
Route::get('/modul-nonaktif', function () {
    return view('settings.modules.disabled');
})->middleware('auth')->name('module.disabled');

// ─── SETTINGS (Superadmin only) ──────────────────────────────────────────────
Route::middleware(['auth', 'role:Super Admin'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('modules', [\App\Http\Controllers\Settings\ModuleController::class, 'index'])->name('modules.index');
    Route::post('modules/{module}/toggle', [\App\Http\Controllers\Settings\ModuleController::class, 'toggle'])->name('modules.toggle');
});

require __DIR__.'/auth.php';
