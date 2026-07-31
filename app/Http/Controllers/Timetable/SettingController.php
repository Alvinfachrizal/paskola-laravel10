<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\AcademicDay;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

class SettingController extends Controller
{
    /**
     * Tampilkan halaman setting (Hari aktif & Time slot)
     */
    public function index()
    {
        $schoolId = Auth::user()->school_id;

        // Ambil data hari aktif
        $academicDays = AcademicDay::where('school_id', $schoolId)->orderBy('day_of_week')->get();
        
        // Ambil data time slots, urutkan berdasarkan hari, shift, lalu jam mulai
        $timeSlots = TimeSlot::where('school_id', $schoolId)
            ->orderBy('day_of_week')
            ->orderBy('shift')
            ->orderBy('start_time')
            ->get();

        return view('timetable.settings.index', compact('academicDays', 'timeSlots'));
    }

    /**
     * Update status hari aktif secara massal
     */
    public function updateDays(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $activeDays = $request->input('active_days', []); // array of day_of_week yang aktif

        DB::beginTransaction();
        try {
            // Kita proses dari hari 1 sampai 7
            for ($i = 1; $i <= 7; $i++) {
                AcademicDay::updateOrCreate(
                    ['school_id' => $schoolId, 'day_of_week' => $i],
                    ['is_active' => in_array($i, $activeDays)]
                );
            }
            DB::commit();
            return redirect()->route('timetable.settings.index')->with('success', 'Pengaturan hari aktif berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan pengaturan hari: ' . $e->getMessage());
        }
    }

    /**
     * Tambah jam pelajaran (Time Slot) baru
     */
    public function storeTimeSlot(Request $request)
    {
        $request->validate([
            'day_of_week' => 'required|integer|between:1,7',
            'shift' => 'required|in:Pagi,Siang',
            'period_number' => 'required|integer|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ], [
            'end_time.after' => 'Jam selesai harus lebih besar dari jam mulai.',
        ]);

        $schoolId = Auth::user()->school_id;

        // Cek apakah period_number sudah dipakai di hari dan shift yang sama
        $exists = TimeSlot::where('school_id', $schoolId)
            ->where('day_of_week', $request->day_of_week)
            ->where('shift', $request->shift)
            ->where('period_number', $request->period_number)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Jam ke-{$request->period_number} untuk Shift {$request->shift} di hari tersebut sudah ada.");
        }

        // Cek apakah waktu bertabrakan (overlap) dengan slot lain di hari & shift yang sama
        $overlap = TimeSlot::where('school_id', $schoolId)
            ->where('day_of_week', $request->day_of_week)
            ->where('shift', $request->shift)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                      });
            })->exists();

        if ($overlap) {
            return redirect()->back()->with('error', 'Jam pelajaran bertabrakan dengan jam yang sudah ada.');
        }

        TimeSlot::create([
            'school_id' => $schoolId,
            'day_of_week' => $request->day_of_week,
            'shift' => $request->shift,
            'period_number' => $request->period_number,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->route('timetable.settings.index')->with('success', 'Jam pelajaran berhasil ditambahkan.');
    }

    /**
     * Hapus jam pelajaran (Time Slot)
     */
    public function destroyTimeSlot($id)
    {
        $schoolId = Auth::user()->school_id;
        $timeSlot = TimeSlot::where('school_id', $schoolId)->findOrFail($id);

        // Cek apakah time slot sudah digunakan di jadwal pelajaran
        if ($timeSlot->schedules()->exists()) {
            return redirect()->back()->with('error', 'Jam pelajaran tidak dapat dihapus karena sudah digunakan di jadwal.');
        }

        $timeSlot->delete();

        return redirect()->route('timetable.settings.index')->with('success', 'Jam pelajaran berhasil dihapus.');
    }
}
