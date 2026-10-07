<?php
/**
 * QA Check Script - Jalankan dengan: php qa/qa_check.php
 * Script ini mensimulasikan pengecekan data dan logika RBAC tanpa browser.
 */

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\LmsMaterial;
use App\Models\LmsAssignment;
use App\Models\LmsSubmission;
use App\Models\Student;

$pass = 0;
$fail = 0;

function check($label, $condition, $detail = '') {
    global $pass, $fail;
    if ($condition) {
        echo "\033[32m  [PASS]\033[0m {$label}" . ($detail ? " ({$detail})" : '') . PHP_EOL;
        $pass++;
    } else {
        echo "\033[31m  [FAIL]\033[0m {$label}" . ($detail ? " → {$detail}" : '') . PHP_EOL;
        $fail++;
    }
}

echo PHP_EOL . "\033[1m============================\033[0m" . PHP_EOL;
echo "\033[1m  PASKOLA QA — CODE LEVEL  \033[0m" . PHP_EOL;
echo "\033[1m============================\033[0m" . PHP_EOL . PHP_EOL;

// ============================================================
// SKENARIO 1: Ketersediaan Akun Demo
// ============================================================
echo "\033[1m[GRUP 1] Akun Demo & Role\033[0m" . PHP_EOL;
$admin  = User::where('email', 'admin@paskola.com')->first();
$guru   = User::where('email', 'guru.mtk@paskola.com')->first();
$siswa  = User::where('email', 'siswa.xipa11@paskola.com')->first();
$ortu   = User::where('email', 'ortu@paskola.com')->first();
$kepsek = User::where('email', 'kepsek@paskola.com')->first();

check("Akun Admin tersedia",          $admin  !== null, $admin?->email);
check("Akun Admin punya role Admin",  $admin  && in_array($admin->role, ['Admin','Super Admin']), $admin?->role);
check("Akun Guru tersedia",           $guru   !== null, $guru?->email);
check("Akun Guru punya role Guru",    $guru   && $guru->role === 'Guru', $guru?->role);
check("Akun Siswa tersedia",          $siswa  !== null, $siswa?->email);
check("Akun Siswa punya role Siswa",  $siswa  && $siswa->role === 'Siswa', $siswa?->role);
check("Akun Ortu tersedia",           $ortu   !== null, $ortu?->email);
check("Akun Kepsek tersedia",         $kepsek !== null, $kepsek?->email);

// ============================================================
// SKENARIO 2: Data LMS
// ============================================================
echo PHP_EOL . "\033[1m[GRUP 2] Data LMS\033[0m" . PHP_EOL;
$totalMaterial   = LmsMaterial::count();
$totalAssignment = LmsAssignment::count();
$totalSubmission = LmsSubmission::count();

check("Ada materi LMS di database",     $totalMaterial > 0,   "total={$totalMaterial}");
check("Ada tugas LMS di database",      $totalAssignment > 0, "total={$totalAssignment}");
check("Ada submission di database",     $totalSubmission > 0, "total={$totalSubmission}");

// ============================================================
// SKENARIO 3: Relasi Siswa → Kelas
// ============================================================
echo PHP_EOL . "\033[1m[GRUP 3] Relasi Siswa & Kelas\033[0m" . PHP_EOL;
if ($siswa) {
    $student = $siswa->student;
    check("Akun Siswa punya record di tabel students", $student !== null, $student ? "id={$student->id}" : "NULL");

    if ($student) {
        $classes = $student->classes;
        check("Siswa terdaftar di minimal 1 kelas", $classes->count() > 0, "kelas=" . $classes->pluck('name')->implode(', '));

        // Uji logika filter: materi untuk kelas siswa
        $classIds = $classes->pluck('id');
        $materiSiswa = LmsMaterial::whereIn('class_id', $classIds)->count();
        check("Ada materi LMS untuk kelas siswa ini", $materiSiswa > 0, "materi tersedia={$materiSiswa}");

        $tugasSiswa = LmsAssignment::whereIn('class_id', $classIds)->count();
        check("Ada tugas LMS untuk kelas siswa ini", $tugasSiswa > 0, "tugas tersedia={$tugasSiswa}");
    }
}

// ============================================================
// SKENARIO 4: RBAC Logika LMS (Code Level)
// ============================================================
echo PHP_EOL . "\033[1m[GRUP 4] RBAC — LmsSubmission (Code Audit)\033[0m" . PHP_EOL;

// Periksa bahwa LmsSubmissionController::store() memeriksa role Siswa
$submissionController = file_get_contents(__DIR__ . '/../app/Http/Controllers/LmsSubmissionController.php');
$needle1 = "abort_if(!\$user->hasRole('Siswa')";
check(
    "store() hanya untuk Siswa (ada abort_if !hasRole Siswa)",
    str_contains($submissionController, $needle1),
    "Terdapat guard abort_if di store()"
);
check(
    "show() Siswa hanya lihat milik sendiri (ada abort_if student_id !== user->id)",
    str_contains($submissionController, 'student_id !== $user->id'),
    "Terdapat guard student_id check di show()"
);
check(
    "index() Siswa difilter where student_id",
    str_contains($submissionController, "where('student_id', \$user->id)"),
    "Ada filter student_id untuk Siswa"
);

// ============================================================
// SKENARIO 5: RBAC Logika LmsMaterial (Code Audit)
// ============================================================
echo PHP_EOL . "\033[1m[GRUP 5] RBAC — LmsMaterial (Code Audit)\033[0m" . PHP_EOL;
$materialController = file_get_contents(__DIR__ . '/../app/Http/Controllers/LmsMaterialController.php');
check(
    "destroy() hanya pemilik atau Admin (ada teacher_id !== Auth::id check)",
    str_contains($materialController, 'teacher_id !== Auth::id()'),
    "Guard ada di destroy()"
);
check(
    "index() Siswa hanya lihat materi kelasnya (whereIn class_id)",
    str_contains($materialController, "whereIn('class_id', \$classIds)"),
    "Filter kelas ada untuk Siswa"
);
check(
    "index() Guru hanya lihat materi miliknya (where teacher_id)",
    str_contains($materialController, "where('teacher_id', \$user->id)"),
    "Filter teacher_id ada untuk Guru"
);

// ============================================================
// SKENARIO 6: RBAC Logika LmsAssignment (Code Audit)
// ============================================================
echo PHP_EOL . "\033[1m[GRUP 6] RBAC — LmsAssignment (Code Audit)\033[0m" . PHP_EOL;
$assignmentController = file_get_contents(__DIR__ . '/../app/Http/Controllers/LmsAssignmentController.php');
check(
    "destroy() hanya pemilik atau Admin",
    str_contains($assignmentController, 'teacher_id !== Auth::id()'),
    "Guard ada di destroy()"
);
check(
    "index() Siswa difilter per kelas",
    str_contains($assignmentController, "whereIn('class_id', \$classIds)"),
    "Filter kelas ada"
);

// ============================================================
// SKENARIO 7: Sinkronisasi Nilai ke student_grades
// ============================================================
echo PHP_EOL . "\033[1m[GRUP 7] Sync Nilai LMS → student_grades (Code Audit)\033[0m" . PHP_EOL;
check(
    "update() memanggil syncToStudentGrades setelah nilai diset",
    str_contains($submissionController, 'syncToStudentGrades'),
    "Fungsi sync ada"
);
check(
    "syncToStudentGrades memanggil StudentGrade::updateOrCreate",
    str_contains($submissionController, 'StudentGrade::updateOrCreate'),
    "updateOrCreate ada"
);
check(
    "syncToStudentGrades memanggil ReportCard::recalculate",
    str_contains($submissionController, 'ReportCard::recalculate'),
    "recalculate otomatis ada"
);
check(
    "source ditetapkan 'lms' saat disimpan",
    str_contains($submissionController, "'source'") && str_contains($submissionController, 'lms'),
    "source=lms ada"
);

// ============================================================
// SKENARIO 8: Validasi Form (Code Audit)
// ============================================================
echo PHP_EOL . "\033[1m[GRUP 8] Validasi Form\033[0m" . PHP_EOL;
check(
    "Submission: validasi file max 20MB (max:20480)",
    str_contains($submissionController, 'max:20480'),
    "Validasi file size ada"
);
check(
    "Submission: validasi tipe file (pdf, doc, zip, dll)",
    str_contains($submissionController, 'mimes:pdf,doc,docx,zip'),
    "Validasi mimes ada"
);
check(
    "Submission: validasi score min:0 max:100",
    str_contains($submissionController, 'min:0|max:100'),
    "Validasi range score ada"
);
check(
    "Material: validasi tipe (document/video/link)",
    str_contains($materialController, "in:document,video,link"),
    "Validasi type enum ada"
);

// ============================================================
// RINGKASAN
// ============================================================
$total = $pass + $fail;
echo PHP_EOL . "\033[1m============================\033[0m" . PHP_EOL;
echo "\033[1m  RINGKASAN HASIL\033[0m" . PHP_EOL;
echo "\033[1m============================\033[0m" . PHP_EOL;
echo "Total skenario : {$total}" . PHP_EOL;
echo "\033[32mLulus (PASS)   : {$pass}\033[0m" . PHP_EOL;
if ($fail > 0) {
    echo "\033[31mGagal (FAIL)   : {$fail}\033[0m" . PHP_EOL;
    echo PHP_EOL . "\033[33mAda " . $fail . " skenario yang perlu perhatian!\033[0m" . PHP_EOL;
} else {
    echo "\033[32mGagal (FAIL)   : {$fail}\033[0m" . PHP_EOL;
    echo PHP_EOL . "\033[32mSemua skenario LULUS! ✅\033[0m" . PHP_EOL;
}
echo PHP_EOL;
