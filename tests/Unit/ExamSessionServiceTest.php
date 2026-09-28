<?php

namespace Tests\Unit;

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
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\ExamSessionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit Test untuk ExamSessionService
 *
 * Skenario yang diuji (sesuai spesifikasi prompt):
 *   1. Kode milik siswa lain ditolak
 *   2. Urutan soal tetap sama setelah dimuat berulang kali
 *   3. Jawaban ditolak setelah waktu habis
 *   4. Siswa belum_mulai bisa masuk saat jendela susulan dibuka
 *   4b. Siswa yang sudah selesai tidak bisa masuk via susulan
 *   5. Perhitungan skor benar untuk berbagai kombinasi jawaban
 *
 * Jalankan dengan: php artisan test --filter=ExamSessionServiceTest
 */
class ExamSessionServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExamSessionService $service;
    private School $school;
    private SchoolYear $schoolYear;
    private User $guruUser;
    private Subject $subject;
    private SchoolClass $kelas;
    private Student $siswaA;
    private Student $siswaB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ExamSessionService::class);

        // Data dasar
        $this->school = School::create([
            'name'    => 'SMA Test',
            'address' => 'Jl. Test',
        ]);

        $this->schoolYear = SchoolYear::create([
            'school_id'     => $this->school->id,
            'academic_year' => '2025/2026',
            'semester'      => 'Ganjil',
            'start_date'    => '2025-07-01',
            'end_date'      => '2025-12-31',
            'is_active'     => true,
        ]);

        $this->guruUser = User::factory()->create([
            'school_id' => $this->school->id,
        ]);

        $this->subject = Subject::create([
            'school_id' => $this->school->id,
            'name'      => 'Matematika',
            'code'      => 'MTK',
        ]);

        $this->kelas = SchoolClass::create([
            'school_id'      => $this->school->id,
            'school_year_id' => $this->schoolYear->id,
            'name'           => 'X-A',
            'grade'          => 10,
            'is_active'      => true,
        ]);

        // Buat 2 siswa dengan akun User
        $userA = User::factory()->create(['school_id' => $this->school->id]);
        $this->siswaA = Student::create([
            'school_id' => $this->school->id,
            'user_id'   => $userA->id,
            'name'      => 'Siswa A',
            'nisn'      => '001',
            'status'    => 'aktif',
        ]);
        StudentClass::create([
            'student_id'     => $this->siswaA->id,
            'class_id'       => $this->kelas->id,
            'school_year_id' => $this->schoolYear->id,
            'roll_number'    => 1,
            'is_active'      => true,
        ]);

        $userB = User::factory()->create(['school_id' => $this->school->id]);
        $this->siswaB = Student::create([
            'school_id' => $this->school->id,
            'user_id'   => $userB->id,
            'name'      => 'Siswa B',
            'nisn'      => '002',
            'status'    => 'aktif',
        ]);
        StudentClass::create([
            'student_id'     => $this->siswaB->id,
            'class_id'       => $this->kelas->id,
            'school_year_id' => $this->schoolYear->id,
            'roll_number'    => 2,
            'is_active'      => true,
        ]);
    }

    // ── Helper: Buat ujian aktif + soal + peserta ────────────────────────────

    private function makeActiveExam(int $durationMinutes = 60): Exam
    {
        $exam = Exam::create([
            'school_id'        => $this->school->id,
            'teacher_id'       => $this->guruUser->id,
            'subject_id'       => $this->subject->id,
            'title'            => 'Ujian Test',
            'start_at'         => now()->subMinutes(5),
            'end_at'           => now()->addHours(2),
            'duration_minutes' => $durationMinutes,
            'show_score'       => ExamShowScore::AfterApprove->value,
            'status'           => ExamStatus::Published->value,
        ]);

        $exam->classes()->attach($this->kelas->id);

        // Buat 5 soal dengan opsi
        for ($i = 1; $i <= 5; $i++) {
            $soal = Question::create([
                'school_id'     => $this->school->id,
                'teacher_id'    => $this->guruUser->id,
                'subject_id'    => $this->subject->id,
                'question_text' => "Soal nomor {$i}",
                'in_bank'       => true,
            ]);

            for ($pos = 1; $pos <= 4; $pos++) {
                QuestionOption::create([
                    'question_id' => $soal->id,
                    'option_text' => "Pilihan {$pos} soal {$i}",
                    'position'    => $pos,
                    'is_correct'  => ($pos === 1), // pilihan A = benar
                ]);
            }

            $exam->questions()->attach($soal->id, ['points' => 20.00]); // total = 100 poin
        }

        return $exam;
    }

    private function makeParticipant(Exam $exam, Student $student, string $status = 'belum_mulai'): ExamParticipant
    {
        return ExamParticipant::create([
            'exam_id'      => $exam->id,
            'student_id'   => $student->id,
            'code'         => 'KODE' . strtoupper(substr($student->id, 0, 4)),
            'status'       => $status,
            'grade_status' => ExamGradeStatus::MenungguApprove->value,
        ]);
    }

    // ── Skenario 1: Kode milik siswa lain ditolak ────────────────────────────

    /**
     * Siswa A mencoba memasukkan kode milik Siswa B → harus ditolak
     * dengan pesan yang sama seperti "kode tidak ditemukan" (tidak mengungkap kepemilikan).
     */
    public function test_kode_milik_siswa_lain_ditolak(): void
    {
        $exam = $this->makeActiveExam();

        // Buat peserta untuk Siswa B
        $participantB = $this->makeParticipant($exam, $this->siswaB);
        $kodeB = $participantB->code;

        // Siswa A mencoba kode Siswa B
        $result = $this->service->validateCode($kodeB, $this->siswaA);

        $this->assertFalse($result['ok']);
        // Pesan tidak boleh menyebut "bukan milik Anda" atau sejenisnya
        $this->assertStringContainsString('tidak ditemukan', $result['error']);
    }

    // ── Skenario 2: Urutan soal tetap sama setelah dimuat berulang ───────────

    /**
     * question_order dibuat sekali saat startSession() pertama.
     * Pemanggilan startSession() kedua tidak boleh mengacak ulang.
     */
    public function test_urutan_soal_tetap_sama_setelah_muat_berulang(): void
    {
        $exam = $this->makeActiveExam();
        $participant = $this->makeParticipant($exam, $this->siswaA);

        // Mulai sesi pertama kali
        $p1 = $this->service->startSession($participant);
        $order1 = $p1->question_order;

        $this->assertNotNull($order1);
        $this->assertCount(5, $order1);

        // Muat ulang (simulasi refresh halaman)
        $participant->refresh();
        $p2 = $this->service->startSession($participant);
        $order2 = $p2->question_order;

        // Urutan HARUS sama persis
        $this->assertEquals($order1, $order2);
    }

    // ── Skenario 3: Jawaban ditolak setelah waktu habis ─────────────────────

    /**
     * Buat ujian berdurasi sangat pendek, mundurkan started_at ke masa lalu
     * sehingga waktu sudah habis. Coba simpan jawaban → harus ditolak.
     */
    public function test_jawaban_ditolak_setelah_waktu_habis(): void
    {
        $exam = $this->makeActiveExam(durationMinutes: 1); // durasi 1 menit
        $participant = $this->makeParticipant($exam, $this->siswaA, 'mengerjakan');

        // Mundurkan started_at agar waktu sudah habis
        $participant->update([
            'started_at'     => now()->subMinutes(5), // sudah 5 menit, padahal durasi 1 menit
            'question_order' => $exam->questions()->pluck('questions.id')->toArray(),
        ]);
        $participant->refresh();

        $soal = $exam->questions()->first();
        $opsi = $soal->options()->first();

        $result = $this->service->saveAnswer($participant, $soal->id, $opsi->id);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('berakhir', $result['error']);

        // Status peserta juga harus berubah menjadi waktu_habis (lazy finalization)
        $participant->refresh();
        $this->assertEquals(ExamParticipantStatus::WaktuHabis, $participant->status);
    }

    // ── Skenario 4a: Siswa belum_mulai bisa masuk saat jendela susulan ───────

    public function test_siswa_belum_mulai_bisa_masuk_saat_jendela_susulan(): void
    {
        // Buat ujian yang sudah berakhir (end_at di masa lalu)
        $exam = Exam::create([
            'school_id'        => $this->school->id,
            'teacher_id'       => $this->guruUser->id,
            'subject_id'       => $this->subject->id,
            'title'            => 'Ujian Susulan Test',
            'start_at'         => now()->subDays(2),
            'end_at'           => now()->subDay(), // sudah selesai kemarin
            'duration_minutes' => 60,
            'show_score'       => ExamShowScore::AfterApprove->value,
            'status'           => ExamStatus::Published->value,
        ]);
        $exam->classes()->attach($this->kelas->id);

        // Tambah soal
        $soal = Question::create([
            'school_id' => $this->school->id, 'teacher_id' => $this->guruUser->id,
            'subject_id' => $this->subject->id, 'question_text' => 'Soal 1', 'in_bank' => true,
        ]);
        QuestionOption::create(['question_id' => $soal->id, 'option_text' => 'A', 'position' => 1, 'is_correct' => true]);
        $exam->questions()->attach($soal->id, ['points' => 100]);

        $participant = $this->makeParticipant($exam, $this->siswaA, 'belum_mulai');

        // Guru buka jendela susulan untuk sekarang
        $this->service->openMakeup(
            $exam,
            from: now()->subHour(),
            until: now()->addHour(),
            participantIds: [$participant->id]
        );

        $participant->refresh();

        // Validasi kode sekarang harus berhasil
        $result = $this->service->validateCode($participant->code, $this->siswaA);

        $this->assertTrue($result['ok'], 'Siswa dengan susulan aktif seharusnya bisa masuk. Error: ' . ($result['error'] ?? 'none'));
    }

    // ── Skenario 4b: Siswa yang sudah selesai tidak bisa masuk via susulan ───

    public function test_siswa_yang_sudah_selesai_tidak_bisa_masuk(): void
    {
        $exam = $this->makeActiveExam();
        $participant = $this->makeParticipant($exam, $this->siswaA, 'selesai');
        $participant->update(['score' => 80.00, 'finished_at' => now()]);

        $result = $this->service->validateCode($participant->code, $this->siswaA);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('sudah menyelesaikan', $result['error']);
    }

    // ── Skenario 5: Perhitungan skor untuk berbagai kombinasi jawaban ─────────

    /**
     * 5 soal, masing-masing 20 poin.
     * Jawaban benar = pilihan A (position 1).
     */
    public function test_skor_semua_benar_adalah_100(): void
    {
        $exam = $this->makeActiveExam();
        $participant = $this->makeParticipant($exam, $this->siswaA, 'mengerjakan');
        $participant->update(['started_at' => now(), 'question_order' => $exam->questions()->pluck('questions.id')->toArray()]);
        $participant->refresh();

        // Jawab semua soal dengan pilihan BENAR (position 1 = is_correct)
        foreach ($exam->questions as $soal) {
            $benar = $soal->options()->where('is_correct', true)->first();
            ExamAnswer::create([
                'participant_id' => $participant->id,
                'question_id'    => $soal->id,
                'option_id'      => $benar->id,
                'answered_at'    => now(),
            ]);
        }

        $skor = $this->service->calculateScore($participant);
        $this->assertEquals(100.0, $skor);
    }

    public function test_skor_semua_salah_adalah_0(): void
    {
        $exam = $this->makeActiveExam();
        $participant = $this->makeParticipant($exam, $this->siswaA, 'mengerjakan');
        $participant->update(['started_at' => now(), 'question_order' => $exam->questions()->pluck('questions.id')->toArray()]);
        $participant->refresh();

        // Jawab semua soal dengan pilihan SALAH (position 2)
        foreach ($exam->questions as $soal) {
            $salah = $soal->options()->where('is_correct', false)->first();
            ExamAnswer::create([
                'participant_id' => $participant->id,
                'question_id'    => $soal->id,
                'option_id'      => $salah->id,
                'answered_at'    => now(),
            ]);
        }

        $skor = $this->service->calculateScore($participant);
        $this->assertEquals(0.0, $skor);
    }

    public function test_skor_sebagian_benar(): void
    {
        $exam = $this->makeActiveExam(); // 5 soal @ 20 poin = 100 total
        $participant = $this->makeParticipant($exam, $this->siswaA, 'mengerjakan');
        $participant->update(['started_at' => now(), 'question_order' => $exam->questions()->pluck('questions.id')->toArray()]);
        $participant->refresh();

        $soals = $exam->questions()->get();

        // Benarkan 3 soal pertama, salahkan 2 sisanya
        foreach ($soals as $idx => $soal) {
            if ($idx < 3) {
                $opsi = $soal->options()->where('is_correct', true)->first();
            } else {
                $opsi = $soal->options()->where('is_correct', false)->first();
            }
            ExamAnswer::create([
                'participant_id' => $participant->id,
                'question_id'    => $soal->id,
                'option_id'      => $opsi->id,
                'answered_at'    => now(),
            ]);
        }

        $skor = $this->service->calculateScore($participant);
        $this->assertEquals(60.0, $skor); // 3/5 soal × 100 = 60
    }

    public function test_skor_tanpa_jawaban_adalah_0(): void
    {
        $exam = $this->makeActiveExam();
        $participant = $this->makeParticipant($exam, $this->siswaA, 'mengerjakan');
        $participant->update(['started_at' => now(), 'question_order' => $exam->questions()->pluck('questions.id')->toArray()]);
        $participant->refresh();

        // Tidak ada jawaban sama sekali
        $skor = $this->service->calculateScore($participant);
        $this->assertEquals(0.0, $skor);
    }
}
