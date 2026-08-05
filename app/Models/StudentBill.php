<?php

namespace App\Models;

use App\Enums\StudentBillStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'bill_type_id',
        'period',
        'amount',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'due_date' => 'date',
        'status'   => StudentBillStatus::class,
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function billType()
    {
        return $this->belongsTo(BillType::class);
    }

    /**
     * Semua riwayat pembayaran untuk tagihan ini.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Pembayaran terakhir yang diajukan.
     */
    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Scope: tagihan yang belum lunas (tampilkan sebagai tagihan aktif).
     */
    public function scopePending($query)
    {
        return $query->whereNotIn('status', [StudentBillStatus::Lunas->value]);
    }

    /**
     * Apakah tagihan ini sudah lunas?
     */
    public function isPaid(): bool
    {
        return $this->status === StudentBillStatus::Lunas;
    }

    /**
     * Format periode ke label lebih manusiawi, misal "2026-08" → "Agustus 2026".
     */
    public function periodLabel(): ?string
    {
        if (!$this->period) return null;
        [$year, $month] = explode('-', $this->period);
        $bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',   '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',    '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober', '11' => 'November',  '12' => 'Desember',
        ];
        return ($bulan[$month] ?? $month) . ' ' . $year;
    }
}
