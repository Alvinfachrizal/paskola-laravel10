<?php

namespace App\Http\Controllers\Exam;

use App\Enums\ExamStatus;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\Question;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\ExamSessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ExamController — Manajemen Ujian oleh Guru / Admin
 *
 * Sub-bagian b, c, f, g dari prompt (buat ujian, peserta, susulan, approval)
 */
class ExamController extends Controller
{
    public function __construct(private ExamSessionService $examService)
    {
    }

    // ── Index: Daftar Ujian ──────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('viewAny', Exam::class);

        $user  = auth()->user();
        $query = Exam::with(['subject', 'teacher'])
            ->where('school_id', $user->school_id);

        if ($user->hasRole('Guru')) {
            $query->where('teacher_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $exams = $query->latest()->paginate(15)->withQueryString();

        return view('exam.exams.index', compact('exams'));
    }

    // ── Create: Form Buat Ujian ──────────────────────────────────────────────

    public function create()
    {
        $this->authorize('create', Exam::class);

        $user     = auth()->user();
        $subjects = Subject::where('school_id', $user->school_id)->orderBy('name')->get();
        $classes  = SchoolClass::where('school_id', $user->school_id)->where('is_active', true)->orderBy('name')->get();
        $questions = Question::where('school_id', $user->school_id)
            ->where('teacher_id', $user->id)
            ->where('in_bank', true)
            ->with('subject')
            ->latest()
            ->get();

        return view('exam.exams.create', compact('subjects', 'classes', 'questions'));
    }

    // ── Store: Simpan Ujian Baru ─────────────────────────────────────────────

    public function store(Request $request)
    {
        $this->authorize('create', Exam::class);

        $validated = $request->validate([
            'subject_id'       => 'required|uuid|exists:subjects,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:2000',
            'start_at'         => 'required|date|after:now',
            'end_at'           => 'required|date|after:start_at',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'show_score'       => 'required|in:after_submit,after_approve',
            'class_ids'        => 'required|array|min:1',
            'class_ids.*'      => 'uuid|exists:school_classes,id',
            'question_ids'     => 'required|array|min:1',
            'question_ids.*'   => 'uuid|exists:questions,id',
            'points'           => 'required|array',
            'points.*'         => 'numeric|min:0.01|max:999.99',
        ]);

        $exam = DB::transaction(function () use ($request, $validated) {
            $user = auth()->user();

            $exam = Exam::create([
                'school_id'        => $user->school_id,
                'teacher_id'       => $user->id,
                'subject_id'       => $validated['subject_id'],
                'title'            => $validated['title'],
                'description'      => $validated['description'] ?? null,
                'start_at'         => $validated['start_at'],
                'end_at'           => $validated['end_at'],
                'duration_minutes' => $validated['duration_minutes'],
                'show_score'       => $validated['show_score'],
                'status'           => ExamStatus::Draft->value,
            ]);

            // Daftarkan kelas
            $exam->classes()->attach($validated['class_ids']);

            // Daftarkan soal beserta poin
            $questionSync = [];
            foreach ($validated['question_ids'] as $idx => $qId) {
                $questionSync[$qId] = ['points' => $validated['points'][$idx] ?? 1.00];
            }
            $exam->questions()->attach($questionSync);

            return $exam;
        });

        return redirect()->route('exam.exams.show', $exam)
            ->with('success', 'Ujian berhasil dibuat. Silakan publish bila sudah siap.');
    }

    // ── Show: Detail Ujian ───────────────────────────────────────────────────

    public function show(Exam $exam)
    {
        $this->authorize('view', $exam);
        $exam->load(['subject', 'teacher', 'classes', 'questions.options']);

        return view('exam.exams.show', compact('exam'));
    }

    // ── Edit & Update ────────────────────────────────────────────────────────

    public function edit(Exam $exam)
    {
        $this->authorize('update', $exam);

        if ($exam->status !== ExamStatus::Draft) {
            return redirect()->route('exam.exams.show', $exam)
                ->with('error', 'Ujian yang sudah dipublish tidak dapat diedit. Tutup ujian terlebih dahulu.');
        }

        $user      = auth()->user();
        $subjects  = Subject::where('school_id', $user->school_id)->orderBy('name')->get();
        $classes   = SchoolClass::where('school_id', $user->school_id)->where('is_active', true)->orderBy('name')->get();
        $questions = Question::where('school_id', $user->school_id)
            ->where('teacher_id', $user->id)->where('in_bank', true)
            ->with('subject')->latest()->get();

        $exam->load(['classes', 'questions']);

        return view('exam.exams.edit', compact('exam', 'subjects', 'classes', 'questions'));
    }

    public function update(Request $request, Exam $exam)
    {
        $this->authorize('update', $exam);

        if ($exam->status !== ExamStatus::Draft) {
            return back()->with('error', 'Ujian yang sudah dipublish tidak dapat diubah.');
        }

        $validated = $request->validate([
            'subject_id'       => 'required|uuid|exists:subjects,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:2000',
            'start_at'         => 'required|date',
            'end_at'           => 'required|date|after:start_at',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'show_score'       => 'required|in:after_submit,after_approve',
            'class_ids'        => 'required|array|min:1',
            'class_ids.*'      => 'uuid|exists:school_classes,id',
            'question_ids'     => 'required|array|min:1',
            'question_ids.*'   => 'uuid|exists:questions,id',
            'points'           => 'required|array',
            'points.*'         => 'numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request, $validated, $exam) {
            $exam->update([
                'subject_id'       => $validated['subject_id'],
                'title'            => $validated['title'],
                'description'      => $validated['description'] ?? null,
                'start_at'         => $validated['start_at'],
                'end_at'           => $validated['end_at'],
                'duration_minutes' => $validated['duration_minutes'],
                'show_score'       => $validated['show_score'],
            ]);

            $exam->classes()->sync($validated['class_ids']);

            $questionSync = [];
            foreach ($validated['question_ids'] as $idx => $qId) {
                $questionSync[$qId] = ['points' => $validated['points'][$idx] ?? 1.00];
            }
            $exam->questions()->sync($questionSync);
        });

        return redirect()->route('exam.exams.show', $exam)->with('success', 'Ujian berhasil diperbarui.');
    }

    // ── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(Exam $exam)
    {
        $this->authorize('delete', $exam);
        $exam->delete();

        return redirect()->route('exam.exams.index')->with('success', 'Ujian berhasil dihapus.');
    }

    // ── Publish Ujian ────────────────────────────────────────────────────────

    public function publish(Exam $exam)
    {
        $this->authorize('publish', $exam);

        if ($exam->questions()->count() === 0) {
            return back()->with('error', 'Ujian harus memiliki setidaknya satu soal sebelum dipublish.');
        }

        DB::transaction(function () use ($exam) {
            $exam->update(['status' => ExamStatus::Published->value]);
            $added = $this->examService->syncParticipants($exam);

            session()->flash('success', "Ujian berhasil dipublish. {$added} peserta ditambahkan.");
        });

        return redirect()->route('exam.exams.participants', $exam);
    }

    // ── Sinkronkan Peserta ────────────────────────────────────────────────────

    public function syncParticipants(Exam $exam)
    {
        $this->authorize('syncParticipants', $exam);

        $added = $this->examService->syncParticipants($exam);

        return back()->with('success', "{$added} peserta baru berhasil ditambahkan.");
    }

    // ── Halaman Peserta ───────────────────────────────────────────────────────

    public function participants(Exam $exam)
    {
        $this->authorize('viewResults', $exam);

        // Lazy finalization: periksa peserta yang sudah waktu habis
        $this->examService->finalizeAllExpired($exam);

        $exam->load(['subject', 'classes']);
        $participants = ExamParticipant::where('exam_id', $exam->id)
            ->with('student.user')
            ->orderBy('status')
            ->paginate(30);

        return view('exam.exams.participants', compact('exam', 'participants'));
    }

    // ── Halaman Hasil & Approval ──────────────────────────────────────────────

    public function results(Exam $exam)
    {
        $this->authorize('viewResults', $exam);

        $this->examService->finalizeAllExpired($exam);

        $participants = ExamParticipant::where('exam_id', $exam->id)
            ->with('student.user', 'approvedBy')
            ->whereIn('status', ['selesai', 'waktu_habis'])
            ->orderByDesc('score')
            ->get();

        return view('exam.exams.results', compact('exam', 'participants'));
    }

    // ── Approve ───────────────────────────────────────────────────────────────

    public function approve(Request $request, Exam $exam)
    {
        $this->authorize('approve', $exam);

        if ($request->filled('participant_id')) {
            $participant = ExamParticipant::findOrFail($request->participant_id);
            $this->examService->approveParticipant($participant, auth()->user());
            return back()->with('success', 'Nilai peserta berhasil disetujui.');
        }

        $count = $this->examService->approveAllFinished($exam, auth()->user());
        return back()->with('success', "{$count} peserta berhasil disetujui sekaligus.");
    }

    // ── Buka Susulan ─────────────────────────────────────────────────────────

    public function openMakeup(Request $request, Exam $exam)
    {
        $this->authorize('openMakeup', $exam);

        $request->validate([
            'allowed_from'     => 'required|date',
            'allowed_until'    => 'required|date|after:allowed_from',
            'participant_ids'  => 'nullable|array',
            'participant_ids.*'=> 'uuid',
        ]);

        $count = $this->examService->openMakeup(
            $exam,
            Carbon::parse($request->allowed_from),
            Carbon::parse($request->allowed_until),
            $request->input('participant_ids', [])
        );

        return back()->with('success', "{$count} peserta berhasil dibuka jadwal ujian susulannya.");
    }

    // ── Detail Jawaban per Peserta ────────────────────────────────────────────

    public function participantDetail(Exam $exam, ExamParticipant $participant)
    {
        $this->authorize('viewResults', $exam);

        $answers = $participant->answers()
            ->with(['question.options', 'selectedOption'])
            ->get()
            ->keyBy('question_id');

        $questions = $exam->questions()->withPivot('points')->get();

        return view('exam.exams.participant-detail', compact('exam', 'participant', 'answers', 'questions'));
    }
}
