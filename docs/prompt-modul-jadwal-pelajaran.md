# Prompt: Bangun Modul Jadwal Pelajaran (Timetable) — dalam project Paskola Laravel

Copy-paste ke Claude Code/Cursor **di dalam project Laravel Paskola**, dijalankan setelah modul LMS **dan modul Kalender Akademik** selesai, dan **sebelum** modul Absensi (Jadwal Pelajaran adalah fondasi untuk Absensi, karena absensi per jam pelajaran butuh tahu siapa mengajar apa, di kelas mana, jam berapa).

---

## PROMPT

Kamu adalah senior Laravel engineer yang juga sabar membimbing pemula (ingat: saya masih belajar, jelaskan alur tiap bagian, jangan asumsikan saya paham istilah teknis tanpa penjelasan singkat — ikuti format "ringkasan alur modul" yang sudah kita pakai di modul-modul sebelumnya).

Bangun **modul Jadwal Pelajaran (Timetable)** untuk project Laravel Paskola.

### 1. Kebutuhan Fungsional (final, sudah disepakati)
- Sekolah bisa punya **5 atau 6 hari aktif** per minggu (bisa diatur admin, tidak di-hardcode)
- Jam pelajaran punya **durasi bebas/tidak seragam** (misal Jumat lebih pendek dari hari lain) — jangan asumsikan semua jam pelajaran sama panjang
- Ada konsep **shift** (pagi/siang) — beberapa ruang dipakai bergantian oleh kelas berbeda di shift berbeda dalam hari yang sama
- Ruang kelas dikelola sebagai **data master** (`rooms`), bukan teks bebas — beberapa ruang bersifat tetap (1 kelas tetap 1 ruang), beberapa bersifat umum/gantian (lab, aula)
- **Validasi bentrok WAJIB** — sistem harus mencegah 2 jadwal yang bentrok berdasarkan slot waktu yang sama

### 2. Skema Database (ERD yang sudah disepakati)

Buat migration + Eloquent Model untuk:

- **`academic_days`** — setting hari aktif: `id, day_of_week (enum: senin..minggu), is_active (boolean)`. Admin bisa toggle Sabtu aktif/nonaktif dari sini
- **`rooms`** — data master ruang: `id, name, type (enum: tetap/umum)`
- **`time_slots`** — jam pelajaran per hari: `id, day_of_week, shift (enum: pagi/siang), period_number, start_time, end_time`. Durasi dihitung dari selisih `start_time` dan `end_time`, TIDAK dihardcode sebagai durasi tetap
- **`schedules`** — jadwal inti: `id, time_slot_id (FK), room_id (FK), class_id (FK ke tabel classes yang sudah ada), subject_id (FK ke tabel subjects yang sudah ada), teacher_id (FK ke tabel teachers yang sudah ada), semester_id (FK)`

### 3. Aturan Validasi Bentrok (WAJIB, gunakan 1 aturan generik untuk semua kasus)
Sebelum menyimpan baris baru di `schedules`, tolak (validasi di Form Request, kembalikan pesan error jelas) kalau ada baris `schedules` LAIN dengan `time_slot_id` yang SAMA, DAN salah satu dari:
- `teacher_id` sama (guru tidak bisa mengajar 2 kelas di jam yang sama)
- `class_id` sama (kelas tidak bisa belajar 2 mapel di jam yang sama)
- `room_id` sama (ruang tidak bisa dipakai 2 kelas di jam yang sama)

**Penting:** jangan buat logic terpisah untuk "ruang tetap" vs "ruang umum" — aturan generik di atas otomatis benar untuk keduanya. Ruang tetap yang cuma dipakai 1 kelas tidak akan pernah memicu bentrok karena tidak ada kelas lain yang pakai `room_id` yang sama; ruang umum/lab yang dipakai gantian shift pagi/siang juga tidak bentrok karena `time_slot_id`-nya beda (shift berbeda = time_slot berbeda).

Tampilkan pesan error yang spesifik ke admin, misal: "Guru [nama] sudah mengajar kelas [X] di jam ini" — jangan cuma "Data bentrok" yang tidak jelas.

### 3b. Integrasi dengan Kalender Akademik (WAJIB — service ini sudah dibangun di modul sebelumnya)
Modul Kalender Akademik yang sudah selesai dibangun sebelumnya menyediakan `AcademicCalendarService::isHoliday($date, $classId)`. Saat menampilkan jadwal pelajaran untuk tanggal & kelas tertentu (dashboard guru maupun siswa):
1. Panggil service tersebut untuk cek apakah tanggal itu libur untuk kelas yang dicek
2. Kalau `true` → jangan tampilkan jadwal pelajaran normal, tampilkan keterangan libur (ambil nama event dari service)
3. Kalau `false` → tampilkan jadwal pelajaran seperti biasa
**Jangan menulis ulang logic pengecekan libur di modul ini** — panggil service yang sudah ada, supaya tidak ada duplikasi logic antara dua modul.

### 4. Tampilan
- **Admin**: form untuk input/edit jadwal per kelas, dengan pengecekan bentrok real-time (atau minimal saat submit) sebelum tersimpan
- **Guru & Siswa**: tampilan **grid mingguan** (hari sebagai kolom, jam pelajaran sebagai baris) — ini yang paling familiar dan mudah dibaca untuk jadwal sekolah, bukan list/daftar biasa
- Guru cuma lihat jadwal mengajarnya sendiri; siswa lihat jadwal kelasnya sendiri (bukan semua kelas)

### 5. Cara Kerja (ikuti proses per-modul yang sudah kita sepakati sebelumnya)
1. Buat migration + model untuk semua tabel di poin 2, tampilkan ke saya untuk saya cek sebelum lanjut
2. Buat Seeder + Factory dengan data contoh (beberapa hari aktif, beberapa time slot dengan durasi berbeda-beda termasuk shift pagi/siang, beberapa ruang tetap dan umum, dan jadwal yang saling terhubung) supaya saya bisa langsung coba
3. Bangun per sub-bagian secara berurutan, **tunggu konfirmasi saya paham alurnya** sebelum lanjut ke sub-bagian berikutnya:
   - a) Setting hari aktif & manajemen time slot (admin)
   - b) Manajemen ruang (`rooms`)
   - c) Form input jadwal admin + validasi bentrok (bagian paling penting, uji dengan skenario nyata: coba input 2 jadwal yang sengaja bentrok guru/kelas/ruang, pastikan ditolak)
   - d) Tampilan grid mingguan untuk guru
   - e) Tampilan grid mingguan untuk siswa
4. Setiap sub-bagian selesai: beri diagram alur singkat (`Route → Controller → Model → View`), penjelasan tiap file, cara edit kalau saya mau ubah sesuatu, cara test manual (manfaatkan data seeder, termasuk skenario bentrok yang sengaja dicoba), lalu **tunggu saya konfirmasi paham** sebelum `git commit`
5. Buat dokumentasi di `docs/jadwal-pelajaran.md` merangkum modul ini setelah semua sub-bagian selesai

### Batasan Penting
- Modul ini adalah **fondasi untuk modul Absensi** yang akan dibangun setelahnya — pastikan struktur `schedules` dan `time_slot_id` cukup jelas dan stabil untuk dijadikan referensi modul Absensi nanti (misal: guru ambil absensi berdasarkan jadwal mengajarnya di jam tersebut)
- Styling ikuti aturan project: Bootstrap 5 default, kecuali saya minta Tailwind untuk halaman tertentu
- Kalau ada bagian yang ambigu (misal: format waktu, apakah perlu dukungan multi-tahun ajaran sekaligus), tanya saya dulu — jangan berimprovisasi sendiri

---

**Mulai dari poin 5.1 (migration + model), tampilkan hasilnya sebelum lanjut.**
