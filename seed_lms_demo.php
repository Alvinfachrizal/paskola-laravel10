<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\LmsMaterial;
use App\Models\LmsAssignment;
use Carbon\Carbon;

$guru = User::where('email', 'guru@paskola.com')->first();
if(!$guru) {
    echo "Guru tidak ditemukan\n";
    exit;
}
$school_id = $guru->school_id;

$class1 = SchoolClass::first();
$subject1 = Subject::first();
$subject2 = Subject::skip(1)->first();

if (!$class1 || !$subject1) {
    echo "Tolong pastikan data kelas dan mata pelajaran sudah ada.\n";
    exit;
}

// Hapus data lama agar tidak menumpuk
LmsMaterial::query()->delete();
LmsAssignment::query()->delete();

// Create Materials
LmsMaterial::create([
    'school_id' => $school_id,
    'teacher_id' => $guru->id,
    'class_id' => $class1->id,
    'subject_id' => $subject1->id,
    'title' => 'Pengenalan Sistem Organisasi Kehidupan',
    'description' => 'Materi presentasi pengenalan mengenai sel dan jaringan pada makhluk hidup. Silakan dibaca sebelum kelas dimulai besok.',
    'type' => 'document',
    'file_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'
]);

LmsMaterial::create([
    'school_id' => $school_id,
    'teacher_id' => $guru->id,
    'class_id' => $class1->id,
    'subject_id' => $subject2 ? $subject2->id : $subject1->id,
    'title' => 'Video Penjelasan Rumus Trigonometri',
    'description' => 'Video singkat yang menjelaskan tentang sin, cos, dan tan dengan mudah beserta contoh soal.',
    'type' => 'video',
    'file_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
]);

// Create Assignments
LmsAssignment::create([
    'school_id' => $school_id,
    'teacher_id' => $guru->id,
    'class_id' => $class1->id,
    'subject_id' => $subject1->id,
    'title' => 'Tugas 1: Menggambar Struktur Sel',
    'description' => 'Gambarlah struktur sel hewan dan tumbuhan beserta nama organelnya di kertas HVS. Foto dengan jelas lalu unggah di sini.',
    'file_url' => 'https://via.placeholder.com/600x400.png?text=Contoh+Struktur+Sel',
    'due_date' => Carbon::now()->addDays(3),
    'max_score' => 100
]);

LmsAssignment::create([
    'school_id' => $school_id,
    'teacher_id' => $guru->id,
    'class_id' => $class1->id,
    'subject_id' => $subject2 ? $subject2->id : $subject1->id,
    'title' => 'Kuis Tengah Semester Matematika',
    'description' => 'Kuis online mengenai materi fungsi kuadrat dan trigonometri dasar. Waktu pengerjaan hanya 90 menit dari waktu unduh soal.',
    'file_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
    'due_date' => Carbon::now()->subDays(1), // Ini akan menjadi 'Tenggat Berlalu'
    'max_score' => 100
]);

echo "Data LMS (Materi & Tugas) berhasil digenerate!\n";
