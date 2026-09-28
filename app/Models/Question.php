<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_id',
        'teacher_id',
        'subject_id',
        'question_text',
        'image_path',
        'in_bank',
    ];

    protected $casts = [
        'in_bank' => 'boolean',
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

    /** Pilihan jawaban (A, B, C, D, ...) urut berdasarkan position */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('position');
    }

    /** Ujian-ujian yang memakai soal ini (via exam_questions) */
    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')
                    ->withPivot('points')
                    ->withTimestamps();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Kembalikan pilihan jawaban yang benar (dipakai server untuk penilaian) */
    public function correctOption(): ?QuestionOption
    {
        return $this->options()->where('is_correct', true)->first();
    }
}
