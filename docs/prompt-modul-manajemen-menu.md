# Prompt: Bangun Modul Manajemen Menu (Toggle Modul) — dalam project Paskola Laravel

Copy-paste ke Claude Code/Cursor **di dalam project Laravel Paskola**. Modul ini bisa dibangun kapan saja (tidak bergantung pada modul akademik lain secara fungsional), tapi paling berguna setelah beberapa modul besar sudah ada (PPDB, Keuangan, dst) supaya ada yang benar-benar bisa dites di-toggle.

---

## PROMPT

Kamu adalah senior Laravel engineer yang juga sabar membimbing pemula (ingat: saya masih belajar, jelaskan alur tiap bagian, jangan asumsikan saya paham istilah teknis tanpa penjelasan singkat — ikuti format "ringkasan alur modul" yang sudah kita pakai di modul-modul sebelumnya).

Bangun **modul Manajemen Menu**, ditempatkan di halaman **Setting**, khusus bisa diakses role **Superadmin**. Fungsinya: superadmin bisa mengaktifkan/menonaktifkan modul-modul besar dalam sistem (PPDB, Keuangan, LMS, dst) sesuai kebutuhan sekolah.

### 1. Konsep Dasar (final, sudah disepakati)
- Granularitas **per modul besar** (PPDB, Keuangan Sekolah, LMS, Jadwal Pelajaran, Kalender Akademik, Nilai & Rapor, dst) — BUKAN per sub-menu detail di dalam modul
- Menonaktifkan modul harus **benar-benar memblokir akses** (middleware/route level), bukan cuma menyembunyikan link menu di sidebar
- **Beberapa modul saling bergantung** — sistem WAJIB validasi dependency dan **menolak keras** (bukan cascade otomatis) kalau superadmin coba nonaktifkan modul yang masih dibutuhkan modul lain yang aktif
- **Beberapa modul bersifat "core"** (Auth, Administrasi Data Dasar, Dashboard) — modul ini **tidak boleh dinonaktifkan sama sekali**, karena seluruh sistem bergantung padanya
- Kalau modul dinonaktifkan saat masih ada data berjalan (misal PPDB tengah musim daftar): **data tetap aman**, cuma akses dinonaktifkan — TIDAK menghapus data apa pun

### 2. Skema Database

Buat migration + Eloquent Model untuk:

- **`modules`** — daftar modul besar dalam sistem: `id, key (unique, string, contoh: 'ppdb', 'keuangan_sekolah'), name, description, is_core (boolean, default false), is_active (boolean, default true)`
- **`module_dependencies`** — tabel pivot, mendefinisikan modul mana butuh modul lain: `id, module_id (FK ke modules — modul yang punya dependency), depends_on_module_id (FK ke modules — modul yang dibutuhkan)`

**Data seeder wajib** — isi tabel `modules` dengan modul-modul yang SUDAH ada di sistem ini, dan `module_dependencies` dengan relasi yang sudah kita ketahui:
- `auth` (is_core: true), `data_dasar` (is_core: true), `dashboard` (is_core: true)
- `lms`, `kalender_akademik`, `jadwal_pelajaran` (depends_on: `kalender_akademik`), `nilai_rapor`, `ppdb`, `keuangan_sekolah` (depends_on: `ppdb`)

Cek ke saya dulu apakah daftar modul & dependency di atas sudah lengkap sesuai kondisi project sebenarnya sebelum di-seed — kalau ada modul yang saya lewatkan, tanya saya.

### 3. Logika Validasi Dependency (WAJIB, ini bagian paling penting)

Saat superadmin mencoba mengubah `is_active` suatu modul dari `true` ke `false`:
1. Cek: apakah modul ini `is_core = true`? Kalau ya → **tolak total**, modul core tidak bisa dinonaktifkan sama sekali, tampilkan pesan jelas
2. Cek: apakah ada modul LAIN yang `is_active = true` DAN punya baris di `module_dependencies` yang `depends_on_module_id` = modul yang mau dinonaktifkan?
3. Kalau ADA → **tolak (hard block, bukan cascade)**, tampilkan pesan spesifik menyebutkan nama modul yang masih bergantung. Contoh: "Tidak bisa menonaktifkan Kalender Akademik karena modul Jadwal Pelajaran masih aktif dan membutuhkannya. Nonaktifkan Jadwal Pelajaran dulu."
4. Kalau TIDAK ADA yang bergantung → boleh dinonaktifkan, simpan `is_active = false`

### 4. Penegakan Akses (Enforcement) — WAJIB di 2 tempat

**a) Middleware/Route level (WAJIB, ini yang benar-benar mengamankan):**
- Buat middleware (misal `EnsureModuleActive::class`) yang menerima parameter `key` modul
- Terapkan ke semua route group milik modul yang bisa di-toggle (PPDB, Keuangan, LMS, dst)
- Kalau modul nonaktif, middleware redirect ke halaman info "Modul ini sedang tidak aktif" — bukan error 500/404 yang membingungkan

**b) Tampilan menu (sidebar/navigasi):**
- Sembunyikan link menu untuk modul yang nonaktif, supaya user tidak melihat menu yang toh tidak bisa diakses

**Performa:** cache status `is_active` semua modul (misal pakai Laravel Cache, key `modules_status`), supaya tidak query database di setiap request. Invalidate cache begitu ada perubahan status modul oleh superadmin.

### 5. Batasan Role
- **Hanya role Superadmin** yang bisa akses halaman ini dan mengubah status modul — buat sebagai Policy/Gate terpisah, bukan role Admin sekolah biasa
- Kalau di sistem ini belum ada perbedaan antara "Superadmin" dan "Admin sekolah", tanya saya dulu bagaimana cara membedakannya (misal: kolom baru di tabel users, atau role terpisah)

### 6. Tampilan
- Halaman di menu **Setting**: daftar semua modul dalam bentuk list/card dengan toggle switch (on/off) per modul
- Modul `is_core` ditampilkan dengan toggle yang **disabled/terkunci** (bukan disembunyikan), dengan keterangan "Modul inti, tidak bisa dinonaktifkan" — supaya superadmin tahu itu memang sengaja terkunci, bukan bug
- Kalau ada dependency, tampilkan info kecil di tiap modul (misal: "Membutuhkan: Kalender Akademik") supaya superadmin bisa antisipasi sebelum coba matikan sesuatu

### 7. Cara Kerja (ikuti proses per-modul yang sudah kita sepakati sebelumnya)
1. Buat migration + model untuk tabel di poin 2, tampilkan daftar modul & dependency yang akan di-seed untuk saya cek dan koreksi dulu sebelum lanjut
2. Buat Seeder sesuai data yang sudah dikonfirmasi
3. Bangun logic validasi dependency (poin 3) sebagai method di Model/Service (misal `ModuleService::canDeactivate($moduleKey)`), tunjukkan cara kerjanya dengan beberapa skenario test sebelum lanjut ke UI
4. Bangun middleware `EnsureModuleActive` dan terapkan ke route group modul PPDB dan Keuangan Sekolah sebagai contoh (2 modul yang sudah ada), tunjukkan hasilnya
5. Bangun halaman Setting untuk superadmin
6. Update navigasi/sidebar supaya menu ikut nonaktif sesuai status modul
7. Setiap sub-bagian selesai: beri diagram alur singkat (`Route → Controller → Model → View`), penjelasan tiap file, cara edit kalau saya mau ubah sesuatu, cara test manual, lalu **tunggu saya konfirmasi paham** sebelum `git commit`
8. Buat dokumentasi di `docs/manajemen-menu.md`, termasuk daftar lengkap modul & dependency-nya sebagai referensi ke depan (setiap kali kita bangun modul baru, dokumen ini harus diupdate)

### Batasan Penting
- **Setiap kali kita bangun modul besar baru setelah ini** (misal nanti Absensi), modul itu WAJIB didaftarkan ke tabel `modules` (dan `module_dependencies` kalau relevan) sebagai bagian dari proses build-nya — supaya sistem toggle ini tetap up-to-date, bukan cuma mencakup modul yang sudah ada saat prompt ini dijalankan
- Uji skenario nyata: coba nonaktifkan Kalender Akademik selagi Jadwal Pelajaran aktif, pastikan ditolak dengan pesan yang jelas; coba nonaktifkan modul yang benar-benar tidak punya dependency aktif, pastikan berhasil dan link menu-nya hilang dari sidebar SEKALIGUS route-nya benar-benar terblokir (coba akses URL langsung)
- Styling ikuti aturan project: Bootstrap 5 default, kecuali saya minta Tailwind untuk halaman tertentu
- Kalau ada bagian yang ambigu (terutama soal struktur role Superadmin vs Admin), tanya saya dulu — jangan berimprovisasi sendiri

---

**Mulai dari poin 7.1 (migration + model + daftar modul untuk dikonfirmasi), tampilkan hasilnya sebelum lanjut.**
