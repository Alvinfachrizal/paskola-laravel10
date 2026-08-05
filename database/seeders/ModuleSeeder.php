<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus cache status modul agar fresh
        Cache::forget('modules_status');

        // 1. Data modul utama
        $modules = [
            [
                'key'         => 'auth',
                'name'        => 'Autentikasi',
                'description' => 'Login, logout, dan manajemen session. Fondasi keamanan sistem.',
                'icon'        => 'bi-shield-lock',
                'is_core'     => true,
                'is_active'   => true,
                'sort_order'  => 1,
            ],
            [
                'key'         => 'data_dasar',
                'name'        => 'Administrasi & Data Master',
                'description' => 'Manajemen data induk: Kelas, Mata Pelajaran, Tahun Ajaran, Data Guru, dan Data Siswa.',
                'icon'        => 'bi-database',
                'is_core'     => true,
                'is_active'   => true,
                'sort_order'  => 2,
            ],
            [
                'key'         => 'dashboard',
                'name'        => 'Dashboard',
                'description' => 'Halaman utama sistem yang menampilkan ringkasan informasi per peran.',
                'icon'        => 'bi-speedometer2',
                'is_core'     => true,
                'is_active'   => true,
                'sort_order'  => 3,
            ],
            [
                'key'         => 'kalender_akademik',
                'name'        => 'Kalender Akademik',
                'description' => 'Manajemen event, hari libur, dan kalender sekolah. Dibutuhkan oleh Jadwal Pelajaran.',
                'icon'        => 'bi-calendar3',
                'is_core'     => false,
                'is_active'   => true,
                'sort_order'  => 4,
            ],
            [
                'key'         => 'jadwal_pelajaran',
                'name'        => 'Jadwal Pelajaran',
                'description' => 'Pengaturan jadwal mingguan guru dan siswa. Membutuhkan Kalender Akademik aktif.',
                'icon'        => 'bi-calendar-week',
                'is_core'     => false,
                'is_active'   => true,
                'sort_order'  => 5,
            ],
            [
                'key'         => 'lms',
                'name'        => 'LMS (Materi & Tugas)',
                'description' => 'Learning Management System: upload materi, buat tugas/ujian, dan pengumpulan tugas siswa.',
                'icon'        => 'bi-book',
                'is_core'     => false,
                'is_active'   => true,
                'sort_order'  => 6,
            ],
            [
                'key'         => 'nilai_rapor',
                'name'        => 'Nilai & Rapor',
                'description' => 'Input nilai, kalkulasi otomatis, verifikasi wali kelas, dan penerbitan rapor.',
                'icon'        => 'bi-star',
                'is_core'     => false,
                'is_active'   => true,
                'sort_order'  => 7,
            ],
            [
                'key'         => 'ppdb',
                'name'        => 'PPDB Online',
                'description' => 'Penerimaan Peserta Didik Baru: portal publik, verifikasi dokumen, dan daftar ulang.',
                'icon'        => 'bi-clipboard-check',
                'is_core'     => false,
                'is_active'   => true,
                'sort_order'  => 8,
            ],
            [
                'key'         => 'keuangan_sekolah',
                'name'        => 'Keuangan Sekolah',
                'description' => 'Manajemen tagihan, SPP, upload bukti bayar, verifikasi, dan rekap keuangan. Terhubung dengan riwayat bayar PPDB.',
                'icon'        => 'bi-wallet2',
                'is_core'     => false,
                'is_active'   => true,
                'sort_order'  => 9,
            ],
        ];

        // Gunakan updateOrCreate agar aman dijalankan ulang
        foreach ($modules as $data) {
            Module::updateOrCreate(['key' => $data['key']], $data);
        }

        $this->command->info('Modules seeded.');

        // 2. Data dependency
        $dependencies = [
            // Jadwal Pelajaran membutuhkan Kalender Akademik
            ['module' => 'jadwal_pelajaran', 'depends_on' => 'kalender_akademik'],
            // Keuangan Sekolah membutuhkan PPDB
            ['module' => 'keuangan_sekolah', 'depends_on' => 'ppdb'],
        ];

        foreach ($dependencies as $dep) {
            $module    = Module::where('key', $dep['module'])->first();
            $dependsOn = Module::where('key', $dep['depends_on'])->first();

            if ($module && $dependsOn) {
                $module->dependencies()->syncWithoutDetaching([$dependsOn->id]);
            }
        }

        $this->command->info('Module dependencies seeded.');
    }
}
