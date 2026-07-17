<?php

namespace App\Models;

use App\Enums\GradeComponentType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Semester: semester ganjil/genap per tahun ajaran.
 * Semua nilai (student_grades) dan rapor (report_cards) terikat ke sini.
 */
class Semester extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_id', 'term', 'academic_year', 'name', 'start_date', 'end_date', 'is_active',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'start_date'   => 'date',
        'end_date'     => 'date',
        'term'         => 'integer',
        'academic_year'=> 'integer',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function studentGrades()
    {
        return $this->hasMany(StudentGrade::class);
    }

    public function reportCards()
    {
        return $this->hasMany(ReportCard::class);
    }

    // ─── Helper ────────────────────────────────────────────────────────────

    /** Label yang lengkap, misal: "Semester Ganjil 2025/2026" */
    public function fullLabel(): string
    {
        $termLabel = $this->term === 1 ? 'Ganjil' : 'Genap';
        return "Semester {$termLabel} {$this->academic_year}/" . ($this->academic_year + 1);
    }

    /** Scope: hanya semester yang sedang aktif */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
