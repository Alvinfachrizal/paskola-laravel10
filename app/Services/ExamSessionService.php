<?php

namespace App\Services;

use App\Enums\ExamGradeStatus;
use App\Enums\ExamParticipantStatus;
use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamParticipant;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

/**
 * ExamSessionService
 *
 * Berisi seluruh logika inti sesi ujian:
 *   - Pembuatan peserta & kode unik
 *   - Validasi kode masuk siswa
 *   - Memulai sesi & pengacakan soal
 *   - Pengecekan batas waktu (server sebagai sumber kebenaran)
 *   - Simpan jawaban (autosave)
 *   - Finalisasi (submit / waktu habis)
 *   - Penilaian (scoring)
 *   - Ujian susulan
 *   - Approval guru
 */
class ExamSessionService
{
    // ── Bagian A: Pembuatan Peserta & Kode ──────────────────────────────────

    /**
     * Generate kode unik 8 karakter.
     * Menghindari karakter yang mudah tertukar: 0, O, 1, I, l
     */
    public function generateCode(): string
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

    /**
     * Buat exam_participants untuk semua siswa aktif di kelas-kelas yang terdaftar di ujian.
     * Dipanggil saat guru memilih kelas dan mempublish ujian.
     * Juga dipakai tombol "Sinkronkan Peserta" (idempotent — tidak menduplikasi).
     *
     * @return int Jumlah peserta baru yang berhasil ditambahkan
     */
    public function syncParticipants(Exam $exam): int
    {
        $added = 0;

        // Ambil semua class_id yang terdaftar di ujian ini
        $classIds = $exam->classes()->pluck('school_classes.id');

        // Ambil semua siswa aktif di kelas-kelas tersebut
        // (melalui student_classes → is_active = true)
        $studentIds = DB::table('student_classes')
            ->whereIn('class_id', $classIds)
            ->where('is_active', true)
            ->pluck('student_id')
            ->unique();

        foreach ($studentIds as $studentId) {
            $exists = ExamParticipant::where('exam_id', $exam->id)
                ->where('student_id', $studentId)
                ->exists();

            if (!$exists) {
                ExamParticipant::create([
                    'exam_id'      => $exam->id,
                    'student_id'   => $studentId,
                    'code'         => $this->generateCode(),
                    'status'       => ExamParticipantStatus::BelumMulai->value,
                    'grade_status' => ExamGradeStatus::MenungguApprove->value,
                ]);
                $added++;
            }
        }

        return $added;
    }

    // ── Bagian B: Validasi Kode Masuk ───────────────────────────────────────

    /**
     * Validasi kode ujian yang dimasukkan siswa.
     *
     * Urutan pengecekan (sesuai spesifikasi):
     *   1. Kode ada
     *   2. participant.student_id sama dengan siswa yang login
     *   3. Ujian berstatus published
     *   4. Waktu sekarang dalam jendela ujian (reguler atau susulan)
     *   5. Status peserta belum_mulai atau mengerjakan
     *
     * Pesan error boleh spesifik tapi tidak boleh mengungkap kepemilikan kode.
     *
     * @return array{ok: bool, participant?: ExamParticipant, error?: string}
     */
    public function validateCode(string $code, Student $student): array
    {
        // Pengecekan 1: kode ada
        $participant = ExamParticipant::where('code', strtoupper($code))->first();
        if (!$participant) {
            return ['ok' => false, 'error' => 'Kode ujian tidak ditemukan.'];
        }

        // Pengecekan 2: kode milik siswa yang login
        if ($participant->student_id !== $student->id) {
            return ['ok' => false, 'error' => 'Kode ujian tidak ditemukan.'];
            // Pesan sama agar tidak mengungkap bahwa kode milik orang lain
        }

        $exam = $participant->exam;

        // Pengecekan 3: ujian berstatus published
        if ($exam->status !== ExamStatus::Published) {
            if ($exam->status === ExamStatus::Draft) {
                return ['ok' => false, 'error' => 'Ujian belum dibuka oleh guru.'];
            }
            return ['ok' => false, 'error' => 'Ujian sudah ditutup.'];
        }

        // Pengecekan 4: dalam jendela waktu yang berlaku
        if (!$participant->isInActiveWindow()) {
            $now = now();
            if ($now->lt($exam->start_at)) {
                return ['ok' => false, 'error' => 'Ujian belum dibuka. Silakan tunggu jadwal ujian.'];
            }
            return ['ok' => false, 'error' => 'Waktu ujian sudah berakhir.'];
        }

        // Pengecekan 5: status peserta aktif
        if ($participant->status->isFinished()) {
            return ['ok' => false, 'error' => 'Anda sudah menyelesaikan ujian ini.'];
        }

        // Cek apakah waktu sudah habis meski status masih mengerjakan (lazy finalization)
        if ($participant->status === ExamParticipantStatus::Mengerjakan && $participant->isTimeExpired()) {
            $this->finalizeExpired($participant);
            return ['ok' => false, 'error' => 'Waktu ujian Anda sudah habis. Jawaban yang tersimpan sudah dinilai.'];
        }

        return ['ok' => true, 'participant' => $participant];
    }

    // ── Bagian C: Mulai Sesi & Acak Soal ────────────────────────────────────

    /**
     * Mulai sesi ujian untuk peserta.
     *
     * - Jika status belum_mulai: isi started_at, buat question_order (diacak, simpan sekali)
     * - Jika status mengerjakan: kembalikan question_order yang sudah ada (TIDAK diacak ulang)
     *
     * @return ExamParticipant yang sudah diperbarui
     */
    public function startSession(ExamParticipant $participant): ExamParticipant
    {
        if ($participant->status === ExamParticipantStatus::BelumMulai) {
            // Ambil semua ID soal dalam ujian
            $questionIds = $participant->exam->questions()->pluck('questions.id')->toArray();

            // Acak urutan dan simpan SEKALI — tidak pernah diubah lagi
            shuffle($questionIds);

            $participant->update([
                'started_at'     => now(),
                'status'         => ExamParticipantStatus::Mengerjakan->value,
                'question_order' => $questionIds,
            ]);

            $participant->refresh();
        }

        return $participant;
    }

    // ── Bagian D: Cek Batas Waktu ────────────────────────────────────────────

    /**
     * Periksa apakah peserta masih boleh mengerjakan.
     * Ini adalah "gerbang" yang harus dicek sebelum setiap request muat soal atau simpan jawaban.
     *
     * @return array{allowed: bool, remaining_seconds?: int, participant?: ExamParticipant}
     */
    public function checkTimeAllowed(ExamParticipant $participant): array
    {
        // Refresh data terbaru dari DB
        $participant->refresh();

        if ($participant->status->isFinished()) {
            return ['allowed' => false, 'remaining_seconds' => 0];
        }

        if ($participant->isTimeExpired()) {
            // Finalisasi malas: dilakukan saat siswa request berikutnya
            $participant = $this->finalizeExpired($participant);
            return ['allowed' => false, 'remaining_seconds' => 0, 'participant' => $participant];
        }

        return [
            'allowed'           => true,
            'remaining_seconds' => $participant->remainingSeconds(),
        ];
    }

    // ── Bagian E: Finalisasi Waktu Habis (Lazy) ──────────────────────────────

    /**
     * Finalisasi peserta yang sudah lewat batas waktu.
     *
     * Strategi "malas" (lazy finalization):
     *   Finalisasi tidak dilakukan oleh scheduler melainkan saat:
     *     (a) Siswa membuka kembali halaman ujian / mengirim jawaban
     *     (b) Guru membuka halaman daftar hasil
     *
     * Trade-off dibanding Laravel Scheduler:
     *   + Tidak butuh cron job (aman untuk setup Laragon tanpa task scheduler aktif)
     *   + Tidak ada overhead proses background
     *   - Nilai baru terhitung saat ada "trigger" (buka ulang / guru cek)
     *   - Jika tidak ada yang membuka, baris tetap di status 'mengerjakan' di DB
     *     meski secara logika sudah selesai (tidak ada dampak ke siswa lain)
     */
    public function finalizeExpired(ExamParticipant $participant): ExamParticipant
    {
        if ($participant->status->isFinished()) {
            return $participant; // sudah selesai, tidak perlu diproses
        }

        $score = $this->calculateScore($participant);

        $participant->update([
            'status'      => ExamParticipantStatus::WaktuHabis->value,
            'finished_at' => $participant->effectiveDeadline() ?? now(),
            'score'       => $score,
        ]);

        $participant->refresh();
        return $participant;
    }

    /**
     * Finalisasi semua peserta yang sudah waktu habis tapi status masih 'mengerjakan'.
     * Dipanggil saat guru membuka halaman daftar hasil (lazy batch finalization).
     *
     * @return int jumlah peserta yang difinalisasi
     */
    public function finalizeAllExpired(Exam $exam): int
    {
        $finalized = 0;

        $candidates = ExamParticipant::where('exam_id', $exam->id)
            ->where('status', ExamParticipantStatus::Mengerjakan->value)
            ->with('exam')
            ->get();

        foreach ($candidates as $participant) {
            if ($participant->isTimeExpired()) {
                $this->finalizeExpired($participant);
                $finalized++;
            }
        }

        return $finalized;
    }

    // ── Bagian F: Simpan Jawaban (Autosave via AJAX) ─────────────────────────

    /**
     * Simpan (upsert) jawaban siswa untuk satu soal.
     * Dipanggil setiap kali siswa memilih/mengubah jawaban.
     *
     * @return array{ok: bool, error?: string}
     */
    public function saveAnswer(ExamParticipant $participant, string $questionId, ?string $optionId): array
    {
        // Cek batas waktu terlebih dahulu (server adalah sumber kebenaran)
        $timeCheck = $this->checkTimeAllowed($participant);
        if (!$timeCheck['allowed']) {
            return ['ok' => false, 'error' => 'Waktu ujian sudah berakhir. Jawaban tidak dapat disimpan.'];
        }

        // Pastikan soal ini memang bagian dari ujian peserta
        $questionExists = $participant->exam->questions()
            ->where('questions.id', $questionId)
            ->exists();

        if (!$questionExists) {
            return ['ok' => false, 'error' => 'Soal tidak valid.'];
        }

        // Upsert: buat baru atau perbarui jawaban yang sudah ada
        ExamAnswer::updateOrCreate(
            [
                'participant_id' => $participant->id,
                'question_id'    => $questionId,
            ],
            [
                'option_id'   => $optionId, // null = batalkan jawaban
                'answered_at' => now(),
            ]
        );

        return ['ok' => true, 'remaining_seconds' => $timeCheck['remaining_seconds']];
    }

    // ── Bagian G: Submit / Selesai ───────────────────────────────────────────

    /**
     * Finalisasi saat siswa klik tombol "Selesai".
     *
     * @return array{ok: bool, score?: float, error?: string}
     */
    public function submitExam(ExamParticipant $participant): array
    {
        // Cek apakah masih dalam batas waktu
        $timeCheck = $this->checkTimeAllowed($participant);

        // Jika waktu sudah habis, finalizeExpired sudah dipanggil oleh checkTimeAllowed
        if (!$timeCheck['allowed']) {
            $participant->refresh();
            return [
                'ok'    => true,
                'score' => (float) $participant->score,
                'status'=> $participant->status->value,
            ];
        }

        $score = $this->calculateScore($participant);

        $participant->update([
            'status'      => ExamParticipantStatus::Selesai->value,
            'finished_at' => now(),
            'score'       => $score,
        ]);

        $participant->refresh();

        return [
            'ok'    => true,
            'score' => $score,
            'status'=> $participant->status->value,
        ];
    }

    // ── Bagian H: Penilaian ──────────────────────────────────────────────────

    /**
     * Hitung skor peserta.
     *
     * Rumus: (jumlah poin soal yang dijawab benar / jumlah poin seluruh soal) × 100
     * Jawaban kosong = 0. Skor disimpan sebagai 0.00 – 100.00.
     *
     * ⚠️ is_correct HANYA dicek di sini (server). Tidak pernah dikirim ke browser.
     */
    public function calculateScore(ExamParticipant $participant): float
    {
        // Ambil semua soal + poin dalam ujian ini
        $examQuestions = $participant->exam->questions()
            ->withPivot('points')
            ->get();

        $totalPoints = $examQuestions->sum('pivot.points');

        if ($totalPoints <= 0) {
            return 0.0;
        }

        // Ambil semua jawaban peserta, eager load option
        $answers = $participant->answers()->with('selectedOption')->get()
            ->keyBy('question_id');

        $earnedPoints = 0.0;

        foreach ($examQuestions as $question) {
            $answer = $answers->get($question->id);
            if ($answer && $answer->isCorrect()) {
                $earnedPoints += (float) $question->pivot->points;
            }
        }

        return round(($earnedPoints / $totalPoints) * 100, 2);
    }

    // ── Bagian I: Ujian Susulan ──────────────────────────────────────────────

    /**
     * Buka jendela waktu ujian susulan untuk peserta yang belum pernah memulai.
     *
     * Hanya peserta dengan status 'belum_mulai' yang boleh diberi susulan.
     * Peserta yang sudah mengerjakan/selesai/waktu_habis tidak tersentuh.
     *
     * @param string[] $participantIds UUID peserta yang dipilih guru (default: semua belum_mulai)
     * @return int jumlah peserta yang berhasil dibuka susulannya
     */
    public function openMakeup(Exam $exam, Carbon $from, Carbon $until, array $participantIds = []): int
    {
        $query = ExamParticipant::where('exam_id', $exam->id)
            ->where('status', ExamParticipantStatus::BelumMulai->value);

        if (!empty($participantIds)) {
            $query->whereIn('id', $participantIds);
        }

        return $query->update([
            'allowed_from'  => $from,
            'allowed_until' => $until,
        ]);
    }

    // ── Bagian J: Approval Guru ──────────────────────────────────────────────

    /**
     * Approve nilai satu peserta.
     * Approve TIDAK menulis ke student_grades / rapor.
     */
    public function approveParticipant(ExamParticipant $participant, User $approvedBy): ExamParticipant
    {
        $participant->update([
            'grade_status' => ExamGradeStatus::Approved->value,
            'approved_by'  => $approvedBy->id,
            'approved_at'  => now(),
        ]);

        $participant->refresh();
        return $participant;
    }

    /**
     * Approve semua peserta yang sudah selesai (selesai atau waktu_habis) dalam satu ujian.
     *
     * @return int jumlah peserta yang di-approve
     */
    public function approveAllFinished(Exam $exam, User $approvedBy): int
    {
        return ExamParticipant::where('exam_id', $exam->id)
            ->whereIn('status', [
                ExamParticipantStatus::Selesai->value,
                ExamParticipantStatus::WaktuHabis->value,
            ])
            ->where('grade_status', ExamGradeStatus::MenungguApprove->value)
            ->update([
                'grade_status' => ExamGradeStatus::Approved->value,
                'approved_by'  => $approvedBy->id,
                'approved_at'  => now(),
            ]);
    }

    // ── Helper: Data Soal Aman untuk Browser Siswa ───────────────────────────

    /**
     * Kembalikan data soal yang aman dikirim ke browser siswa.
     * ⚠️ is_correct TIDAK disertakan dalam output ini.
     *
     * @return array Data soal terurut sesuai question_order peserta
     */
    public function getStudentQuestions(ExamParticipant $participant): array
    {
        $order = $participant->question_order ?? [];

        // Ambil semua soal dengan opsi, tanpa is_correct
        $questions = $participant->exam->questions()
            ->with('options')
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($order as $qId) {
            $q = $questions->get($qId);
            if (!$q) continue;

            $result[] = [
                'id'           => $q->id,
                'question_text'=> $q->question_text,
                'image_path'   => $q->image_path,
                'options'      => $q->options->map(fn ($opt) => $opt->toStudentArray())->values()->toArray(),
            ];
        }

        return $result;
    }

    /**
     * Kembalikan map: question_id → option_id untuk jawaban yang sudah disimpan peserta.
     * Digunakan untuk menandai soal mana yang sudah dijawab di grid navigator.
     */
    public function getAnsweredMap(ExamParticipant $participant): array
    {
        return $participant->answers()
            ->whereNotNull('option_id')
            ->pluck('option_id', 'question_id')
            ->toArray();
    }
}
