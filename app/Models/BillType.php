<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillType extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'is_recurring',
        'is_initial_bill',
        'default_amount',
        'is_active',
    ];

    protected $casts = [
        'is_recurring'    => 'boolean',
        'is_initial_bill' => 'boolean',
        'is_active'       => 'boolean',
        'default_amount'  => 'decimal:2',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Semua tagihan aktual yang dibuat dari jenis tagihan ini.
     */
    public function studentBills()
    {
        return $this->hasMany(StudentBill::class);
    }

    /**
     * Scope: hanya jenis tagihan yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: hanya tagihan yang ditandai sebagai tagihan awal (initial).
     */
    public function scopeInitial($query)
    {
        return $query->where('is_initial_bill', true);
    }

    /**
     * Scope: hanya tagihan berulang (recurring).
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }
}
