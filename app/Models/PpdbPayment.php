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
}
