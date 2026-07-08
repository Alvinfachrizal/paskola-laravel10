<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Cek peran user dan arahkan ke view (tampilan) yang sesuai
        // Admin, Super Admin, dan Kepsek melihat dashboard admin yang berisi statistik global
        if ($user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return view('dashboard.admin', compact('user'));
        } 
        
        // Guru melihat jadwal mengajar & tugas
        if ($user->hasRole('Guru')) {
            return view('dashboard.guru', compact('user'));
        } 
        
        // Siswa melihat nilai, tugas mendatang, dan absen
        if ($user->hasRole('Siswa')) {
            return view('dashboard.siswa', compact('user'));
        } 
        
        // Ortu melihat tagihan anak, rekap nilai & absen
        if ($user->hasRole('Ortu')) {
            return view('dashboard.ortu', compact('user'));
        }

        // Default jika tidak punya role spesifik atau terjadi kesalahan
        return view('dashboard.default', compact('user'));
    }
}
