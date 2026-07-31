<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Auth;

class RoomController extends Controller
{
    /**
     * Tampilkan daftar ruangan.
     */
    public function index()
    {
        $schoolId = Auth::user()->school_id;
        
        // Ambil semua ruangan, urutkan berdasarkan nama
        $rooms = Room::where('school_id', $schoolId)
            ->orderBy('name')
            ->get();
            
        // Ambil semua tipe ruang yang pernah diinput untuk rekomendasi (datalist)
        $existingTypes = Room::where('school_id', $schoolId)
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->toArray();
            
        // Gabungkan dengan opsi standar jika belum ada
        $standardTypes = ['Kelas', 'Laboratorium', 'Lapangan', 'Perpustakaan', 'Lainnya'];
        $suggestedTypes = array_unique(array_merge($standardTypes, $existingTypes));
        sort($suggestedTypes);

        return view('timetable.rooms.index', compact('rooms', 'suggestedTypes'));
    }

    /**
     * Simpan data ruangan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $schoolId = Auth::user()->school_id;

        // Cek nama ruangan duplikat (opsional tapi disarankan)
        $exists = Room::where('school_id', $schoolId)
            ->where('name', $request->name)
            ->exists();
            
        if ($exists) {
            return redirect()->back()->with('error', 'Ruangan dengan nama tersebut sudah ada.');
        }

        Room::create([
            'school_id' => $schoolId,
            'name' => $request->name,
            'type' => $request->type,
            'capacity' => $request->capacity,
        ]);

        return redirect()->route('timetable.rooms.index')->with('success', 'Ruangan berhasil ditambahkan.');
    }

    /**
     * Update data ruangan.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $schoolId = Auth::user()->school_id;
        $room = Room::where('school_id', $schoolId)->findOrFail($id);
        
        // Cek duplikat nama (kecuali ruangan ini sendiri)
        $exists = Room::where('school_id', $schoolId)
            ->where('name', $request->name)
            ->where('id', '!=', $id)
            ->exists();
            
        if ($exists) {
            return redirect()->back()->with('error', 'Ruangan dengan nama tersebut sudah ada.');
        }

        $room->update([
            'name' => $request->name,
            'type' => $request->type,
            'capacity' => $request->capacity,
        ]);

        return redirect()->route('timetable.rooms.index')->with('success', 'Data ruangan berhasil diperbarui.');
    }

    /**
     * Hapus ruangan.
     */
    public function destroy($id)
    {
        $schoolId = Auth::user()->school_id;
        $room = Room::where('school_id', $schoolId)->findOrFail($id);

        // Cek apakah ruangan sudah dipakai di tabel schedules
        if ($room->schedules()->exists()) {
            return redirect()->back()->with('error', 'Ruangan tidak dapat dihapus karena sedang digunakan di Jadwal Pelajaran aktif.');
        }

        $room->delete();

        return redirect()->route('timetable.rooms.index')->with('success', 'Ruangan berhasil dihapus.');
    }
}
