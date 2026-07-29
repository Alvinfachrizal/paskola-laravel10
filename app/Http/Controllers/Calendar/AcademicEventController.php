<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAcademicEventRequest;
use App\Models\AcademicEvent;
use App\Models\EventCategory;
use App\Models\SchoolClass;
use App\Services\AcademicCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AcademicEventController extends Controller
{
    public function __construct(private AcademicCalendarService $calendarService) {}

    /**
     * Tampilan kalender bulanan utama.
     * Semua role yang login dapat melihat.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Tentukan bulan & tahun yang ditampilkan (default: bulan ini)
        $year  = (int) $request->get('year',  now()->year);
        $month = (int) $request->get('month', now()->month);

        // Batasi range tahun yang valid
        $year  = max(2020, min(2099, $year));
        $month = max(1,    min(12,   $month));

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        // Filter kelas: admin bisa lihat semua/filter, guru/siswa hanya kelasnya
        $classId = $request->get('class_id'); // null = sekolah-wide

        // Ambil event untuk bulan ini
        $events = $this->calendarService->getEventsForPeriod(
            $startOfMonth->toDateString(),
            $endOfMonth->toDateString(),
            $classId ?: null
        );

        // Kelompokkan event per tanggal (untuk render grid)
        $eventsByDate = [];
        foreach ($events as $event) {
            $cursor = $event->start_date->copy();
            while ($cursor->lte($event->end_date)) {
                if ($cursor->month === $month) {
                    $eventsByDate[$cursor->day][] = $event;
                }
                $cursor->addDay();
            }
        }

        // Data untuk form tambah event
        $schoolId   = $user->school_id;
        $categories = EventCategory::where('school_id', $schoolId)->orderBy('name')->get();

        // Untuk Guru: tampilkan semua kelas aktif (nanti diperketat setelah modul Jadwal)
        // Untuk Admin/Kepsek: tampilkan semua kelas
        $classes = SchoolClass::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Guru hanya bisa pilih kategori NON-holiday
        $categoriesForForm = $user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])
            ? $categories
            : $categories->where('is_holiday', false)->values();

        return view('calendar.index', compact(
            'year', 'month', 'startOfMonth', 'endOfMonth',
            'eventsByDate', 'events', 'categories', 'categoriesForForm',
            'classes', 'classId'
        ));
    }

    /**
     * Simpan event baru.
     */
    public function store(StoreAcademicEventRequest $request)
    {
        $this->authorize('create', AcademicEvent::class);

        AcademicEvent::create([
            'category_id' => $request->category_id,
            'class_id'    => $request->class_id ?: null,
            'created_by'  => auth()->id(),
            'title'       => $request->title,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Event "' . $request->title . '" berhasil ditambahkan.');
    }

    /**
     * Update event (via modal/form).
     */
    public function update(StoreAcademicEventRequest $request, AcademicEvent $event)
    {
        $this->authorize('update', $event);

        $event->update([
            'category_id' => $request->category_id,
            'class_id'    => $request->class_id ?: null,
            'title'       => $request->title,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Event berhasil diperbarui.');
    }

    /**
     * Hapus event.
     */
    public function destroy(AcademicEvent $event)
    {
        $this->authorize('delete', $event);

        $title = $event->title;
        $event->delete();

        return back()->with('success', 'Event "' . $title . '" berhasil dihapus.');
    }
}
