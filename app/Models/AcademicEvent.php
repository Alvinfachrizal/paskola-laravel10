<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicEvent extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'category_id',
        'class_id',
        'created_by',
        'title',
        'start_date',
        'end_date',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // Kategori event (libur/ujian/kegiatan/dll)
    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    // Kelas khusus (null = seluruh sekolah)
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // User yang membuat event
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: event yang mencakup tanggal tertentu
     * Dipakai oleh AcademicCalendarService::isHoliday()
     */
    public function scopeCoveringDate($query, string $date)
    {
        return $query->where('start_date', '<=', $date)
                     ->where('end_date', '>=', $date);
    }

    /**
     * Scope: event yang berlaku untuk sekolah-wide (class_id = null)
     * atau untuk kelas tertentu
     */
    public function scopeForClassOrSchool($query, ?string $classId = null)
    {
        return $query->where(function ($q) use ($classId) {
            $q->whereNull('class_id');
            if ($classId) {
                $q->orWhere('class_id', $classId);
            }
        });
    }
}
