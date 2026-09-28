<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'participant_id',
        'question_id',
        'option_id',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────────

    public function participant(): BelongsTo
    {
        return $this->belongsTo(ExamParticipant::class, 'participant_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Pilihan yang dipilih siswa.
     * null = soal belum dijawab (opsi_id nullable).
     */
    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'option_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Apakah jawaban ini benar?
     * Digunakan HANYA di server (untuk penilaian). Jangan ekspos ke frontend siswa.
     */
    public function isCorrect(): bool
    {
        if (!$this->option_id) {
            return false; // soal tidak dijawab → salah
        }

        return (bool) optional($this->selectedOption)->is_correct;
    }
}
