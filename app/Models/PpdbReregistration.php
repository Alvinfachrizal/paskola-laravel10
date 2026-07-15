<?php

namespace App\Models;

use App\Enums\PpdbReregistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpdbReregistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'status',
        'student_id',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'status'       => PpdbReregistrationStatus::class,
        'completed_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(PpdbApplicant::class, 'applicant_id');
    }

    /**
     * Relasi ke tabel students — terisi SETELAH daftar ulang selesai.
     * Pendaftar "naik level" jadi siswa resmi di sini.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
