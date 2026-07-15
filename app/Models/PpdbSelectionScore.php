<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpdbSelectionScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'score_type',
        'score_value',
        'inputted_by',
    ];

    protected $casts = [
        'score_value' => 'decimal:2',
    ];

    /**
     * Tipe nilai yang tersedia untuk seleksi.
     * Admin bisa menambahkan lebih banyak sesuai kebutuhan sekolah.
     */
    public static array $scoreTypes = [
        'matematika'       => 'Matematika',
        'bahasa_indonesia' => 'Bahasa Indonesia',
        'ipa'              => 'IPA',
        'bahasa_inggris'   => 'Bahasa Inggris',
        'wawancara'        => 'Wawancara',
    ];

    public function applicant()
    {
        return $this->belongsTo(PpdbApplicant::class, 'applicant_id');
    }

    public function inputtedBy()
    {
        return $this->belongsTo(User::class, 'inputted_by');
    }
}
