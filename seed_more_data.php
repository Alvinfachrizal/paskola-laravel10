<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

$school = School::first();
if (!$school) {
    echo "Tolong pastikan data sekolah (School) sudah ada.\n";
    exit;
}

// 1. Buat 5 Tahun Ajaran
$schoolYearsData = [
    ['academic_year' => '2023/2024', 'semester' => 'Ganjil'], 
    ['academic_year' => '2023/2024', 'semester' => 'Genap'], 
    ['academic_year' => '2024/2025', 'semester' => 'Ganjil'], 
    ['academic_year' => '2024/2025', 'semester' => 'Genap'], 
    ['academic_year' => '2025/2026', 'semester' => 'Ganjil']
];
foreach ($schoolYearsData as $idx => $sy) {
    SchoolYear::firstOrCreate(
        ['academic_year' => $sy['academic_year'], 'semester' => $sy['semester'], 'school_id' => $school->id],
        ['is_active' => $idx === 0 ? true : false, 'start_date' => Carbon::now()->subMonths(6), 'end_date' => Carbon::now()->addMonths(6)]
    );
}

// 2. Buat 5 Mapel
$subjectsData = [
    ['name' => 'Bahasa Indonesia', 'code' => 'BIN', 'type' => 'Muatan Nasional'],
    ['name' => 'Pendidikan Agama Islam', 'code' => 'PAI', 'type' => 'Muatan Nasional'],
    ['name' => 'Kimia', 'code' => 'KIM', 'type' => 'Muatan Peminatan'],
    ['name' => 'Sosiologi', 'code' => 'SOS', 'type' => 'Muatan Peminatan'],
    ['name' => 'Pendidikan Jasmani Olahraga', 'code' => 'PJOK', 'type' => 'Muatan Nasional'],
];
foreach ($subjectsData as $sub) {
    Subject::firstOrCreate(
        ['name' => $sub['name'], 'school_id' => $school->id],
        ['code' => $sub['code'], 'type' => $sub['type']]
    );
}

// Pastikan ada Major untuk membuat Kelas
$majorMipa = Major::firstOrCreate(['name' => 'MIPA', 'school_id' => $school->id], ['code' => 'MIPA']);
$majorIps = Major::firstOrCreate(['name' => 'IPS', 'school_id' => $school->id], ['code' => 'IPS']);
$activeYear = SchoolYear::where('is_active', true)->first();

// 3. Buat 5 Kelas
$classesData = [
    ['name' => 'X MIPA 2', 'grade' => '10', 'major_id' => $majorMipa->id],
    ['name' => 'XI MIPA 1', 'grade' => '11', 'major_id' => $majorMipa->id],
    ['name' => 'XII MIPA 1', 'grade' => '12', 'major_id' => $majorMipa->id],
    ['name' => 'X IPS 1', 'grade' => '10', 'major_id' => $majorIps->id],
    ['name' => 'XI IPS 1', 'grade' => '11', 'major_id' => $majorIps->id],
];
foreach ($classesData as $cls) {
    SchoolClass::firstOrCreate(
        ['name' => $cls['name'], 'school_id' => $school->id, 'school_year_id' => $activeYear->id],
        ['grade' => $cls['grade'], 'major_id' => $cls['major_id']]
    );
}

// 4. Buat 5 Siswa
$studentsData = [
    ['email' => 'budis@paskola.com', 'name' => 'Budi Santoso', 'nisn' => '0051234501', 'gender' => 'Laki-laki'],
    ['email' => 'ayul@paskola.com', 'name' => 'Ayu Lestari', 'nisn' => '0051234502', 'gender' => 'Perempuan'],
    ['email' => 'dimasp@paskola.com', 'name' => 'Dimas Pratama', 'nisn' => '0051234503', 'gender' => 'Laki-laki'],
    ['email' => 'ratnam@paskola.com', 'name' => 'Ratna Megawati', 'nisn' => '0051234504', 'gender' => 'Perempuan'],
    ['email' => 'kevinw@paskola.com', 'name' => 'Kevin Wijaya', 'nisn' => '0051234505', 'gender' => 'Laki-laki'],
];

// Dapatkan kelas-kelas secara acak (atau berurutan)
$allClasses = SchoolClass::where('school_year_id', $activeYear->id)->get();

foreach ($studentsData as $idx => $sData) {
    // a. Buat Akun User
    $user = User::firstOrCreate(
        ['email' => $sData['email']],
        [
            'name' => $sData['name'],
            'password' => Hash::make('password123'),
            'school_id' => $school->id,
            'role' => 'Siswa',
            'email_verified_at' => now(),
        ]
    );

    if (!$user->hasRole('Siswa')) {
        $user->assignRole('Siswa');
    }

    // b. Buat Profil Student
    $student = Student::firstOrCreate(
        ['user_id' => $user->id],
        [
            'school_id' => $school->id,
            'nisn' => $sData['nisn'],
            'nis' => '2324' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
            'name' => $sData['name'],
            'gender' => $sData['gender'],
            'birth_place' => 'Jakarta',
            'birth_date' => Carbon::now()->subYears(15)->subDays(rand(1, 300))->format('Y-m-d'),
            'religion' => 'Islam',
            'address' => 'Jl. Siswa No. ' . rand(1, 50),
            'phone' => '0821' . rand(10000000, 99999999),
            'entry_year' => 2023,
            'status' => 'Aktif',
            'parent_name' => 'Orang Tua ' . explode(' ', $sData['name'])[0],
            'parent_phone' => '0813' . rand(10000000, 99999999),
        ]
    );

    // c. Masukkan ke dalam Kelas Aktif
    // Pilih kelas bergiliran
    $selectedClass = $allClasses[$idx % count($allClasses)];
    StudentClass::firstOrCreate(
        [
            'student_id' => $student->id,
            'class_id' => $selectedClass->id,
            'school_year_id' => $activeYear->id
        ],
        [
            'is_active' => true,
        ]
    );
}

echo "Data Dummy (Siswa, Kelas, Mapel, Tahun Ajaran) masing-masing 5 entri berhasil digenerate!\n";
