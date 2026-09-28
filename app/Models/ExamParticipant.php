<?php

namespace App\Models;

use App\Enums\ExamParticipantStatus;
use App\Enums\ExamGradeStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ExamParticipant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'exam_id',
        'student_id',
        'code',
        'question_order',
        'started_at',
        'finished_at',
        'score',
        'status',
        'allowed_from',
        'allowed_until',
        'grade_status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'question_order' => 'array',
        'started_at'     => 'datetime',
        'finished_at'    => 'datetime',
        'score'          => 'decimal:2',
        'status'         => ExamParticipantStatus::class,
        'grade_status'   => ExamGradeStatus::class,
        'allowed_from'   => 'datetime',
        'allowed_until'  => 'datetime',
        'approved_at'    => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────────

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'participant_id');
    }

    // ── Helpers (Business Logic Ringan) ─────────────────────────────────────

    /**
     * Hitung batas waktu efektif peserta ini.
     * Batas = lebih awal antara:
     *   (a) started_at + duration_minutes
     *   (b) penutup jendela: allowed_until ?? exam.end_at
     */
    public function effectiveDeadline(): ?Carbon
    {
        if (!$this->started_at) {
            return null;
        }

        $durationDeadline = $this->started_at->copy()
            ->addMinutes($this->exam->duration_minutes);

        $windowClose = $this->allowed_until ?? $this->exam->end_at;

        return $durationDeadline->lt($windowClose) ? $durationDeadline : $windowClose;
    }

    /**
     * Sisa waktu dalam detik (negatif jika sudah lewat).
     */
    public function remainingSeconds(): int
    {
        $deadline = $this->effectiveDeadline();
        if (!$deadline) {
            return 0;
        }

        return (int) now()->diffInSeconds($deadline, false);
    }

    /**
     * Apakah waktu peserta ini sudah habis?
     */
    public function isTimeExpired(): bool
    {
        if (!$this->started_at) {
            return false;
        }

        return $this->remainingSeconds() <= 0;
    }

    /**
     * Apakah siswa ini dalam jendela waktu aktif (ujian reguler atau susulan)?
     */
    public function isInActiveWindow(): bool
    {
        $now = now();

        // Jendela susulan khusus
        if ($this->allowed_from && $this->allowed_until) {
            return $now->between($this->allowed_from, $this->allowed_until);
        }

        // Jendela ujian reguler
        return $now->between($this->exam->start_at, $this->exam->end_at);
    }
}
