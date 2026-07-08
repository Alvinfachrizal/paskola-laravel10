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

$teachers = User::where('role', 'Guru')->get();

if ($teachers->isEmpty()) {
    echo "Tidak ada guru ditemukan.\n";
    exit;
}

$classes = SchoolClass::all();
$subjects = Subject::all();

if ($classes->isEmpty() || $subjects->isEmpty()) {
    echo "Tolong pastikan data kelas dan mata pelajaran sudah ada.\n";
    exit;
}

// Hapus data lama agar bersih
LmsMaterial::query()->delete();
LmsAssignment::query()->delete();

foreach ($teachers as $idx => $guru) {
    $school_id = $guru->school_id;
    
    // Pilih kelas dan mapel secara acak namun deterministik berdasarkan index
    $class1 = $classes[$idx % count($classes)];
    $class2 = $classes[($idx + 1) % count($classes)];
    
    $subject1 = $subjects[$idx % count($subjects)];
    $subject2 = $subjects[($idx + 1) % count($subjects)];

    // Create Materials
    LmsMaterial::create([
        'school_id' => $school_id,
        'teacher_id' => $guru->id,
        'class_id' => $class1->id,
        'subject_id' => $subject1->id,
        'title' => 'Pengenalan ' . $subject1->name . ' Bab 1',
        'description' => 'Materi presentasi pengenalan dasar untuk mata pelajaran ' . $subject1->name . '. Silakan dibaca sebelum kelas dimulai besok.',
        'type' => 'document',
        'file_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'
    ]);

    LmsMaterial::create([
        'school_id' => $school_id,
        'teacher_id' => $guru->id,
        'class_id' => $class2->id,
        'subject_id' => $subject2->id,
        'title' => 'Video Eksperimen ' . $subject2->name,
        'description' => 'Video singkat yang menjelaskan studi kasus ' . $subject2->name . ' di dunia nyata.',
        'type' => 'video',
        'file_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
    ]);

    // Create Assignments
    LmsAssignment::create([
        'school_id' => $school_id,
        'teacher_id' => $guru->id,
        'class_id' => $class1->id,
        'subject_id' => $subject1->id,
        'title' => 'Tugas Makalah: ' . $subject1->name,
        'description' => 'Buatlah makalah singkat sebanyak 3 halaman tentang topik yang dibahas minggu ini. Kumpulkan dalam bentuk PDF.',
        'file_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
        'due_date' => Carbon::now()->addDays(rand(2, 7)),
        'max_score' => 100
    ]);

    LmsAssignment::create([
        'school_id' => $school_id,
        'teacher_id' => $guru->id,
        'class_id' => $class2->id,
        'subject_id' => $subject2->id,
        'title' => 'Kuis Harian ' . $subject2->name,
        'description' => 'Kuis evaluasi pemahaman materi. Kerjakan di kertas, foto dengan jelas, lalu unggah.',
        'file_url' => 'https://via.placeholder.com/600x400.png?text=Soal+Kuis',
        'due_date' => Carbon::now()->subDays(rand(1, 3)), // Sengaja expired
        'max_score' => 100
    ]);
}

echo "Data LMS (Materi & Tugas) untuk semua guru berhasil digenerate!\n";
