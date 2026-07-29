<?php

namespace Tests\Unit;

use App\Models\AcademicEvent;
use App\Models\EventCategory;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\AcademicCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit Test untuk AcademicCalendarService::isHoliday()
 *
 * Jalankan dengan: php artisan test --filter=AcademicCalendarServiceTest
 */
class AcademicCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    private AcademicCalendarService $service;
    private School $school;
    private User $user;
    private EventCategory $holidayCategory;
    private EventCategory $nonHolidayCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AcademicCalendarService::class);

        // Buat data dasar yang dibutuhkan semua test
        $this->school = School::create(['name' => 'SMA Test', 'address' => 'Jl. Test']);
        $this->user   = User::factory()->create();

        $this->holidayCategory = EventCategory::create([
            'school_id'  => $this->school->id,
            'name'       => 'Hari Libur Nasional',
            'is_holiday' => true,
            'color'      => '#EF4444',
        ]);

        $this->nonHolidayCategory = EventCategory::create([
            'school_id'  => $this->school->id,
            'name'       => 'Ujian',
            'is_holiday' => false,
            'color'      => '#3B82F6',
        ]);
    }

    /**
     * Skenario 1: Tanggal libur sekolah-wide (class_id = null)
     * Harapan: isHoliday() return true + nama event
     */
    public function test_detects_school_wide_holiday(): void
    {
        AcademicEvent::create([
            'category_id' => $this->holidayCategory->id,
            'class_id'    => null, // seluruh sekolah
            'created_by'  => $this->user->id,
            'title'       => 'HUT RI',
            'start_date'  => '2025-08-17',
            'end_date'    => '2025-08-17',
        ]);

        $result = $this->service->isHoliday('2025-08-17');

        $this->assertTrue($result['is_holiday']);
        $this->assertEquals('HUT RI', $result['event_title']);
    }

    /**
     * Skenario 2a: Tanggal libur khusus 1 kelas
     * Harapan: isHoliday() return true HANYA untuk kelas tersebut
     */
    public function test_detects_class_specific_holiday(): void
    {
        $classA = SchoolClass::factory()->create(['school_id' => $this->school->id]);
        $classB = SchoolClass::factory()->create(['school_id' => $this->school->id]);

        AcademicEvent::create([
            'category_id' => $this->holidayCategory->id,
            'class_id'    => $classA->id, // khusus kelas A
            'created_by'  => $this->user->id,
            'title'       => 'Libur Khusus Kelas A',
            'start_date'  => '2025-09-01',
            'end_date'    => '2025-09-01',
        ]);

        // Kelas A → harus libur
        $resultA = $this->service->isHoliday('2025-09-01', $classA->id);
        $this->assertTrue($resultA['is_holiday']);
        $this->assertEquals('Libur Khusus Kelas A', $resultA['event_title']);

        // Kelas B → tidak libur (event bukan miliknya dan bukan sekolah-wide)
        $resultB = $this->service->isHoliday('2025-09-01', $classB->id);
        $this->assertFalse($resultB['is_holiday']);
        $this->assertNull($resultB['event_title']);
    }

    /**
     * Skenario 3: Tanggal biasa (tidak ada event holiday)
     * Harapan: isHoliday() return false
     */
    public function test_returns_false_for_regular_day(): void
    {
        $result = $this->service->isHoliday('2025-07-15');

        $this->assertFalse($result['is_holiday']);
        $this->assertNull($result['event_title']);
    }

    /**
     * Skenario 4: Tanggal ada event NON-holiday (misal: Ujian)
     * Harapan: isHoliday() tetap return false (hanya is_holiday=true yang dihitung)
     */
    public function test_ignores_non_holiday_events(): void
    {
        AcademicEvent::create([
            'category_id' => $this->nonHolidayCategory->id, // bukan libur
            'class_id'    => null,
            'created_by'  => $this->user->id,
            'title'       => 'Ujian Tengah Semester',
            'start_date'  => '2025-10-06',
            'end_date'    => '2025-10-10',
        ]);

        $result = $this->service->isHoliday('2025-10-07');

        $this->assertFalse($result['is_holiday']);
        $this->assertNull($result['event_title']);
    }

    /**
     * Skenario 5: Tanggal di dalam rentang event multi-hari
     * Harapan: isHoliday() mendeteksi meskipun bukan tanggal start_date-nya
     */
    public function test_detects_holiday_in_multi_day_range(): void
    {
        AcademicEvent::create([
            'category_id' => $this->holidayCategory->id,
            'class_id'    => null,
            'created_by'  => $this->user->id,
            'title'       => 'Libur Idul Fitri',
            'start_date'  => '2025-03-28',
            'end_date'    => '2025-04-07', // 11 hari libur
        ]);

        // Cek tanggal di tengah rentang
        $result = $this->service->isHoliday('2025-04-02');

        $this->assertTrue($result['is_holiday']);
        $this->assertEquals('Libur Idul Fitri', $result['event_title']);
    }
}
