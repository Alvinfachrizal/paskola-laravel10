<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_bill_id',
        'method',
        'proof_file',
        'gateway_reference',
        'amount_paid',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount_paid'  => 'decimal:2',
        'status'       => PaymentStatus::class,
        'verified_at'  => 'datetime',
    ];

    /**
     * Tagihan yang dibayar oleh record payment ini.
     */
    public function studentBill()
    {
        return $this->belongsTo(StudentBill::class);
    }

    /**
     * Admin/bendahara yang memverifikasi pembayaran ini.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
