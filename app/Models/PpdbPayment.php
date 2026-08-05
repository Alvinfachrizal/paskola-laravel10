<?php

namespace App\Models;

use App\Enums\PpdbPaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpdbPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'student_id',      // Terisi otomatis setelah proses daftar ulang selesai
        'payment_type',
        'amount',
        'status',
        'payment_method',
        'proof_path',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'status'  => PpdbPaymentStatus::class,
        'paid_at' => 'datetime',
    ];

    /**
     * Tipe pembayaran yang tersedia.
     */
    public static array $paymentTypes = [
        'registration_fee'    => 'Biaya Pendaftaran',
        're_registration_fee' => 'Biaya Daftar Ulang',
    ];

    public function applicant()
    {
        return $this->belongsTo(PpdbApplicant::class, 'applicant_id');
    }

    /**
     * Relasi ke Student — terisi setelah daftar ulang selesai.
     * Digunakan oleh modul Keuangan untuk melihat riwayat bayar PPDB per siswa.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
