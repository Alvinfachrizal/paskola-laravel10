<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\Student;
use App\Services\ExamSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * ExamSessionController — Sesi ujian dari sisi siswa
 *
 * Sub-bagian d (input kode, halaman mengerjakan), e (submit/waktu habis)
 */
class ExamSessionController extends Controller
{
    public function __construct(private ExamSessionService $examService)
    {
    }

    // ── Daftar Ujian Siswa ───────────────────────────────────────────────────

    public function myExams()
    {
        $student = $this->getStudentOrAbort();

        // Ambil semua kelas aktif siswa ini
        $classIds = $student->classes()->wherePivot('is_active', true)->pluck('school_classes.id');

        // Ujian yang kelas siswa terdaftar
        $exams = Exam::whereHas('classes', fn ($q) => $q->whereIn('school_classes.id', $classIds))
            ->where('status', '!=', 'draft')
            ->with(['subject', 'teacher'])
            ->latest('start_at')
            ->get();

        // Attach status peserta untuk setiap ujian
        $participants = ExamParticipant::where('student_id', $student->id)
            ->whereIn('exam_id', $exams->pluck('id'))
            ->get()
            ->keyBy('exam_id');

        return view('exam.session.my-exams', compact('exams', 'participants'));
    }

    // ── Halaman Input Kode ───────────────────────────────────────────────────

    public function showEnterCode()
    {
        return view('exam.session.enter-code');
    }

    public function enterCode(Request $request)
    {
        $request->validate(['code' => 'required|string|min:6|max:10']);

        $student = $this->getStudentOrAbort();

        // Rate limiting: max 5 kali per 10 menit per user
        $rateLimitKey = 'exam-code-attempt:' . auth()->id();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->withErrors(['code' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik."])->withInput();
        }

        $result = $this->examService->validateCode($request->code, $student);

        if (!$result['ok']) {
            RateLimiter::hit($rateLimitKey, 600); // 10 menit
            return back()->withErrors(['code' => $result['error']])->withInput();
        }

        // Kode valid → reset rate limiter, mulai sesi
        RateLimiter::clear($rateLimitKey);

        $participant = $this->examService->startSession($result['participant']);

        return redirect()->route('exam.session.show', $participant);
    }

    // ── Halaman Mengerjakan Ujian ─────────────────────────────────────────────

    public function show(ExamParticipant $participant)
    {
        $this->authorize('takeExam', $participant);

        // Cek waktu sebelum tampilkan soal
        $timeCheck = $this->examService->checkTimeAllowed($participant);
        if (!$timeCheck['allowed']) {
            return redirect()->route('exam.session.result', $participant)
                ->with('info', 'Waktu ujian Anda sudah berakhir.');
        }

        // Jika belum mulai (edge case): mulai dulu
        if ($participant->status->value === 'belum_mulai') {
            $participant = $this->examService->startSession($participant);
        }

        $questions     = $this->examService->getStudentQuestions($participant);
        $answeredMap   = $this->examService->getAnsweredMap($participant);
        $remainingSeconds = $timeCheck['remaining_seconds'];

        return view('exam.session.show', compact('participant', 'questions', 'answeredMap', 'remainingSeconds'));
    }

    // ── Halaman Hasil ────────────────────────────────────────────────────────

    public function result(ExamParticipant $participant)
    {
        $this->authorize('takeExam', $participant);

        // Lazy finalize jika belum
        if ($participant->isTimeExpired() && !$participant->status->isFinished()) {
            $participant = $this->examService->finalizeExpired($participant);
        }

        $exam = $participant->exam()->with('subject')->first();

        // Tampilkan nilai sesuai show_score
        $canSeeScore = $participant->status->isFinished() &&
            ($exam->show_score->value === 'after_submit' ||
             ($exam->show_score->value === 'after_approve' && $participant->grade_status->value === 'approved'));

        return view('exam.session.result', compact('participant', 'exam', 'canSeeScore'));
    }

    // ── AJAX: Simpan Jawaban ──────────────────────────────────────────────────

    public function saveAnswer(Request $request, ExamParticipant $participant)
    {
        $this->authorize('takeExam', $participant);

        $request->validate([
            'question_id' => 'required|uuid',
            'option_id'   => 'nullable|uuid',
        ]);

        $result = $this->examService->saveAnswer(
            $participant,
            $request->question_id,
            $request->option_id
        );

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    // ── AJAX: Submit Ujian ────────────────────────────────────────────────────

    public function submit(Request $request, ExamParticipant $participant)
    {
        $this->authorize('takeExam', $participant);

        $result = $this->examService->submitExam($participant);

        if (request()->expectsJson()) {
            return response()->json($result);
        }

        return redirect()->route('exam.session.result', $participant)
            ->with('success', 'Ujian berhasil diselesaikan.');
    }

    // ── AJAX: Sisa Waktu ─────────────────────────────────────────────────────

    public function getTime(ExamParticipant $participant)
    {
        $this->authorize('takeExam', $participant);

        $timeCheck = $this->examService->checkTimeAllowed($participant);

        return response()->json([
            'allowed'           => $timeCheck['allowed'],
            'remaining_seconds' => $timeCheck['remaining_seconds'] ?? 0,
        ]);
    }

    // ── Private Helper ───────────────────────────────────────────────────────

    private function getStudentOrAbort(): Student
    {
        $user    = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        return $student;
    }
}
