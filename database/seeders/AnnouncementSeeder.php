<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (!$school) {
            $this->command->warn('Sekolah tidak ditemukan. Jalankan RoleAndUserSeeder terlebih dahulu.');
            return;
        }

        $admin = User::where('email', 'admin@paskola.com')->first()
            ?? User::where('email', 'superadmin@paskola.com')->first()
            ?? User::first();

        $guru = User::where('email', 'guru@paskola.com')->first()
            ?? User::where('role', 'Guru')->first()
            ?? $admin;

        $firstClass = SchoolClass::where('school_id', $school->id)->first();
        $classIds   = $firstClass ? [$firstClass->id] : null;

        // Kosongkan announcement demo lama jika ada
        Announcement::where('school_id', $school->id)->delete();

        $announcements = [
            [
                'school_id'        => $school->id,
                'author_id'        => $admin->id,
                'title'            => 'Peringatan Hari Guru Nasional & Pelaksanaan Upacara Bendera',
                'content'          => "Diberitahukan kepada seluruh dewan guru, staf tata usaha, dan seluruh siswa SMA Nusantara bahwa dalam rangka memperingati Hari Guru Nasional, sekolah akan menyelenggarakan Upacara Bendera Khusus pada:\n\nHari/Tanggal: Senin, 25 November 2026\nWaktu: Pukul 07.00 WIB - Selesai\nTempat: Lapangan Utama SMA Nusantara\n\nKetentuan Pakaian:\n1. Dewan Guru & Staf: Seragam Batik PGRI resmi.\n2. Siswa: Seragam OSIS lengkap dengan dasi, topi, dan sepatu hitam.\n\nSeluruh siswa diharapkan hadir tepat waktu sebelum pukul 06.45 WIB. Terima kasih atas perhatian dan kerjasamanya.",
                'target_roles'     => null, // Semua role
                'target_class_ids' => null,
                'is_pinned'        => true,
                'priority'         => 'normal',
                'published_at'     => Carbon::now()->subDays(2),
                'expires_at'       => Carbon::now()->addDays(14),
            ],
            [
                'school_id'        => $school->id,
                'author_id'        => $admin->id,
                'title'            => 'PENTING: Jadwal & Tata Tertib Penilaian Akhir Semester (PAS) Ganjil',
                'content'          => "Yth. Bapak/Ibu Guru, Orang Tua/Wali Murid, serta Seluruh Siswa,\n\nBerikut kami sampaikan jadwal resmi pelaksanaan Penilaian Akhir Semester (PAS) Gasal Tahun Ajaran berjalan:\n\n1. Pelaksanaan Ujian: 1 - 10 Desember 2026.\n2. Bentuk Ujian: Ujian Berbasis Komputer/CBT melalui platform Paskola.\n3. Syarat Mengikuti Ujian: Telah melunasi administrasi dan memiliki Kartu Peserta Ujian Digital.\n\nMohon kepada orang tua untuk mendampingi dan memastikan kesiapan putra-putrinya selama periode ujian berlangsung. Jadwal per mapel dapat diakses melalui menu Ujian Saya atau Jadwal Pelajaran.",
                'target_roles'     => ['Guru', 'Siswa', 'Orang Tua'],
                'target_class_ids' => null,
                'is_pinned'        => true,
                'priority'         => 'urgent',
                'published_at'     => Carbon::now()->subDay(),
                'expires_at'       => Carbon::now()->addDays(20),
            ],
            [
                'school_id'        => $school->id,
                'author_id'        => $guru->id,
                'title'            => 'Batas Waktu Pengumpulan Laporan Praktikum Fisika',
                'content'          => "Pengingat bagi seluruh siswa:\n\nLaporan praktikum modul Gerak Lurus Berubah Beraturan (GLBB) wajib dikumpulkan paling lambat hari Jumat pekan ini pukul 23.59 WIB melalui portal LMS Paskola.\n\nFormat laporan berupa file PDF. Keterlambatan pengumpulan tanpa izin resmi akan dikenakan pengurangan nilai sebesar 10% per hari.",
                'target_roles'     => ['Siswa'],
                'target_class_ids' => $classIds,
                'is_pinned'        => false,
                'priority'         => 'urgent',
                'published_at'     => Carbon::now()->subHours(5),
                'expires_at'       => Carbon::now()->addDays(5),
            ],
            [
                'school_id'        => $school->id,
                'author_id'        => $admin->id,
                'title'            => 'Undangan Rapat Koordinasi Evaluasi Kurikulum & Kenaikan Kelas',
                'content'          => "Mengharap kehadiran Bapak/Ibu Dewan Guru dan Wali Kelas dalam rapat koordinasi bulanan yang akan dilaksanakan pada:\n\nHari/Tanggal: Kamis, 28 November 2026\nWaktu: 13.30 WIB s.d. selesai\nTempat: Ruang Multimedia & Pertemuan Lantai 2\nAgenda: Evaluasi capaian pembelajaran pertengahan semester dan verifikasi bank soal ujian.\n\nKehadiran Bapak/Ibu guru tepat waktu sangat diharapkan demi kelancaran kegiatan akademik.",
                'target_roles'     => ['Guru', 'Kepala Sekolah'],
                'target_class_ids' => null,
                'is_pinned'        => false,
                'priority'         => 'normal',
                'published_at'     => Carbon::now()->subDays(3),
                'expires_at'       => Carbon::now()->addDays(7),
            ],
            [
                'school_id'        => $school->id,
                'author_id'        => $guru->id,
                'title'            => '[DRAFT] Lomba Literasi & Pekan Kreativitas Siswa',
                'content'          => "Draf rencana perlombaan antar kelas dalam rangka menyemarakkan Pekan Kreativitas dan Literasi Sekolah:\n- Lomba Cipta & Baca Puisi\n- Lomba Pidato Bahasa Inggris & Indonesia\n- Pameran Karya Seni Rupa & Daur Ulang\n\n(Catatan internal: Tunggu approval kepala sekolah terkait anggaran hadiah).",
                'target_roles'     => ['Siswa'],
                'target_class_ids' => null,
                'is_pinned'        => false,
                'priority'         => 'normal',
                'published_at'     => null, // Draft
                'expires_at'       => null,
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::create($data);
        }

        $this->command->info('✅ AnnouncementSeeder: 5 data demo pengumuman berhasil dibuat.');
    }
}

