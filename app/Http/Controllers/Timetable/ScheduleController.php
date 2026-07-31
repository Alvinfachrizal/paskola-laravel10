<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Auth;
use DB;

class ScheduleController extends Controller
{
    /**
     * Tampilkan form input jadwal pelajaran
     */
    public function create()
    {
        $schoolId = Auth::user()->school_id;
        
        // Dapatkan Semester Aktif
        $activeSemester = Semester::where('school_id', $schoolId)->where('is_active', true)->first();
        if (!$activeSemester) {
            return redirect()->back()->with('error', 'Belum ada semester yang aktif. Silakan seting semester terlebih dahulu.');
        }

        // Dapatkan data referensi
        $hariLokal = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
        ];

        // Format label timeslot: "Senin - Shift Pagi (Jam ke-1: 07:00 - 07:45)"
        $timeSlots = TimeSlot::where('school_id', $schoolId)
            ->orderBy('day_of_week')
            ->orderBy('shift')
            ->orderBy('start_time')
            ->get()
            ->map(function ($slot) use ($hariLokal) {
                $start = \Carbon\Carbon::parse($slot->start_time)->format('H:i');
                $end = \Carbon\Carbon::parse($slot->end_time)->format('H:i');
                $slot->label = "{$hariLokal[$slot->day_of_week]} - Shift {$slot->shift} (Jam ke-{$slot->period_number}: {$start} - {$end})";
                return $slot;
            });

        $rooms = Room::where('school_id', $schoolId)->orderBy('name')->get();
        // Gunakan orderBy('grade') karena nama kolomnya 'grade' bukan 'level'
        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('grade')->orderBy('name')->get();
        $subjects = Subject::where('school_id', $schoolId)->orderBy('name')->get();
        $teachers = Teacher::whereHas('user', function($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->get();

        // Tampilkan 10 jadwal terakhir yang baru saja diinput di semester ini (untuk riwayat cepat admin)
        $recentSchedules = Schedule::with(['timeSlot', 'room', 'schoolClass', 'subject', 'teacher.user'])
            ->where('school_id', $schoolId)
            ->where('semester_id', $activeSemester->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('timetable.schedules.create', compact(
            'activeSemester', 'timeSlots', 'rooms', 'classes', 'subjects', 'teachers', 'recentSchedules', 'hariLokal'
        ));
    }

    /**
     * Simpan jadwal pelajaran & validasi anti-bentrok
     */
    public function store(Request $request)
    {
        $request->validate([
            'time_slot_id' => 'required|uuid|exists:time_slots,id',
            'room_id'      => 'required|uuid|exists:rooms,id',
            'class_id'     => 'required|uuid|exists:school_classes,id',
            'subject_id'   => 'required|uuid|exists:subjects,id',
            'teacher_id'   => 'required|uuid|exists:teachers,id',
        ]);

        $schoolId = Auth::user()->school_id;
        
        $activeSemester = Semester::where('school_id', $schoolId)->where('is_active', true)->first();
        if (!$activeSemester) {
            return redirect()->back()->with('error', 'Semester aktif tidak ditemukan.');
        }

        // VALIDASI ANTI-BENTROK KELAS, GURU, DAN RUANGAN
        $timeSlotId = $request->time_slot_id;
        $semesterId = $activeSemester->id;
        
        // 1. Cek Bentrok Guru: Guru mengajar 2 kelas di jam yang sama
        $teacherClash = Schedule::with('schoolClass')
            ->where('semester_id', $semesterId)
            ->where('time_slot_id', $timeSlotId)
            ->where('teacher_id', $request->teacher_id)
            ->first();
            
        if ($teacherClash) {
            $teacherName = Teacher::find($request->teacher_id)->user->name ?? 'Guru ini';
            return redirect()->back()
                ->withInput()
                ->with('error', "Gagal disimpan: {$teacherName} sudah mengajar di kelas {$teacherClash->schoolClass->name} pada jam tersebut.");
        }

        // 2. Cek Bentrok Kelas: 1 Kelas diajar 2 mapel berbeda di jam yang sama
        $classClash = Schedule::with('subject')
            ->where('semester_id', $semesterId)
            ->where('time_slot_id', $timeSlotId)
            ->where('class_id', $request->class_id)
            ->first();
            
        if ($classClash) {
            $className = SchoolClass::find($request->class_id)->name ?? 'Kelas ini';
            return redirect()->back()
                ->withInput()
                ->with('error', "Gagal disimpan: {$className} sudah memiliki jadwal mata pelajaran {$classClash->subject->name} pada jam tersebut.");
        }

        // 3. Cek Bentrok Ruang: Ruang dipakai 2 kelas di jam yang sama
        $roomClash = Schedule::with('schoolClass')
            ->where('semester_id', $semesterId)
            ->where('time_slot_id', $timeSlotId)
            ->where('room_id', $request->room_id)
            ->first();
            
        if ($roomClash) {
            $roomName = Room::find($request->room_id)->name ?? 'Ruangan ini';
            return redirect()->back()
                ->withInput()
                ->with('error', "Gagal disimpan: {$roomName} sudah digunakan oleh kelas {$roomClash->schoolClass->name} pada jam tersebut.");
        }

        // Lolos semua validasi bentrok, simpan!
        Schedule::create([
            'school_id'    => $schoolId,
            'semester_id'  => $semesterId,
            'time_slot_id' => $timeSlotId,
            'room_id'      => $request->room_id,
            'class_id'     => $request->class_id,
            'subject_id'   => $request->subject_id,
            'teacher_id'   => $request->teacher_id,
        ]);

        return redirect()->route('timetable.schedules.create')->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    /**
     * Hapus jadwal pelajaran
     */
    public function destroy($id)
    {
        $schoolId = Auth::user()->school_id;
        $schedule = Schedule::where('school_id', $schoolId)->findOrFail($id);
        $schedule->delete();

        return redirect()->route('timetable.schedules.create')->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    /**
     * API: Ambil daftar guru berdasarkan mata pelajaran (untuk Select2)
     */
    public function getTeachersBySubject(Request $request)
    {
        $subjectId = $request->query('subject_id');
        $schoolId = Auth::user()->school_id;
        
        $subject = Subject::find($subjectId);
        
        $teachers = Teacher::with('user')
            ->whereHas('user', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->get();

        $recommended = [];
        $others = [];
        
        if ($subject) {
            foreach ($teachers as $t) {
                $name = $t->user->name ?? 'Unknown';
                // Jika mapel cocok dengan spesialisasi
                if ($t->subject_specialty && stripos($t->subject_specialty, $subject->name) !== false) {
                    $recommended[] = ['id' => $t->id, 'text' => $name . ' (' . $t->subject_specialty . ')'];
                } else {
                    $others[] = ['id' => $t->id, 'text' => $name . ($t->subject_specialty ? ' (' . $t->subject_specialty . ')' : '')];
                }
            }
        } else {
            foreach ($teachers as $t) {
                $others[] = ['id' => $t->id, 'text' => ($t->user->name ?? 'Unknown')];
            }
        }

        $results = [];
        if (count($recommended) > 0) {
            $results[] = [
                'text' => 'Guru Rekomendasi (Sesuai Mapel)',
                'children' => $recommended
            ];
        }
        if (count($others) > 0) {
            $results[] = [
                'text' => 'Guru Lainnya',
                'children' => $others
            ];
        }

        return response()->json(['results' => $results]);
    }
}
