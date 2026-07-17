<?php

namespace App\Models;

use App\Enums\GradeComponentType;
use App\Enums\ReportCardStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model ReportCard: rapor FINAL satu siswa untuk satu mapel satu semester.
 *
 * Nilai final_score dihitung dari student_grades × grade_weights.
 * Status alur: draft → perlu_verifikasi → terverifikasi → published.
 */
class ReportCard extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id', 'subject_id', 'semester_id',
        'final_score', 'description', 'status', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'status'      => ReportCardStatus::class,
        'final_score' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────

    public function student()    { return $this->belongsTo(Student::class); }
    public function subject()    { return $this->belongsTo(Subject::class); }
    public function semester()   { return $this->belongsTo(Semester::class); }
    public function verifiedBy() { return $this->belongsTo(User::class, 'verified_by'); }

    // ─── Scope ─────────────────────────────────────────────────────────────

    /** Hanya rapor yang sudah published (untuk siswa/ortu) */
    public function scopePublished($query)
    {
        return $query->where('status', ReportCardStatus::Published->value);
    }

    // ─── Helper ────────────────────────────────────────────────────────────

    /**
     * Hitung nilai akhir berbobot dari student_grades + grade_weights.
     * Rumus: Σ (score × weight_percent / 100) untuk semua komponen.
     * Mengembalikan null jika tidak ada bobot yang terdefinisi.
     */
    public static function calculateFinalScore(string $studentId, string $subjectId, string $semesterId): ?float
    {
        $grades  = StudentGrade::gradesFor($studentId, $subjectId, $semesterId);
        $weights = GradeWeight::where('subject_id', $subjectId)
            ->where('semester_id', $semesterId)
            ->get();

        if ($weights->isEmpty()) return null;

        $total = 0;
        foreach ($weights as $w) {
            $gradeKey = $w->component_type->value;
            $score    = isset($grades[$gradeKey]) ? (float) $grades[$gradeKey]->score : 0;
            $total   += $score * ($w->weight_percent / 100);
        }

        return round($total, 2);
    }

    /**
     * Buat atau perbarui rapor otomatis setelah nilai diubah.
     * Dipanggil dari controller StudentGrade setiap kali nilai disimpan.
     */
    public static function recalculate(string $studentId, string $subjectId, string $semesterId): self
    {
        $finalScore = self::calculateFinalScore($studentId, $subjectId, $semesterId);

        return self::updateOrCreate(
            [
                'student_id'  => $studentId,
                'subject_id'  => $subjectId,
                'semester_id' => $semesterId,
            ],
            [
                'final_score' => $finalScore,
                'status'      => ReportCardStatus::Draft->value,
                // Reset ke draft jika ada perubahan nilai setelah verifikasi
            ]
        );
    }

    /** Konversi nilai ke huruf (A/B/C/D/E) */
    public function scoreToGrade(): string
    {
        $score = (float) $this->final_score;
        return match(true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default      => 'E',
        };
    }
}
