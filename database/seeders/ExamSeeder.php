<?php

namespace Database\Seeders;

use App\Enums\ExamGradeStatus;
use App\Enums\ExamParticipantStatus;
use App\Enums\ExamShowScore;
use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamParticipant;
use App\Models\Question;
use App\Models\QuestionOption;
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
use Illuminate\Support\Str;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (!$school) {
            $this->command->error('School tidak ditemukan. Jalankan RoleAndUserSeeder terlebih dahulu.');
            return;
        }

        // ── 1. Pastikan ada guru dengan profil Teacher ─────────────────────
        $guruUser = User::firstOrCreate(
            ['email' => 'guru.ujian@paskola.com'],
            [
                'name'              => 'Sari Matematika',
                'password'          => Hash::make('password123'),
                'school_id'         => $school->id,
                'role'              => 'Guru',
                'email_verified_at' => now(),
            ]
        );
        $guruUser->assignRole('Guru');

        $guru2User = User::firstOrCreate(
            ['email' => 'guru.ipa@paskola.com'],
            [
                'name'              => 'Budi Fisika',
                'password'          => Hash::make('password123'),
                'school_id'         => $school->id,
                'role'              => 'Guru',
                'email_verified_at' => now(),
            ]
        );
        $guru2User->assignRole('Guru');

        $guruProfile = Teacher::firstOrCreate(
            ['user_id' => $guruUser->id],
            ['school_id' => $school->id, 'name' => $guruUser->name, 'nip' => 'NIP-UJIAN-01']
        );

        $guru2Profile = Teacher::firstOrCreate(
            ['user_id' => $guru2User->id],
            ['school_id' => $school->id, 'name' => $guru2User->name, 'nip' => 'NIP-UJIAN-02']
        );

        // ── 2. Pastikan ada mapel ──────────────────────────────────────────
        $matPelajaran = Subject::firstOrCreate(
            ['school_id' => $school->id, 'code' => 'MTK'],
            ['name' => 'Matematika']
        );
        $fisika = Subject::firstOrCreate(
            ['school_id' => $school->id, 'code' => 'FIS'],
            ['name' => 'Fisika']
        );

        // ── 3. Pastikan ada kelas & siswa aktif ───────────────────────────
        $schoolYear = SchoolYear::firstOrCreate(
            ['school_id' => $school->id, 'academic_year' => '2025/2026', 'semester' => 'Ganjil'],
            ['start_date' => '2025-07-01', 'end_date' => '2025-12-31', 'is_active' => true]
        );

        $kelasXA = SchoolClass::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'X-A'],
            ['school_year_id' => $schoolYear->id, 'grade' => 10, 'is_active' => true]
        );
        $kelasXB = SchoolClass::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'X-B'],
            ['school_year_id' => $schoolYear->id, 'grade' => 10, 'is_active' => true]
        );

        // Buat 5 siswa untuk kelas X-A
        $siswaXA = [];
        for ($i = 1; $i <= 5; $i++) {
            $siswaUser = User::firstOrCreate(
                ['email' => "siswa.xa{$i}@paskola.com"],
                [
                    'name'              => "Siswa X-A {$i}",
                    'password'          => Hash::make('password123'),
                    'school_id'         => $school->id,
                    'role'              => 'Siswa',
                    'email_verified_at' => now(),
                ]
            );
            $siswaUser->assignRole('Siswa');

            $student = Student::firstOrCreate(
                ['user_id' => $siswaUser->id],
                [
                    'school_id' => $school->id,
                    'name'      => $siswaUser->name,
                    'nisn'      => '200' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'status'    => 'aktif',
                ]
            );

            StudentClass::firstOrCreate(
                ['student_id' => $student->id, 'class_id' => $kelasXA->id, 'school_year_id' => $schoolYear->id],
                ['roll_number' => $i, 'is_active' => true]
            );

            $siswaXA[] = $student;
        }

        // Buat 3 siswa untuk kelas X-B
        $siswaXB = [];
        for ($i = 1; $i <= 3; $i++) {
            $siswaUser = User::firstOrCreate(
                ['email' => "siswa.xb{$i}@paskola.com"],
                [
                    'name'              => "Siswa X-B {$i}",
                    'password'          => Hash::make('password123'),
                    'school_id'         => $school->id,
                    'role'              => 'Siswa',
                    'email_verified_at' => now(),
                ]
            );
            $siswaUser->assignRole('Siswa');

            $student = Student::firstOrCreate(
                ['user_id' => $siswaUser->id],
                [
                    'school_id' => $school->id,
                    'name'      => $siswaUser->name,
                    'nisn'      => '200' . str_pad($i + 5, 5, '0', STR_PAD_LEFT),
                    'status'    => 'aktif',
                ]
            );

            StudentClass::firstOrCreate(
                ['student_id' => $student->id, 'class_id' => $kelasXB->id, 'school_year_id' => $schoolYear->id],
                ['roll_number' => $i, 'is_active' => true]
            );

            $siswaXB[] = $student;
        }

        // ── 4. Buat Bank Soal Matematika (15 soal, in_bank = true) ────────
        $this->command->info('Membuat bank soal Matematika...');
        $bankSoalMtk = [];

        $soalData = [
            [
                'text' => 'Berapakah hasil dari 12 × 15?',
                'options' => ['150', '160', '180', '200'],
                'correct' => 2, // C = 180
            ],
            [
                'text' => 'Nilai x yang memenuhi persamaan 3x + 6 = 21 adalah...',
                'options' => ['3', '4', '5', '6'],
                'correct' => 2, // C = 5
            ],
            [
                'text' => 'Hasil dari 2³ + 3² adalah...',
                'options' => ['13', '15', '17', '19'],
                'correct' => 2, // C = 17
            ],
            [
                'text' => 'Luas persegi dengan sisi 8 cm adalah...',
                'options' => ['32 cm²', '48 cm²', '64 cm²', '72 cm²'],
                'correct' => 2, // C = 64
            ],
            [
                'text' => 'FPB dari 24 dan 36 adalah...',
                'options' => ['6', '8', '12', '18'],
                'correct' => 2, // C = 12
            ],
            [
                'text' => 'Hasil dari √144 adalah...',
                'options' => ['10', '11', '12', '13'],
                'correct' => 2, // C = 12
            ],
            [
                'text' => 'Jika a = 4 dan b = 3, maka nilai 2a² - b adalah...',
                'options' => ['25', '29', '31', '35'],
                'correct' => 1, // B = 29
            ],
            [
                'text' => 'KPK dari 4, 6, dan 8 adalah...',
                'options' => ['12', '16', '24', '48'],
                'correct' => 2, // C = 24
            ],
            [
                'text' => 'Sebuah lingkaran memiliki jari-jari 7 cm. Luasnya adalah... (π = 22/7)',
                'options' => ['44 cm²', '88 cm²', '154 cm²', '176 cm²'],
                'correct' => 2, // C = 154
            ],
            [
                'text' => 'Bentuk sederhana dari 18/24 adalah...',
                'options' => ['1/2', '2/3', '3/4', '5/6'],
                'correct' => 2, // C = 3/4
            ],
            [
                'text' => 'Pecahan 3/5 jika diubah ke persen menjadi...',
                'options' => ['35%', '40%', '60%', '65%'],
                'correct' => 2, // C = 60%
            ],
            [
                'text' => 'Himpunan bilangan prima antara 10 dan 20 adalah...',
                'options' => ['{11, 13}', '{11, 13, 17}', '{11, 13, 17, 19}', '{13, 17, 19}'],
                'correct' => 2, // C = {11, 13, 17, 19}
            ],
            [
                'text' => 'Perhatikan barisan: 2, 5, 8, 11, ... Suku ke-10 adalah...',
                'options' => ['28', '29', '30', '31'],
                'correct' => 0, // A = 29 ... 2 + (10-1)*3 = 29
            ],
            [
                'text' => 'Jika suatu segitiga memiliki alas 10 cm dan tinggi 6 cm, luasnya adalah...',
                'options' => ['24 cm²', '30 cm²', '36 cm²', '60 cm²'],
                'correct' => 1, // B = 30
            ],
            [
                'text' => 'Bilangan bulat manakah yang terletak di antara -3 dan 2?',
                'options' => ['-4, -3, -2, -1, 0, 1, 2, 3', '-2, -1, 0, 1', '-2, -1, 0, 1, 2', '-3, -2, -1, 0, 1'],
                'correct' => 1, // B = -2, -1, 0, 1
            ],
        ];

        foreach ($soalData as $idx => $s) {
            $soal = Question::firstOrCreate(
                [
                    'school_id'     => $school->id,
                    'teacher_id'    => $guruUser->id,
                    'subject_id'    => $matPelajaran->id,
                    'question_text' => $s['text'],
                ],
                ['in_bank' => true]
            );

            foreach ($s['options'] as $pos => $optText) {
                QuestionOption::firstOrCreate(
                    ['question_id' => $soal->id, 'position' => $pos + 1],
                    [
                        'option_text' => $optText,
                        'is_correct'  => ($pos === $s['correct']),
                    ]
                );
            }

            $bankSoalMtk[] = $soal;
        }
        $this->command->info(count($bankSoalMtk) . ' soal Matematika berhasil dibuat.');

        // ── 5. Buat Bank Soal Fisika (5 soal, in_bank = true) ─────────────
        $bankSoalFis = [];
        $soalFisikaData = [
            [
                'text'    => 'Satuan kecepatan dalam sistem SI adalah...',
                'options' => ['km/jam', 'm/s', 'cm/s', 'm/menit'],
                'correct' => 1, // B = m/s
            ],
            [
                'text'    => 'Sebuah benda bergerak dengan kecepatan 20 m/s selama 5 sekon. Jarak yang ditempuh adalah...',
                'options' => ['80 m', '100 m', '120 m', '150 m'],
                'correct' => 1, // B = 100 m
            ],
            [
                'text'    => 'Hukum Newton yang menyatakan "setiap aksi ada reaksi yang sama besar dan berlawanan arah" adalah...',
                'options' => ['Hukum Newton I', 'Hukum Newton II', 'Hukum Newton III', 'Hukum Archimedes'],
                'correct' => 2, // C = Hukum Newton III
            ],
            [
                'text'    => 'Besaran yang hanya memiliki nilai tanpa arah disebut...',
                'options' => ['Vektor', 'Skalar', 'Tensor', 'Koordinat'],
                'correct' => 1, // B = Skalar
            ],
            [
                'text'    => 'Energi potensial benda bermassa 2 kg pada ketinggian 5 m (g = 10 m/s²) adalah...',
                'options' => ['50 J', '80 J', '100 J', '120 J'],
                'correct' => 2, // C = 100 J
            ],
        ];

        foreach ($soalFisikaData as $s) {
            $soal = Question::firstOrCreate(
                [
                    'school_id'     => $school->id,
                    'teacher_id'    => $guru2User->id,
                    'subject_id'    => $fisika->id,
                    'question_text' => $s['text'],
                ],
                ['in_bank' => true]
            );

            foreach ($s['options'] as $pos => $optText) {
                QuestionOption::firstOrCreate(
                    ['question_id' => $soal->id, 'position' => $pos + 1],
                    [
                        'option_text' => $optText,
                        'is_correct'  => ($pos === $s['correct']),
                    ]
                );
            }

            $bankSoalFis[] = $soal;
        }
        $this->command->info(count($bankSoalFis) . ' soal Fisika berhasil dibuat.');

        // ── 6. Buat Ujian 1: MTK — sudah selesai, beragam status peserta ──
        $this->command->info('Membuat Ujian Matematika (sudah selesai)...');

        $examMtkSelesai = Exam::firstOrCreate(
            ['school_id' => $school->id, 'title' => 'UTS Matematika Kelas X'],
            [
                'teacher_id'       => $guruUser->id,
                'subject_id'       => $matPelajaran->id,
                'description'      => 'Ujian Tengah Semester Matematika untuk kelas X semester ganjil 2025/2026.',
                'start_at'         => now()->subDays(3),
                'end_at'           => now()->subDays(3)->addHours(2),
                'duration_minutes' => 60,
                'show_score'       => ExamShowScore::AfterApprove->value,
                'status'           => ExamStatus::Closed->value,
            ]
        );

        // Daftarkan 10 soal ke ujian ini
        $soal10 = array_slice($bankSoalMtk, 0, 10);
        foreach ($soal10 as $soal) {
            $examMtkSelesai->questions()->syncWithoutDetaching([$soal->id => ['points' => 10.00]]);
        }

        // Daftarkan kelas X-A
        $examMtkSelesai->classes()->syncWithoutDetaching([$kelasXA->id]);

        $questionIds = collect($soal10)->pluck('id')->toArray();

        // Buat peserta dengan status beragam
        foreach ($siswaXA as $idx => $student) {
            $existing = ExamParticipant::where('exam_id', $examMtkSelesai->id)
                ->where('student_id', $student->id)->first();
            if ($existing) continue;

            // Acak urutan soal untuk tiap peserta
            $shuffled = $questionIds;
            shuffle($shuffled);

            $statusData = match(true) {
                $idx === 0 => ['status' => ExamParticipantStatus::Selesai, 'grade_status' => ExamGradeStatus::Approved, 'score' => 90.00],
                $idx === 1 => ['status' => ExamParticipantStatus::Selesai, 'grade_status' => ExamGradeStatus::Approved, 'score' => 70.00],
                $idx === 2 => ['status' => ExamParticipantStatus::WaktuHabis, 'grade_status' => ExamGradeStatus::MenungguApprove, 'score' => 50.00],
                $idx === 3 => ['status' => ExamParticipantStatus::Selesai, 'grade_status' => ExamGradeStatus::MenungguApprove, 'score' => 80.00],
                default    => ['status' => ExamParticipantStatus::BelumMulai, 'grade_status' => ExamGradeStatus::MenungguApprove, 'score' => null],
            };

            $startedAt = ($idx < 4) ? now()->subDays(3)->addMinutes(5) : null;
            $finishedAt = in_array($statusData['status'], [ExamParticipantStatus::Selesai, ExamParticipantStatus::WaktuHabis])
                ? $startedAt?->copy()->addMinutes(55)
                : null;

            $participant = ExamParticipant::create([
                'exam_id'      => $examMtkSelesai->id,
                'student_id'   => $student->id,
                'code'         => $this->generateCode(),
                'question_order' => $shuffled,
                'started_at'   => $startedAt,
                'finished_at'  => $finishedAt,
                'score'        => $statusData['score'],
                'status'       => $statusData['status']->value,
                'grade_status' => $statusData['grade_status']->value,
                'approved_by'  => $statusData['grade_status'] === ExamGradeStatus::Approved ? $guruUser->id : null,
                'approved_at'  => $statusData['grade_status'] === ExamGradeStatus::Approved ? now()->subDays(2) : null,
            ]);

            // Buat jawaban untuk siswa yang sudah mengerjakan
            if ($startedAt) {
                foreach ($soal10 as $soal) {
                    $options = $soal->options()->get();
                    if ($options->isEmpty()) continue;
                    // Pilih jawaban acak (kadang benar kadang salah)
                    $chosen = $options->random();
                    ExamAnswer::updateOrCreate(
                        ['participant_id' => $participant->id, 'question_id' => $soal->id],
                        ['option_id' => $chosen->id, 'answered_at' => $startedAt->copy()->addMinutes(rand(2, 50))]
                    );
                }
            }
        }
        $this->command->info('Ujian 1 (Matematika selesai) selesai dibuat.');

        // ── 7. Buat Ujian 2: MTK — sedang berjalan (aktif sekarang) ───────
        $this->command->info('Membuat Ujian Matematika (sedang berjalan)...');

        $examMtkAktif = Exam::firstOrCreate(
            ['school_id' => $school->id, 'title' => 'UAS Matematika Kelas X'],
            [
                'teacher_id'       => $guruUser->id,
                'subject_id'       => $matPelajaran->id,
                'description'      => 'Ujian Akhir Semester Matematika. Kerjakan dengan jujur dan teliti.',
                'start_at'         => now()->subHour(),
                'end_at'           => now()->addHours(2),
                'duration_minutes' => 90,
                'show_score'       => ExamShowScore::AfterApprove->value,
                'status'           => ExamStatus::Published->value,
            ]
        );

        // Pakai semua 15 soal MTK
        foreach ($bankSoalMtk as $soal) {
            $examMtkAktif->questions()->syncWithoutDetaching([$soal->id => ['points' => 6.67]]);
        }
        $examMtkAktif->classes()->syncWithoutDetaching([$kelasXA->id, $kelasXB->id]);

        // Buat peserta dari kedua kelas
        foreach (array_merge($siswaXA, $siswaXB) as $student) {
            if (ExamParticipant::where('exam_id', $examMtkAktif->id)->where('student_id', $student->id)->exists()) continue;

            ExamParticipant::create([
                'exam_id'      => $examMtkAktif->id,
                'student_id'   => $student->id,
                'code'         => $this->generateCode(),
                'question_order' => null,
                'started_at'   => null,
                'finished_at'  => null,
                'score'        => null,
                'status'       => ExamParticipantStatus::BelumMulai->value,
                'grade_status' => ExamGradeStatus::MenungguApprove->value,
            ]);
        }
        $this->command->info('Ujian 2 (Matematika aktif) selesai dibuat.');

        // ── 8. Buat Ujian 3: Fisika — draft ───────────────────────────────
        $examFisDraft = Exam::firstOrCreate(
            ['school_id' => $school->id, 'title' => 'Quiz Fisika — Hukum Newton'],
            [
                'teacher_id'       => $guru2User->id,
                'subject_id'       => $fisika->id,
                'description'      => 'Kuis singkat tentang Hukum Newton.',
                'start_at'         => now()->addDays(7),
                'end_at'           => now()->addDays(7)->addHour(),
                'duration_minutes' => 30,
                'show_score'       => ExamShowScore::AfterSubmit->value,
                'status'           => ExamStatus::Draft->value,
            ]
        );

        foreach ($bankSoalFis as $soal) {
            $examFisDraft->questions()->syncWithoutDetaching([$soal->id => ['points' => 20.00]]);
        }
        $examFisDraft->classes()->syncWithoutDetaching([$kelasXA->id]);

        $this->command->info('Ujian 3 (Fisika draft) selesai dibuat.');
        $this->command->info('');
        $this->command->info('=== ExamSeeder Selesai ===');
        $this->command->info('Akun login demo:');
        $this->command->info('  Guru MTK: guru.ujian@paskola.com / password123');
        $this->command->info('  Guru FIS: guru.ipa@paskola.com / password123');
        $this->command->info('  Siswa X-A 1: siswa.xa1@paskola.com / password123');
        $this->command->info('  Siswa X-A 2: siswa.xa2@paskola.com / password123');
        $this->command->info('  Siswa X-B 1: siswa.xb1@paskola.com / password123');
    }

    /**
     * Generate kode unik 8 karakter, tanpa karakter yang mirip (0, O, 1, I, l).
     */
    private function generateCode(): string
    {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (ExamParticipant::where('code', $code)->exists());

        return $code;
    }
}
