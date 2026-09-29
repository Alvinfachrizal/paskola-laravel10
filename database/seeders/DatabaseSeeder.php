<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — Orchestrator semua seeder
 *
 * Urutan WAJIB dihormati karena ada relasi antar tabel:
 *   1. RoleAndUserSeeder    → Roles, School, akun utama (superadmin, admin, kepsek, guru default, siswa, ortu)
 *   2. MasterDataSeeder     → Tahun ajaran, semester, jurusan, kelas, mapel, guru lengkap, siswa 30+, ortu
 *   3. ModuleSeeder         → Tabel modules (toggle fitur)
 *   4. AcademicCalendarSeeder → Event kalender & kategori
 *   5. TimetableSeeder      → Hari aktif, ruangan, jam pelajaran, jadwal
 *   6. GradeSeeder          → Bobot nilai, input nilai, rapor
 *   7. FinanceSeeder        → Jenis tagihan
 *   8. DummyFinanceSeeder   → Tagihan & pembayaran demo per siswa
 *   9. ExamSeeder           → Bank soal, ujian, peserta, jawaban
 *  10. PpdbSeeder           → Gelombang PPDB, pendaftar, dokumen, seleksi
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // ── Fondasi ─────────────────────────────────────────────────────
            RoleAndUserSeeder::class,      // 1. School, roles, akun demo dasar

            // ── Master Data ──────────────────────────────────────────────────
            MasterDataSeeder::class,       // 2. Guru, siswa, kelas, mapel lengkap

            // ── Pengaturan Sistem ─────────────────────────────────────────────
            ModuleSeeder::class,           // 3. Toggle modul aktif/nonaktif

            // ── Akademik ─────────────────────────────────────────────────────
            AcademicCalendarSeeder::class, // 4. Kalender & event
            TimetableSeeder::class,        // 5. Jadwal pelajaran
            GradeSeeder::class,            // 6. Nilai & rapor

            // ── Keuangan ─────────────────────────────────────────────────────
            FinanceSeeder::class,          // 7. Jenis tagihan master
            DummyFinanceSeeder::class,     // 8. Tagihan & pembayaran demo

            // ── Ujian Online ──────────────────────────────────────────────────
            ExamSeeder::class,             // 9. Bank soal, ujian, peserta

            // ── PPDB ──────────────────────────────────────────────────────────
            PpdbSeeder::class,             // 10. Gelombang & pendaftar
        ]);

        $this->command->newLine();
        $this->command->info('✅ Semua seeder berhasil dijalankan!');
        $this->command->info('');
        $this->command->info('Akun utama untuk login:');
        $this->command->table(
            ['Role',          'Email',                    'Password'],
            [
                ['Super Admin',    'superadmin@paskola.com',  'password123'],
                ['Admin',          'admin@paskola.com',       'password123'],
                ['Kepala Sekolah', 'kepsek@paskola.com',      'password123'],
                ['Guru MTK',       'guru.mtk@paskola.com',    'password123'],
                ['Guru Ujian MTK', 'guru.ujian@paskola.com',  'password123'],
                ['Guru IPA',       'guru.ipa@paskola.com',    'password123'],
                ['Siswa X-IPA-1',  'siswa.xipa11@paskola.com','password123'],
                ['Ortu',           'ortu@paskola.com',        'password123'],
            ]
        );
    }
}
