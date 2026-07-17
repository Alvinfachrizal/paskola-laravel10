# Prompt: Bangun Modul Ujian & Tugas (LMS) + Modul Rapor (dalam project Paskola Laravel)

Copy-paste ke Claude Code/Cursor **di dalam project Laravel Paskola**, dijalankan setelah modul Auth, Administrasi Data Dasar, dan Dashboard selesai (sesuai urutan modul yang sudah disepakati: modul LMS dikerjakan sebelum Absensi & Nilai/Rapor).

---

## PROMPT

Kamu adalah senior Laravel engineer yang juga sabar membimbing pemula (ingat: saya masih belajar, jelaskan alur tiap bagian, jangan asumsikan saya paham istilah teknis tanpa penjelasan singkat — ikuti format "ringkasan alur modul" yang sudah kita pakai di modul-modul sebelumnya).

Bangun **dua modul yang saling terhubung**: **Ujian & Tugas** (bagian dari LMS) dan **Rapor** (agregasi nilai jadi rapor resmi). Ini BUKAN satu modul yang sama — keduanya punya tanggung jawab berbeda tapi berbagi satu titik pertemuan data.

### 1. Hubungan Kedua Modul (konsep penting, pahami dulu sebelum coding)
- **Modul Ujian & Tugas (LMS)**: tempat guru bikin materi, tugas, kuis, ujian online. Siswa mengerjakan online. Sistem hitung nilai otomatis untuk yang berbentuk pilihan ganda, manual untuk esai.
- **Modul Rapor**: tempat SEMUA nilai (baik dari LMS maupun UTS/UAS kertas yang diinput manual guru) diagregasi per siswa per mata pelajaran per semester, dihitung pakai bobot, lalu diverifikasi wali kelas sebelum dipublish jadi rapor resmi.
- Titik pertemuannya: tabel `student_grades` — nilai dari LMS otomatis masuk ke tabel ini (kolom `source = 'lms'`), nilai UTS/UAS kertas diinput manual guru ke tabel yang sama (kolom `source = 'manual'`). Modul Rapor tinggal membaca dari tabel ini untuk dihitung.

### 2. Skema Database (ERD yang sudah disepakati)

Buat migration + Eloquent Model untuk tabel-tabel berikut:

- **`subjects`** (kalau belum ada dari modul Administrasi Data Dasar) — mata pelajaran: `id, name`
- **`grade_weights`** — bobot komponen nilai per mata pelajaran, diatur guru: `id, subject_id (FK), component_type (enum: tugas/kuis/ujian_online/uts/uas/praktik), weight_percent`
  - **Validasi wajib**: total `weight_percent` untuk semua `component_type` dalam satu `subject_id` **harus tepat 100%** — tolak simpan (validasi di Form Request) kalau tidak pas, tampilkan pesan error yang jelas ke guru
- **`student_grades`** — nilai mentah per siswa per mapel per semester: `id, student_id (FK), subject_id (FK), semester_id (FK), component_type, score, source (enum: lms/manual)`
- **`report_cards`** — hasil akhir per siswa per mapel per semester: `id, student_id (FK), subject_id (FK), semester_id (FK), final_score, description (nullable — opsional, untuk narasi capaian gaya kurikulum merdeka), status (enum: draft/perlu_verifikasi/terverifikasi/published), verified_by (FK ke user wali kelas, nullable), verified_at (nullable)`

Status di `report_cards` dikelola sebagai PHP Enum class di Laravel, bukan enum kolom MySQL yang kaku.

### 3. Alur Proses (flowchart yang sudah disepakati)
```
Guru mapel input nilai (ujian tulis manual + ujian online otomatis dari LMS)
   → Nilai sementara dihitung otomatis per bobot (status: draft)
   → Wali kelas cocokkan semua nilai semua mapel untuk siswa di kelasnya
        ──[meragukan]──→ Konfirmasi/revisi oleh guru mapel (balik ke guru mapel, lalu ulang ke wali kelas)
        ──[sudah cocok]──→ Rapor final publish (siswa & orang tua bisa lihat)
```

### 4. Batasan Role (WAJIB diterapkan dengan tegas — ini bagian paling sensitif dari modul ini)

| Role | Kewenangan |
|---|---|
| **Guru mapel** | CRUD nilai (`student_grades`) **HANYA** untuk `subject_id` yang diampu DAN `student_id` yang ada di kelas yang diajar guru tersebut. Cek relasi guru-mapel-kelas di setiap request, jangan hanya mengandalkan role name saja |
| **Wali kelas** | Read semua `student_grades` & `report_cards` untuk siswa di kelas yang diwalikan. Bisa ubah `status` jadi `perlu_verifikasi` (kirim balik ke guru mapel) atau `terverifikasi`. **TIDAK BOLEH** mengubah `score` di `student_grades` secara langsung — itu wewenang guru mapel |
| **Admin/Kepala Sekolah** | Full akses, termasuk generate PDF rapor final dan publish |
| **Siswa/Orang tua** | Read-only, HANYA bisa lihat `report_cards` dengan `status = published` milik siswa yang bersangkutan (jangan sampai siswa A bisa lihat rapor siswa B — cek `student_id` cocok dengan akun yang login) |

Implementasikan ini menggunakan Laravel Policy/Gate per tabel (`StudentGradePolicy`, `ReportCardPolicy`), jangan hanya cek role di controller — supaya konsisten dan gampang di-maintain.

### 5. Detail Tambahan
- **Deskripsi capaian** (`report_cards.description`) bersifat **opsional** — sekolah yang tidak pakai kurikulum merdeka boleh mengosongkan field ini
- **Nilai terikat ke semester** (`semester_id`) — pastikan filter semester ganjil/genap konsisten di semua query (nilai semester ganjil tidak boleh tercampur dengan genap)
- **Riwayat perubahan**: setiap kali guru mapel merevisi nilai setelah dikirim balik wali kelas, catat di log (bisa pakai package `spatie/laravel-activitylog` atau tabel log manual sederhana) — supaya ada jejak audit siapa mengubah apa

### 6. Cara Kerja (ikuti proses per-modul yang sudah kita sepakati sebelumnya)
1. Buat migration + model dulu untuk semua tabel di poin 2, tampilkan ke saya untuk saya cek sebelum lanjut
2. Buat Seeder + Factory dengan data contoh (beberapa mapel dengan bobot berbeda, beberapa siswa dengan nilai dari kedua sumber `lms` dan `manual`) supaya saya bisa langsung coba
3. Buat Policy untuk role-based access sesuai poin 4, tampilkan ke saya untuk saya cek dulu sebelum lanjut ke UI
4. Bangun per sub-bagian secara berurutan, **tunggu konfirmasi saya paham alurnya** sebelum lanjut ke sub-bagian berikutnya:
   - a) Modul Ujian & Tugas: guru bikin materi/tugas/kuis/ujian online, siswa mengerjakan, penilaian otomatis untuk pilihan ganda
   - b) Guru input nilai manual (UTS/UAS kertas) ke `student_grades`
   - c) Halaman guru mapel: atur bobot nilai (`grade_weights`) dengan validasi 100%
   - d) Perhitungan otomatis nilai akhir sementara berdasarkan bobot
   - e) Dashboard wali kelas: cocokkan nilai semua mapel per kelas, kirim balik atau verifikasi
   - f) Generate & publish rapor final (PDF), termasuk halaman read-only untuk siswa/orang tua
5. Setiap sub-bagian selesai: beri diagram alur singkat (`Route → Controller → Model → View`), penjelasan tiap file, cara edit kalau saya mau ubah sesuatu, cara test manual (manfaatkan data seeder), lalu **tunggu saya konfirmasi paham** sebelum `git commit`
6. Buat dokumentasi di `docs/ujian-tugas.md` dan `docs/rapor.md` merangkum masing-masing modul setelah semua sub-bagian selesai

### Batasan Penting
- Jangan gabungkan modul Ujian & Tugas dan modul Rapor jadi satu Controller/tabel besar — pertahankan sebagai dua concern terpisah yang terhubung lewat `student_grades`, sesuai poin 1
- Styling ikuti aturan project: Bootstrap 5 default, kecuali saya minta Tailwind untuk halaman tertentu
- Kalau ada bagian yang ambigu (misal: daftar lengkap `component_type` yang berlaku, atau format PDF rapor), tanya saya dulu — jangan berimprovisasi sendiri
- Uji batasan role dengan skenario nyata sebelum saya anggap selesai: coba login sebagai guru mapel A, pastikan TIDAK BISA input nilai mapel B atau siswa di kelas lain

---

**Mulai dari poin 6.1 (migration + model), tampilkan hasilnya sebelum lanjut.**
