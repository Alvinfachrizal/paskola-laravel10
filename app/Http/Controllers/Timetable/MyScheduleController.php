<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\AcademicDay;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TimeSlot;
use App\Services\AcademicCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyScheduleController extends Controller
{
    /**
     * Tampilkan grid jadwal pelajaran (Untuk Guru atau Siswa)
     */
    public function index(Request $request, AcademicCalendarService $calendarService)
    {
        $user = Auth::user();
        $schoolId = $user->school_id;

        // 1. Tentukan rentang minggu ini (Senin - Minggu)
        $dateParam = $request->query('date', Carbon::now()->toDateString());
        $currentDate = Carbon::parse($dateParam);
        $startOfWeek = $currentDate->copy()->startOfWeek();
        $endOfWeek = $currentDate->copy()->endOfWeek();

        // 2. Dapatkan Semester Aktif
        $activeSemester = Semester::where('school_id', $schoolId)->where('is_active', true)->first();
        if (!$activeSemester) {
            return view('timetable.my-schedule.empty', ['message' => 'Tidak ada semester yang aktif. Silakan hubungi Admin.']);
        }

        // 3. Ambil Hari Aktif (Dinamis dari tabel academic_days)
        $activeDays = AcademicDay::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->get();

        if ($activeDays->isEmpty()) {
            return view('timetable.my-schedule.empty', ['message' => 'Admin belum mengatur hari aktif sekolah.']);
        }

        $activeDayNumbers = $activeDays->pluck('day_of_week')->toArray();

        // 4. Identifikasi Role (Guru vs Siswa) dan Ambil Jadwal yang sesuai
        $schedules = collect();
        $classIdForHoliday = null;
        $titlePrefix = "Jadwal Saya";
        $subtitlePrefix = "";

        if ($user->role === 'Guru') {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                return view('timetable.my-schedule.empty', ['message' => 'Data profil guru Anda tidak ditemukan.']);
            }
            
            // Isolasi data: Guru hanya bisa lihat jadwal dia sendiri
            $schedules = Schedule::with(['timeSlot', 'room', 'schoolClass', 'subject'])
                ->where('school_id', $schoolId)
                ->where('semester_id', $activeSemester->id)
                ->where('teacher_id', $teacher->id)
                ->get();
                
            $titlePrefix = "Jadwal Mengajar";
            $subtitlePrefix = $teacher->name;

        } elseif ($user->role === 'Siswa') {
            $student = Student::with(['classes' => function($q) {
                $q->wherePivot('is_active', true);
            }])->where('user_id', $user->id)->first();

            $activeClass = $student ? $student->classes->first() : null;

            if (!$activeClass) {
                return view('timetable.my-schedule.empty', ['message' => 'Anda belum dimasukkan ke kelas manapun di semester ini.']);
            }

            $classIdForHoliday = $activeClass->id;

            // Isolasi data: Siswa hanya bisa lihat jadwal kelasnya sendiri
            $schedules = Schedule::with(['timeSlot', 'room', 'teacher.user', 'subject'])
                ->where('school_id', $schoolId)
                ->where('semester_id', $activeSemester->id)
                ->where('class_id', $activeClass->id)
                ->get();
                
            $titlePrefix = "Jadwal Pelajaran";
            $subtitlePrefix = "Kelas " . $activeClass->name;
        } else {
            // Jika bukan guru/siswa mengakses route ini (misal Admin nyasar ke sini)
            return view('timetable.my-schedule.empty', ['message' => 'Menu ini dikhususkan untuk Guru dan Siswa.']);
        }

        // 5. Ambil data Event dari Kalender Akademik untuk minggu ini
        // Menggunakan scope yang sudah aman di AcademicCalendarService
        $events = $calendarService->getEventsForPeriod($startOfWeek->toDateString(), $endOfWeek->toDateString(), $classIdForHoliday);

        // 6. Ambil semua TimeSlot (dikumpulkan berdasarkan hari, lalu urutkan shift/jam)
        $allTimeSlots = TimeSlot::where('school_id', $schoolId)
            ->whereIn('day_of_week', $activeDayNumbers)
            ->orderBy('day_of_week')
            ->orderBy('shift')
            ->orderBy('start_time')
            ->get();

        // Cari jumlah maksimal jam pelajaran (period) per hari untuk merender baris Grid
        $maxPeriod = $allTimeSlots->max('period_number') ?? 0;

        // Group time slots by day and period for easy grid rendering
        $timeSlotsGrid = [];
        foreach ($allTimeSlots as $ts) {
            $timeSlotsGrid[$ts->day_of_week][$ts->period_number] = $ts;
        }

        // Group schedules by time_slot_id
        $schedulesByTimeSlot = $schedules->keyBy('time_slot_id');

        // Menyiapkan data navigasi minggu
        $prevWeek = $startOfWeek->copy()->subWeek()->toDateString();
        $nextWeek = $startOfWeek->copy()->addWeek()->toDateString();
        $weekTitle = $startOfWeek->translatedFormat('d M Y') . ' - ' . $endOfWeek->translatedFormat('d M Y');

        return view('timetable.my-schedule.index', compact(
            'activeSemester',
            'activeDays',
            'maxPeriod',
            'timeSlotsGrid',
            'schedulesByTimeSlot',
            'events',
            'startOfWeek',
            'prevWeek',
            'nextWeek',
            'weekTitle',
            'titlePrefix',
            'subtitlePrefix'
        ));
    }
}
