<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'question_id',
        'option_text',
        'image_path',
        'is_correct',
        'position',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'position'   => 'integer',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────────

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Label posisi (A, B, C, D, E) berdasarkan angka position */
    public function positionLabel(): string
    {
        return match($this->position) {
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            default => (string) $this->position,
        };
    }

    /**
     * Kembalikan array data AMAN untuk dikirim ke browser siswa.
     * ⚠️ is_correct TIDAK disertakan.
     */
    public function toStudentArray(): array
    {
        return [
            'id'         => $this->id,
            'option_text'=> $this->option_text,
            'image_path' => $this->image_path,
            'position'   => $this->position,
            'label'      => $this->positionLabel(),
        ];
    }
}
