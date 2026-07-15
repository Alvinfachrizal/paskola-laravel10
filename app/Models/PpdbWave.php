<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpdbWave extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quota',
        'start_date',
        'end_date',
        'registration_fee',
        'is_active',
        'description',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'registration_fee' => 'decimal:2',
        'is_active'        => 'boolean',
    ];

    /**
     * Relasi: satu gelombang punya banyak pendaftar.
     */
    public function applicants()
    {
        return $this->hasMany(PpdbApplicant::class, 'wave_id');
    }

    /**
     * Helper: apakah gelombang ini membutuhkan biaya pendaftaran?
     */
    public function hasFee(): bool
    {
        return $this->registration_fee > 0;
    }

    /**
     * Helper: hitung sisa kuota (kuota - jumlah yang sudah lolos/daftar ulang).
     */
    public function remainingQuota(): int
    {
        $filled = $this->applicants()
            ->whereIn('status', ['selected', 're_registered'])
            ->count();

        return max(0, $this->quota - $filled);
    }
}
