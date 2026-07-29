# Prompt: Bangun Modul Kalender Akademik — dalam project Paskola Laravel

Copy-paste ke Claude Code/Cursor **di dalam project Laravel Paskola**, dijalankan setelah modul Data Dasar & Dashboard selesai, dan **sebelum** modul Jadwal Pelajaran. Modul ini berdiri sendiri (tidak bergantung pada Jadwal Pelajaran), tapi menyediakan `AcademicCalendarService` yang nanti **dipakai** oleh modul Jadwal Pelajaran untuk fitur "hari libur otomatis meniadakan jadwal".

---

## PROMPT

Kamu adalah senior Laravel engineer yang juga sabar membimbing pemula (ingat: saya masih belajar, jelaskan alur tiap bagian, jangan asumsikan saya paham istilah teknis tanpa penjelasan singkat — ikuti format "ringkasan alur modul" yang sudah kita pakai di modul-modul sebelumnya).

Bangun **modul Kalender Akademik** untuk project Laravel Paskola, terintegrasi dengan modul Jadwal Pelajaran yang sudah ada.

### 1. Kebutuhan Fungsional (final, sudah disepakati)
- Kategori event (libur, ujian, kegiatan, dll) dibuat **fleksibel sebagai data master** — admin bisa tambah kategori baru kapan saja, **jangan hardcode daftar kategori di kode**
- Kalau tanggal ditandai kategori yang bersifat "libur", jadwal pelajaran hari itu **otomatis tidak aktif**
- **Admin** bisa membuat event untuk seluruh sekolah maupun kelas tertentu, termasuk kategori "libur"
- **Guru** hanya bisa membuat event untuk **kelas yang dia ajar sendiri** (misal: jadwal ujian susulan), dan **TIDAK BOLEH** memilih kategori yang bersifat "libur" (mencegah guru menonaktifkan jadwal sekolah/kelas lain secara tidak sengaja atau sengaja)

### 2. Skema Database (ERD yang sudah disepakati)

Buat migration + Eloquent Model untuk:

- **`event_categories`** — kategori event: `id, name, is_holiday (boolean), color (untuk tampilan kalender, hex code)`
- **`academic_events`** — event aktual: `id, category_id (FK), class_id (FK ke classes, NULLABLE), created_by (FK ke users/teachers), title, start_date, end_date, description (nullable)`
  - `class_id = NULL` berarti event berlaku untuk **seluruh sekolah**
  - `class_id` diisi berarti event khusus **1 kelas tertentu**

### 3. Sediakan Service untuk Dipakai Modul Jadwal Pelajaran Nanti (BAGIAN PALING PENTING)
Modul Jadwal Pelajaran **belum dibangun** saat ini, jadi modul Kalender Akademik ini TIDAK perlu (dan tidak bisa) mengintegrasikan diri ke tampilan jadwal apa pun sekarang. Tugasmu di modul ini adalah **menyediakan service yang siap dipakai nanti**:

Buat method `AcademicCalendarService::isHoliday($date, $classId = null)` yang:
1. Cek apakah ada baris `academic_events` yang kategorinya (`event_categories.is_holiday = true`) mencakup `$date`
2. DAN `academic_events.class_id` bernilai NULL (berlaku seluruh sekolah) ATAU sama dengan `$classId` yang diberikan
3. Kalau kondisi di atas terpenuhi → return `true` beserta nama event (`title`) yang menyebabkan hari itu libur
4. Kalau tidak ada yang cocok → return `false`

Buat juga unit test sederhana untuk method ini (beberapa skenario: tanggal libur sekolah-wide, tanggal libur khusus 1 kelas, tanggal biasa) supaya saat modul Jadwal Pelajaran dibangun nanti, kita tinggal **memanggil** service ini dengan yakin tanpa perlu baca ulang logic-nya.

### 4. Batasan Role (WAJIB diterapkan pakai Laravel Policy)
| Role | Kewenangan |
|---|---|
| **Admin/Kepala Sekolah** | CRUD penuh `event_categories` dan `academic_events`, termasuk event seluruh sekolah dan kategori `is_holiday` |
| **Guru** | Hanya bisa membuat `academic_events` dengan `class_id` = kelas yang dia ajar (cek relasi guru-kelas), dan **hanya boleh memilih kategori dengan `is_holiday = false`** — validasi ini di Form Request, tolak kalau guru coba pilih kategori holiday |
| **Siswa/Orang tua** | Read-only, lihat kalender akademik sekolah + event khusus kelasnya |

### 5. Tampilan
- Tampilan utama: **kalender bulanan** (grid tanggal seperti kalender pada umumnya), event ditandai dengan warna sesuai `event_categories.color`
- Klik/hover tanggal menampilkan detail event di tanggal tersebut (bisa lebih dari 1 event per tanggal)
- Filter tampilan: admin bisa lihat semua, guru/siswa defaultnya lihat kalender sekolah + kelasnya saja

### 6. Cara Kerja (ikuti proses per-modul yang sudah kita sepakati sebelumnya)
1. Buat migration + model untuk semua tabel di poin 2, tampilkan ke saya untuk saya cek sebelum lanjut
2. Buat Seeder + Factory dengan data contoh (beberapa kategori termasuk yang holiday dan bukan, beberapa event sekolah-wide dan beberapa event khusus 1 kelas) supaya saya bisa langsung coba
3. Buat `AcademicCalendarService` beserta unit test-nya (skenario di poin 3), tampilkan ke saya cara kerjanya dan hasil test sebelum lanjut
4. Bangun per sub-bagian secara berurutan, **tunggu konfirmasi saya paham alurnya** sebelum lanjut ke sub-bagian berikutnya:
   - a) Manajemen kategori event (admin)
   - b) Manajemen event sekolah-wide (admin)
   - c) Manajemen event khusus kelas (guru, dengan batasan role)
   - d) Tampilan kalender bulanan untuk semua role
5. Setiap sub-bagian selesai: beri diagram alur singkat (`Route → Controller → Model → View`), penjelasan tiap file, cara edit kalau saya mau ubah sesuatu, cara test manual (manfaatkan data seeder), lalu **tunggu saya konfirmasi paham** sebelum `git commit`
6. Buat dokumentasi di `docs/kalender-akademik.md` merangkum modul ini setelah semua sub-bagian selesai, termasuk penjelasan cara pakai `AcademicCalendarService` untuk modul lain yang akan mengintegrasikannya nanti (terutama Jadwal Pelajaran)

### Batasan Penting
- `AcademicCalendarService` harus **berdiri sendiri dan reusable** — jangan taruh logic pengecekan libur di controller, supaya modul lain (terutama Jadwal Pelajaran nanti) tinggal memanggil service ini tanpa duplikasi logic
- Uji batasan role dengan skenario nyata: coba login sebagai guru, pastikan **tidak bisa** pilih kategori `is_holiday = true` saat membuat event, dan **tidak bisa** membuat event untuk kelas yang bukan diajarnya
- Styling ikuti aturan project: Bootstrap 5 default, kecuali saya minta Tailwind untuk halaman tertentu
- Kalau ada bagian yang ambigu, tanya saya dulu — jangan berimprovisasi sendiri

---

**Mulai dari poin 6.1 (migration + model), tampilkan hasilnya sebelum lanjut.**
