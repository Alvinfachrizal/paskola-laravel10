<?php

namespace App\Models;

use App\Enums\PpdbApplicantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpdbApplicant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wave_id',
        'registration_code',
        'full_name',
        'nisn',
        'birth_date',
        'birth_place',
        'gender',
        'address',
        'parent_name',
        'parent_phone',
        'email',
        'login_token',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        // Cast string dari DB menjadi PHP Enum otomatis
        'status'     => PpdbApplicantStatus::class,
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────

    /** Pendaftar ini ikut gelombang mana */
    public function wave()
    {
        return $this->belongsTo(PpdbWave::class, 'wave_id');
    }

    /** Daftar dokumen yang diupload pendaftar (1-ke-banyak) */
    public function documents()
    {
        return $this->hasMany(PpdbDocument::class, 'applicant_id');
    }

    /** Riwayat pembayaran (1-ke-banyak karena bisa ada 2 tipe bayar) */
    public function payments()
    {
        return $this->hasMany(PpdbPayment::class, 'applicant_id');
    }

    /** Nilai seleksi (1-ke-banyak per jenis nilai) */
    public function selectionScores()
    {
        return $this->hasMany(PpdbSelectionScore::class, 'applicant_id');
    }

    /** Data seragam (1-ke-1) */
    public function uniformOrder()
    {
        return $this->hasOne(PpdbUniformOrder::class, 'applicant_id');
    }

    /** Data daftar ulang (1-ke-1) */
    public function reregistration()
    {
        return $this->hasOne(PpdbReregistration::class, 'applicant_id');
    }

    // ─── Helper Methods ────────────────────────────────────────────────────

    /** Hitung rata-rata nilai seleksi */
    public function averageScore(): ?float
    {
        return $this->selectionScores()->avg('score_value');
    }

    /** Cek apakah semua dokumen valid */
    public function allDocumentsValid(): bool
    {
        if ($this->documents()->count() === 0) return false;

        return !$this->documents()->where('status', '!=', 'valid')->exists();
    }

    /** Cek apakah ada dokumen yang pending (belum diverifikasi) */
    public function hasPendingDocuments(): bool
    {
        return $this->documents()->where('status', 'pending')->exists();
    }

    /**
     * Generate kode pendaftaran unik.
     * Format: PPDB-[tahun]-[5 digit urut], misal: PPDB-2025-00001
     */
    public static function generateRegistrationCode(): string
    {
        $year    = date('Y');
        $prefix  = "PPDB-{$year}-";
        $lastCode = static::where('registration_code', 'like', "{$prefix}%")
            ->orderByDesc('registration_code')
            ->value('registration_code');

        $nextNumber = $lastCode
            ? (int) substr($lastCode, strlen($prefix)) + 1
            : 1;

        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
