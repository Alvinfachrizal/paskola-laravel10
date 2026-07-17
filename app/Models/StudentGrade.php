<?php

namespace App\Models;

use App\Enums\GradeComponentType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model StudentGrade: nilai MENTAH satu siswa untuk satu komponen
 * di satu mapel satu semester.
 *
 * Source 'lms'    → nilai diisi otomatis oleh sistem dari submission LMS.
 * Source 'manual' → nilai diisi manual oleh guru mapel (UTS, UAS, Praktik).
 */
class StudentGrade extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id', 'subject_id', 'semester_id', 'assignment_id',
        'component_type', 'score', 'source', 'inputted_by', 'notes',
    ];

    protected $casts = [
        'component_type' => GradeComponentType::class,
        'score'          => 'decimal:2',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────

    public function student()    { return $this->belongsTo(Student::class); }
    public function subject()    { return $this->belongsTo(Subject::class); }
    public function semester()   { return $this->belongsTo(Semester::class); }
    public function assignment() { return $this->belongsTo(LmsAssignment::class, 'assignment_id'); }
    public function inputtedBy() { return $this->belongsTo(User::class, 'inputted_by'); }
    public function changeLogs() { return $this->hasMany(GradeChangeLog::class); }

    // ─── Helper ────────────────────────────────────────────────────────────

    /**
     * Ambil semua nilai satu siswa di satu mapel satu semester,
     * dikembalikan sebagai collection key=component_type, val=score.
     */
    public static function gradesFor(string $studentId, string $subjectId, string $semesterId): \Illuminate\Support\Collection
    {
        return self::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('semester_id', $semesterId)
            ->get()
            ->keyBy(fn($g) => $g->component_type->value);
    }
}
