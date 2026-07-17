<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Log perubahan nilai — audit trail untuk setiap revisi score di student_grades. */
class GradeChangeLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_grade_id', 'changed_by', 'old_score', 'new_score', 'reason',
    ];

    protected $casts = [
        'old_score' => 'decimal:2',
        'new_score' => 'decimal:2',
    ];

    public function studentGrade() { return $this->belongsTo(StudentGrade::class); }
    public function changedBy()    { return $this->belongsTo(User::class, 'changed_by'); }
}
