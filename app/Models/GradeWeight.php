<?php

namespace App\Models;

use App\Enums\GradeComponentType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model GradeWeight: bobot nilai per komponen per mapel per semester.
 * Total weight_percent semua komponen untuk satu subject+semester HARUS = 100.
 */
class GradeWeight extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'subject_id', 'semester_id', 'component_type', 'weight_percent',
    ];

    protected $casts = [
        'component_type' => GradeComponentType::class,
        'weight_percent' => 'integer',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // ─── Helper ────────────────────────────────────────────────────────────

    /**
     * Hitung total bobot untuk subject + semester tertentu.
     * Digunakan untuk validasi: harus = 100.
     */
    public static function totalWeight(string $subjectId, string $semesterId, ?string $excludeId = null): int
    {
        return (int) self::where('subject_id', $subjectId)
            ->where('semester_id', $semesterId)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->sum('weight_percent');
    }
}
