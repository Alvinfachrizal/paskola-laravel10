<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $announcements = $this->getRecentAnnouncements($user);

        // Cek peran user dan arahkan ke view (tampilan) yang sesuai
        if ($user->hasRole(['Super Admin', 'Admin'])) {
            return view('dashboard.admin', compact('user', 'announcements'));
        } 
        
        // Kepala Sekolah melihat dashboard khusus dengan statistik komprehensif
        if ($user->hasRole('Kepala Sekolah')) {
            return view('dashboard.kepsek', compact('user', 'announcements'));
        } 
        
        // Guru melihat jadwal mengajar & tugas
        if ($user->hasRole('Guru')) {
            return view('dashboard.guru', compact('user', 'announcements'));
        } 
        
        // Siswa melihat nilai, tugas mendatang, dan absen
        if ($user->hasRole('Siswa')) {
            return view('dashboard.siswa', compact('user', 'announcements'));
        } 
        
        // Ortu melihat tagihan anak, rekap nilai & absen
        if ($user->hasRole('Ortu')) {
            return view('dashboard.ortu', compact('user', 'announcements'));
        }

        // Default jika tidak punya role spesifik atau terjadi kesalahan
        return view('dashboard.default', compact('user', 'announcements'));
    }

    private function getRecentAnnouncements($user, int $limit = 4)
    {
        if (!$user) return collect();

        $query = Announcement::with('author')
            ->where('school_id', $user->school_id)
            ->published()
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at');

        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            // Admin & Kepsek melihat semua pengumuman published
        } elseif ($user->hasRole('Guru')) {
            $query->forRole('Guru');
        } else {
            $role    = $user->role;
            $classId = $user->student?->currentClass?->school_class_id;
            $query->forRole($role);
            if ($classId) {
                $query->forClass($classId);
            }
        }

        return $query->take($limit)->get();
    }
}
