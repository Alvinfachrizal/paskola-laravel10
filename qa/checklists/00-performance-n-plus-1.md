# Checklist QA — Performa & N+1 Query

> **Apa itu N+1 Query?** Ini adalah masalah performa yang umum di aplikasi yang menggunakan ORM seperti Laravel Eloquent. Bayangkan Anda menampilkan daftar 30 siswa beserta nama kelas mereka. Jika kode tidak efisien, Laravel akan menjalankan 1 query untuk mengambil 30 siswa, lalu 1 query LAGI untuk setiap siswa untuk mengambil nama kelasnya = total 31 query. Jika ada 100 siswa, jadinya 101 query. Inilah "N+1". Solusinya: gunakan **Eager Loading** (`->with('class')`) agar semua data diambil hanya dengan 2 query, berapapun jumlah siswanya.

> **Alat yang Digunakan:** `barryvdh/laravel-debugbar` (HANYA untuk development!)
>
> **Cara Install:** `composer require barryvdh/laravel-debugbar --dev`
>
> **⚠️ PENTING:** Package ini HANYA boleh aktif jika `APP_DEBUG=true` di `.env`. Di environment production, **WAJIB** dimatikan dengan `APP_DEBUG=false`. Debugbar otomatis tidak aktif jika `APP_DEBUG=false`.
>
> **Cara Mengaktifkan:** Setelah install, aktifkan dengan membuka browser. Debugbar akan muncul sebagai toolbar di bagian bawah halaman. Klik tab "Queries" untuk melihat semua query SQL yang berjalan di halaman tersebut.

## Status: [ ] Belum diuji / [ ] Sedang diuji / [x] Lulus / [ ] Ada temuan

---

## Kondisi Data Minimum untuk Pengujian

Sebelum menguji, pastikan ada data yang representatif (bukan hanya 2-3 baris). Target minimum:
- **30+ siswa** (sudah disediakan oleh `MasterDataSeeder`)
- **10+ soal per mapel** (sudah disediakan oleh `QuestionBankSeeder`)
- **5+ ujian** (sudah disediakan oleh `ExamSeeder`)

---

## Tabel Hasil Pengujian

| Halaman | URL | Jumlah Query | Status | File & Query Penyebab (jika N+1) |
|---|---|---|---|---|
| Dashboard Admin | `/dashboard` | | [ ] Wajar / [ ] N+1 | |
| Dashboard Guru | `/dashboard` (login Guru) | | [ ] Wajar / [ ] N+1 | |
| Dashboard Siswa | `/dashboard` (login Siswa) | | [ ] Wajar / [ ] N+1 | |
| Daftar Siswa (Admin) | `/admin/students` | | [ ] Wajar / [ ] N+1 | |
| Daftar Guru (Admin) | `/admin/teachers` | | [ ] Wajar / [ ] N+1 | |
| Nilai per Kelas | `/grades/...` | | [ ] Wajar / [ ] N+1 | |
| Halaman Verifikasi Wali Kelas | `/grades/verify/...` | | [ ] Wajar / [ ] N+1 | |
| Grid Jadwal Guru | `/timetable` (login Guru) | | [ ] Wajar / [ ] N+1 | |
| Grid Jadwal Siswa | `/timetable` (login Siswa) | | [ ] Wajar / [ ] N+1 | |
| Riwayat Keuangan Siswa | `/finance/history/...` | | [ ] Wajar / [ ] N+1 | |
| Daftar Peserta Ujian | `/exam/exams/{id}/participants` | | [ ] Wajar / [ ] N+1 | |
| Halaman Hasil Ujian | `/exam/exams/{id}/results` | | [ ] Wajar / [ ] N+1 | |
| Bank Soal | `/exam/questions` | | [ ] Wajar / [ ] N+1 | |
| Kalender Akademik | `/calendar` | | [ ] Wajar / [ ] N+1 | |
| Daftar Pendaftar PPDB | `/admin/ppdb` | | [ ] Wajar / [ ] N+1 | |

---

## Target yang Dapat Diterima

- Jumlah query per halaman **tidak bertambah mengikuti jumlah baris data** (tanda N+1).
- Idealnya: **di bawah 20 query** untuk halaman biasa yang menampilkan daftar data dengan relasi.
- Jika jumlah query sangat tinggi (50+) atau bertambah sebanding dengan data, itu adalah indikasi kuat N+1.

---

## Ringkasan Temuan N+1 (isi setelah testing)

| Halaman | File Controller | Query Penyebab | Prioritas Perbaikan | Status |
|---|---|---|---|---|
| LMS Submissions | `LmsSubmissionController.php` | `$submission->assignment->subject` tidak di-*eager load* | Tinggi | ✅ Diperbaiki |
| Daftar Ujian | `ExamController.php` | `$exam->participants()->count()` (query berulang dalam loop) | Tinggi | ✅ Diperbaiki (withCount) |

---

> **Ingat:** Jangan perbaiki N+1 di sesi QA ini. Catat temuannya di tabel di atas, lalu buat sesi/prompt terpisah untuk perbaikan menggunakan Eager Loading.
