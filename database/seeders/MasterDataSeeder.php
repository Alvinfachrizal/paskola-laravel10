<?php

namespace Database\Seeders;

use App\Models\Major;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * MasterDataSeeder
 *
 * Membuat data master yang realistis:
 * - 1 Tahun Ajaran + 2 Semester
 * - 3 Jurusan (IPA, IPS, Bahasa)
 * - 6 Kelas (X-IPA-1, X-IPA-2, XI-IPS-1, XI-IPS-2, XII-IPA-1, XII-IPS-1)
 * - 10 Mata Pelajaran
 * - 6 Guru (user + profil teacher) dengan mapel ampu
 * - 30 Siswa (user + profil student) tersebar ke kelas
 * - 3 Ortu (user) terhubung ke 3 siswa pertama
 */
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Database\Eloquent\Model::unguard();

        $school = School::first();
        if (!$school) {
            $this->command->error('School tidak ditemukan. Jalankan RoleAndUserSeeder terlebih dahulu.');
            return;
        }

        $this->command->info('=== MasterDataSeeder ===');

        // ── 1. Tahun Ajaran & Semester ────────────────────────────────────────
        $schoolYear = SchoolYear::firstOrCreate(
            ['school_id' => $school->id, 'academic_year' => '2025/2026', 'semester' => 'Ganjil'],
            ['start_date' => '2025-07-14', 'end_date' => '2025-12-20', 'is_active' => true]
        );

        $semGanjil = Semester::firstOrCreate(
            ['school_id' => $school->id, 'academic_year' => 2025, 'term' => 1],
            ['name' => 'Semester Ganjil 2025/2026', 'start_date' => '2025-07-14', 'end_date' => '2025-12-20', 'is_active' => true]
        );

        $semGenap = Semester::firstOrCreate(
            ['school_id' => $school->id, 'academic_year' => 2025, 'term' => 2],
            ['name' => 'Semester Genap 2025/2026', 'start_date' => '2026-01-05', 'end_date' => '2026-06-28', 'is_active' => false]
        );

        $this->command->info('✓ Tahun Ajaran & Semester');

        // ── 2. Jurusan ────────────────────────────────────────────────────────
        $majors = [];
        foreach (['IPA', 'IPS', 'Bahasa'] as $majorName) {
            $majors[$majorName] = Major::firstOrCreate(
                ['school_id' => $school->id, 'name' => $majorName],
                ['type' => 'Umum', 'is_active' => true]
            );
        }

        $this->command->info('✓ Jurusan');

        // ── 3. Kelas ──────────────────────────────────────────────────────────
        $classesData = [
            ['name' => 'X-IPA-1',  'grade' => 10, 'major' => 'IPA'],
            ['name' => 'X-IPA-2',  'grade' => 10, 'major' => 'IPA'],
            ['name' => 'X-IPS-1',  'grade' => 10, 'major' => 'IPS'],
            ['name' => 'XI-IPA-1', 'grade' => 11, 'major' => 'IPA'],
            ['name' => 'XI-IPS-1', 'grade' => 11, 'major' => 'IPS'],
            ['name' => 'XII-IPA-1','grade' => 12, 'major' => 'IPA'],
        ];

        $classes = [];
        foreach ($classesData as $cd) {
            $classes[$cd['name']] = SchoolClass::firstOrCreate(
                ['school_id' => $school->id, 'name' => $cd['name']],
                [
                    'school_year_id' => $schoolYear->id,
                    'major_id'       => $majors[$cd['major']]->id,
                    'grade'          => $cd['grade'],
                    'is_active'      => true,
                ]
            );
        }

        $this->command->info('✓ ' . count($classes) . ' Kelas');

        // ── 4. Mata Pelajaran ─────────────────────────────────────────────────
        $subjectsData = [
            ['name' => 'Matematika',         'code' => 'MTK'],
            ['name' => 'Bahasa Indonesia',   'code' => 'BIN'],
            ['name' => 'Bahasa Inggris',     'code' => 'BIG'],
            ['name' => 'Fisika',             'code' => 'FIS'],
            ['name' => 'Kimia',              'code' => 'KIM'],
            ['name' => 'Biologi',            'code' => 'BIO'],
            ['name' => 'Sejarah',            'code' => 'SEJ'],
            ['name' => 'Pendidikan Agama',   'code' => 'PAI'],
            ['name' => 'Olahraga',           'code' => 'OLR'],
            ['name' => 'Seni Budaya',        'code' => 'SBD'],
        ];

        // Cek apakah tabel subjects punya kolom code
        $hasCode = \Illuminate\Support\Facades\Schema::hasColumn('subjects', 'code');

        $subjects = [];
        foreach ($subjectsData as $sd) {
            if ($hasCode) {
                $subjects[$sd['code']] = Subject::firstOrCreate(
                    ['school_id' => $school->id, 'code' => $sd['code']],
                    ['name' => $sd['name']]
                );
            } else {
                $subjects[$sd['code']] = Subject::firstOrCreate(
                    ['school_id' => $school->id, 'name' => $sd['name']]
                );
            }
        }

        $this->command->info('✓ ' . count($subjects) . ' Mata Pelajaran');

        // ── 5. Guru ────────────────────────────────────────────────────────────
        $gurusData = [
            ['name' => 'Ahmad Fauzi, S.Pd',     'email' => 'guru.mtk@paskola.com',  'nip' => '196501011990031001'],
            ['name' => 'Sari Indah, M.Pd',      'email' => 'guru.ujian@paskola.com', 'nip' => '197203152000032001'],
            ['name' => 'Budi Santoso, S.Pd',    'email' => 'guru.ipa@paskola.com',   'nip' => '198007221005031002'],
            ['name' => 'Dewi Rahmawati, S.S',   'email' => 'guru.bing@paskola.com',  'nip' => '198212052010032003'],
            ['name' => 'Eko Prasetyo, S.Pd',    'email' => 'guru.ips@paskola.com',   'nip' => '197808121999031004'],
            ['name' => 'Budi Guru',              'email' => 'guru@paskola.com',       'nip' => '198505052010011005'],
        ];

        $teachers = [];
        foreach ($gurusData as $gd) {
            $user = User::firstOrCreate(
                ['email' => $gd['email']],
                [
                    'name'              => $gd['name'],
                    'password'          => Hash::make('password123'),
                    'school_id'         => $school->id,
                    'role'              => 'Guru',
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles(['Guru']);

            $teacher = Teacher::firstOrCreate(
                ['user_id' => $user->id],
                ['school_id' => $school->id, 'name' => $gd['name'], 'nip' => $gd['nip']]
            );

            $teachers[$gd['email']] = $teacher;
        }

        $this->command->info('✓ ' . count($teachers) . ' Guru');

        // ── 6. Siswa ───────────────────────────────────────────────────────────
        $siswaPerKelas = [
            'X-IPA-1'  => 8,
            'X-IPA-2'  => 7,
            'X-IPS-1'  => 6,
            'XI-IPA-1' => 5,
            'XI-IPS-1' => 4,
        ];

        $studentCount = 0;
        $allStudents  = [];
        $nisCounter   = 20250001;

        foreach ($siswaPerKelas as $className => $jumlah) {
            $class = $classes[$className] ?? null;
            if (!$class) continue;

            for ($i = 1; $i <= $jumlah; $i++) {
                $slug    = strtolower(str_replace(['-', ' '], ['', ''], $className));
                $email   = "siswa.{$slug}{$i}@paskola.com";
                $name    = "Siswa {$className} {$i}";
                $nis     = (string)($nisCounter++);

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'              => $name,
                        'password'          => Hash::make('password123'),
                        'school_id'         => $school->id,
                        'role'              => 'Siswa',
                        'email_verified_at' => now(),
                    ]
                );
                $user->syncRoles(['Siswa']);

                $student = Student::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'school_id'   => $school->id,
                        'name'        => $name,
                        'nis'         => $nis,
                        'nisn'        => '00' . $nis,
                        'birth_date'  => now()->subYears(16)->subDays(rand(1, 365)),
                        'gender'      => $i % 2 === 0 ? 'P' : 'L',
                        'status'      => 'active',
                        'entry_year'  => 2025,
                    ]
                );

                // Daftarkan ke kelas
                StudentClass::firstOrCreate(
                    ['student_id' => $student->id, 'class_id' => $class->id],
                    ['school_year_id' => $schoolYear->id, 'is_active' => true]
                );

                $allStudents[] = $student;
                $studentCount++;
            }
        }

        // Juga pastikan siswa dari seeder lama tetap ada
        $oldSiswa = User::firstOrCreate(
            ['email' => 'siswa@paskola.com'],
            ['name' => 'Andi Siswa', 'password' => Hash::make('password123'), 'school_id' => $school->id, 'role' => 'Siswa', 'email_verified_at' => now()]
        );
        $oldSiswa->syncRoles(['Siswa']);
        $oldStudent = Student::firstOrCreate(
            ['user_id' => $oldSiswa->id],
            ['school_id' => $school->id, 'name' => 'Andi Siswa', 'nis' => '20249999', 'nisn' => '0020249999', 'birth_date' => now()->subYears(16), 'gender' => 'L', 'status' => 'active']
        );
        $allStudents[] = $oldStudent;

        $this->command->info("✓ {$studentCount} Siswa (+ siswa lama) tersebar ke kelas");

        // ── 7. Orang Tua ──────────────────────────────────────────────────────
        $ortuData = [
            ['name' => 'Orang Tua Siswa 1', 'email' => 'ortu@paskola.com'],
            ['name' => 'Orang Tua Siswa 2', 'email' => 'ortu2@paskola.com'],
            ['name' => 'Orang Tua Siswa 3', 'email' => 'ortu3@paskola.com'],
        ];

        foreach ($ortuData as $od) {
            $user = User::firstOrCreate(
                ['email' => $od['email']],
                ['name' => $od['name'], 'password' => Hash::make('password123'), 'school_id' => $school->id, 'role' => 'Ortu', 'email_verified_at' => now()]
            );
            $user->syncRoles(['Ortu']);
        }

        $this->command->info('✓ Orang Tua');
        $this->command->info('=== MasterDataSeeder selesai ===');
        $this->command->newLine();
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin',    'superadmin@paskola.com', 'password123'],
                ['Admin',          'admin@paskola.com',      'password123'],
                ['Kepala Sekolah', 'kepsek@paskola.com',     'password123'],
                ['Guru (MTK)',     'guru.mtk@paskola.com',   'password123'],
                ['Guru (Ujian)',   'guru.ujian@paskola.com', 'password123'],
                ['Guru (IPA)',     'guru.ipa@paskola.com',   'password123'],
                ['Guru (IPS)',     'guru.ips@paskola.com',   'password123'],
                ['Siswa (X-IPA-1)','siswa.xipa11@paskola.com','password123'],
                ['Ortu',           'ortu@paskola.com',       'password123'],
            ]
        );
    }
}
