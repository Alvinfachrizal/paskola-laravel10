<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\School;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

$school = School::first();

if (!$school) {
    echo "Tolong pastikan data sekolah (School) sudah ada.\n";
    exit;
}

$dummyTeachers = [
    [
        'email' => 'hendra@paskola.com',
        'name' => 'Hendra Setiawan, S.Pd',
        'nip' => '198005142005011001',
        'gender' => 'Laki-laki',
        'subject_specialty' => 'Matematika',
        'employment_type' => 'PNS',
    ],
    [
        'email' => 'siti@paskola.com',
        'name' => 'Siti Aminah, M.Pd',
        'nip' => '198509202010012003',
        'gender' => 'Perempuan',
        'subject_specialty' => 'Biologi',
        'employment_type' => 'PNS',
    ],
    [
        'email' => 'bambang@paskola.com',
        'name' => 'Bambang Pamungkas, S.T',
        'nip' => '199011122015021004',
        'gender' => 'Laki-laki',
        'subject_specialty' => 'Fisika',
        'employment_type' => 'Honorer',
    ],
    [
        'email' => 'rina@paskola.com',
        'name' => 'Rina Wijaya, S.S',
        'nip' => '199203152018012005',
        'gender' => 'Perempuan',
        'subject_specialty' => 'Bahasa Inggris',
        'employment_type' => 'Honorer',
    ],
];

foreach ($dummyTeachers as $tData) {
    // 1. Buat User Account
    $user = User::firstOrCreate(
        ['email' => $tData['email']],
        [
            'name' => $tData['name'],
            'password' => Hash::make('password123'),
            'school_id' => $school->id,
            'role' => 'Guru',
            'email_verified_at' => now(),
        ]
    );

    // Pastikan user punya role Spatie 'Guru'
    if (!$user->hasRole('Guru')) {
        $user->assignRole('Guru');
    }

    // 2. Buat profil Teacher
    Teacher::firstOrCreate(
        ['user_id' => $user->id],
        [
            'school_id' => $school->id,
            'nip' => $tData['nip'],
            'name' => $tData['name'],
            'gender' => $tData['gender'],
            'birth_place' => 'Jakarta',
            'birth_date' => Carbon::now()->subYears(rand(25, 45))->format('Y-m-d'),
            'religion' => 'Islam',
            'address' => 'Jl. Pendidikan No. ' . rand(1, 100),
            'phone' => '0812' . rand(10000000, 99999999),
            'last_education' => str_contains($tData['name'], 'S.T') || str_contains($tData['name'], 'S.Pd') || str_contains($tData['name'], 'S.S') ? 'S1' : 'S2',
            'subject_specialty' => $tData['subject_specialty'],
            'employment_type' => $tData['employment_type'],
            'join_date' => Carbon::now()->subYears(rand(1, 10))->format('Y-m-d'),
            'status' => 'Aktif',
        ]
    );
}

echo "Data Dummy Guru berhasil digenerate!\n";
