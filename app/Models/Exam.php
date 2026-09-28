<?php

namespace App\Models;

use App\Enums\ExamStatus;
use App\Enums\ExamShowScore;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_id',
        'teacher_id',
        'subject_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'duration_minutes',
        'show_score',
        'status',
    ];

    protected $casts = [
        'start_at'         => 'datetime',
        'end_at'           => 'datetime',
        'duration_minutes' => 'integer',
        'show_score'       => ExamShowScore::class,
        'status'           => ExamStatus::class,
    ];

    // ── Relasi ──────────────────────────────────────────────────────────────

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /** Kelas-kelas yang mengikuti ujian ini (via exam_classes) */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'exam_classes', 'exam_id', 'class_id')
                    ->withTimestamps();
    }

    /** Soal-soal yang dipakai di ujian ini (via exam_questions) */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
                    ->withPivot('points')
                    ->withTimestamps();
    }

    /** Semua peserta (baris exam_participants) ujian ini */
    public function participants(): HasMany
    {
        return $this->hasMany(ExamParticipant::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Total poin seluruh soal dalam ujian ini */
    public function totalPoints(): float
    {
        return (float) $this->questions()->sum('exam_questions.points');
    }

    /** Apakah ujian sedang dalam jendela waktu aktif? */
    public function isCurrentlyOpen(): bool
    {
        $now = now();
        return $this->status === ExamStatus::Published
            && $now->between($this->start_at, $this->end_at);
    }
}
