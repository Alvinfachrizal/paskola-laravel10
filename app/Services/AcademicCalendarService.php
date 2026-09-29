<?php

namespace App\Services;

use App\Models\AcademicEvent;
use Carbon\Carbon;

/**
 * AcademicCalendarService
 *
 * Service reusable untuk mengecek apakah suatu tanggal adalah hari libur
 * berdasarkan data di tabel academic_events + event_categories.
 *
 * Cara pakai di modul lain (misal Jadwal Pelajaran):
 *   $service = app(AcademicCalendarService::class);
 *   $result  = $service->isHoliday('2025-08-17', $classId);
 *   if ($result['is_holiday']) { ... }
 */
class AcademicCalendarService
{
    /**
     * Cek apakah tanggal tertentu adalah hari libur.
     *
     * @param  string|\Carbon\Carbon  $date     Tanggal yang dicek (string 'Y-m-d' atau Carbon)
     * @param  string|null            $classId  UUID kelas (null = cek libur sekolah-wide saja)
     *
     * @return array{is_holiday: bool, event_title: string|null, event_id: string|null}
     */
    public function isHoliday(string|Carbon $date, ?string $classId = null): array
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : $date;

        $event = AcademicEvent::query()
            // Hanya event dengan kategori is_holiday = true
            ->whereHas('category', fn ($q) => $q->where('is_holiday', true))
            // Event yang mencakup tanggal yang dicek
            ->coveringDate($dateStr)
            // Berlaku sekolah-wide (class_id null) ATAU untuk kelas tertentu
            ->forClassOrSchool($classId)
            ->select(['id', 'title'])
            ->first();

        if ($event) {
            return [
                'is_holiday'  => true,
                'event_title' => $event->title,
                'event_id'    => $event->id,
            ];
        }

        return [
            'is_holiday'  => false,
            'event_title' => null,
            'event_id'    => null,
        ];
    }

    /**
     * Ambil semua event (libur & non-libur) dalam rentang tanggal tertentu.
     * Dipakai untuk render tampilan kalender bulanan.
     *
     * @param  string      $startDate  Awal bulan, format 'Y-m-d'
     * @param  string      $endDate    Akhir bulan, format 'Y-m-d'
     * @param  string|null $classId    Filter kelas (null = tampilkan semua/sekolah-wide)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEventsForPeriod(string $startDate, string $endDate, ?string $classId = 'all')
    {
        $query = AcademicEvent::with(['category', 'schoolClass'])
            ->where(function ($q) use ($startDate, $endDate) {
                // Event yang overlap dengan rentang yang diminta
                $q->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
            });

        if ($classId !== 'all') {
            $query->forClassOrSchool($classId);
        }

        return $query->orderBy('start_date')->get();
    }
}
