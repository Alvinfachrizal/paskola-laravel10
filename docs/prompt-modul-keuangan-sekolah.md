# Prompt: Bangun Modul Keuangan Sekolah — dalam project Paskola Laravel

Copy-paste ke Claude Code/Cursor **di dalam project Laravel Paskola**, dijalankan setelah modul PPDB dan modul Administrasi Data Dasar selesai (fokus sekolah swasta: SPP, uang komite, seragam, uang gedung).

---

## PROMPT

Kamu adalah senior Laravel engineer yang juga sabar membimbing pemula (ingat: saya masih belajar, jelaskan alur tiap bagian, jangan asumsikan saya paham istilah teknis tanpa penjelasan singkat — ikuti format "ringkasan alur modul" yang sudah kita pakai di modul-modul sebelumnya).

Bangun **modul Keuangan Sekolah** untuk project Laravel Paskola, terintegrasi dengan modul PPDB yang sudah ada.

### 1. Konsep Dasar (final, sudah disepakati)
- Modul ini adalah **"buku besar" tunggal** untuk semua kewajiban finansial siswa **setelah resmi jadi siswa** — SPP bulanan, uang komite, seragam, dan jenis tagihan lain yang bisa ditambah admin kapan saja
- **Biaya pendaftaran & daftar ulang PPDB TETAP dicatat di `ppdb_payments`** (modul PPDB), TIDAK dipindah ke modul ini — tapi begitu applicant resmi jadi siswa, `ppdb_payments.student_id` otomatis terisi, sehingga riwayat itu **tersambung** dan bisa digabung saat menampilkan laporan keuangan 1 siswa secara utuh
- Sekolah ini fokus **swasta** (SPP berbayar), tapi jenis tagihan tetap dibuat fleksibel (data master), bukan hardcode, supaya sekolah lain (misal negeri tanpa SPP) tetap bisa pakai modul ini dengan jenis tagihan yang relevan buat mereka

### 2. Skema Database

Buat migration + Eloquent Model untuk:

- **`bill_types`** — jenis tagihan, data master yang bisa ditambah admin: `id, name, is_recurring (boolean), default_amount (decimal)`
- **`student_bills`** — tagihan aktual per siswa: `id, student_id (FK), bill_type_id (FK), period (nullable, diisi untuk tagihan recurring misal "2026-08"), amount (decimal, bisa override dari default_amount), due_date, status (enum: belum_bayar/menunggu_verifikasi/lunas/terlambat)`
- **`payments`** — riwayat pembayaran: `id, student_bill_id (FK), method (enum: manual/gateway), proof_file (nullable, untuk manual), gateway_reference (nullable, untuk gateway), amount_paid, status (enum: pending/verified/rejected), verified_by (FK ke users, nullable), verified_at (nullable)`

Status dikelola sebagai PHP Enum class di Laravel, bukan enum kolom MySQL yang kaku.

### 3. Integrasi dengan Modul PPDB (WAJIB)
- **Update migration `ppdb_payments`** (kalau belum): tambah kolom `student_id` (FK ke `students`, nullable)
- Saat proses "daftar ulang selesai & data siswa resmi dibuat" di modul PPDB, tambahkan step: **isi otomatis `student_id`** di SEMUA baris `ppdb_payments` milik applicant tersebut (biaya pendaftaran maupun daftar ulang)
- Buat **1 method/service gabungan** (misal `StudentFinanceService::getFullHistory($studentId)`) yang mengambil data dari **kedua sumber**: `ppdb_payments` (`where student_id = ...`) DAN `student_bills` (`where student_id = ...`), digabung jadi 1 riwayat keuangan lengkap per siswa, diurutkan berdasarkan tanggal

### 4. Generate Tagihan Awal Otomatis Saat Siswa Baru Dibuat
Saat data siswa baru dibuat (dari PPDB yang lolos & daftar ulang, ATAU dari input manual admin untuk siswa pindahan), sistem otomatis membuat `student_bills` untuk jenis tagihan yang ditandai "wajib saat masuk" (tambahkan kolom `is_initial_bill` di `bill_types` untuk menandai ini — misal uang gedung, seragam)

### 5. Generate Tagihan Berulang (SPP dkk)
- Tagihan dengan `is_recurring = true` (SPP) perlu digenerate tiap bulan untuk semua siswa aktif
- Buat sebagai **Laravel Scheduled Command** (`php artisan schedule:run`, dijalankan tiap awal bulan) — jelaskan ke saya cara setup scheduler ini di Laragon karena mekanismenya beda dari server production biasa (Laragon tidak punya cron job otomatis seperti Linux, perlu Windows Task Scheduler atau dijalankan manual saat development)
- Cegah duplikasi: jangan generate ulang SPP bulan yang sama untuk siswa yang sudah punya baris `student_bills` di periode itu

### 6. Metode Pembayaran
- **Manual (fokus utama untuk MVP)**: siswa/ortu upload bukti transfer → status `menunggu_verifikasi` → admin/bendahara verifikasi → status `lunas`
- **Payment gateway (opsional, bisa disusul belakangan)**: integrasi Midtrans/Xendit untuk pembayaran otomatis tanpa perlu verifikasi manual — tanya saya dulu sebelum implementasi bagian ini, karena butuh akun & API key pihak ketiga yang belum tentu sudah saya siapkan

### 7. Batasan Role
| Role | Kewenangan |
|---|---|
| **Admin/Bendahara** | CRUD `bill_types`, generate/edit `student_bills`, verifikasi `payments` manual, lihat rekap keuangan seluruh siswa |
| **Siswa/Orang tua** | Read tagihan & riwayat pembayaran **milik sendiri saja**, upload bukti bayar |
| **Guru/Wali kelas** | Tidak perlu akses ke modul ini (kecuali nanti kamu minta wali kelas bisa lihat status SPP kelasnya untuk keperluan tertentu) |

### 8. Tampilan
- **Siswa/Orang tua**: daftar tagihan aktif + riwayat lunas, tombol upload bukti bayar per tagihan
- **Admin/Bendahara**: dashboard rekap (total tertagih, total lunas, total belum bayar), halaman verifikasi pembayaran manual, halaman kelola jenis tagihan
- **Riwayat keuangan siswa** (gabungan PPDB + Keuangan) ditampilkan sebagai 1 timeline utuh per siswa, bukan 2 tabel terpisah yang membingungkan

### 9. Cara Kerja (ikuti proses per-modul yang sudah kita sepakati sebelumnya)
1. Buat migration + model untuk semua tabel di poin 2 & update `ppdb_payments` di poin 3, tampilkan ke saya untuk saya cek sebelum lanjut
2. Buat Seeder + Factory dengan data contoh (beberapa jenis tagihan termasuk yang recurring dan initial, beberapa siswa dengan riwayat PPDB yang sudah tersambung `student_id`, beberapa tagihan dengan status berbeda) supaya saya bisa langsung coba
3. Bangun `StudentFinanceService` dan tunjukkan hasil gabungan riwayatnya sebelum lanjut ke UI
4. Bangun per sub-bagian secara berurutan, **tunggu konfirmasi saya paham alurnya** sebelum lanjut ke sub-bagian berikutnya:
   - a) Manajemen jenis tagihan (admin)
   - b) Generate tagihan awal otomatis saat siswa baru dibuat
   - c) Generate tagihan berulang (SPP) + penjelasan scheduler di Laragon
   - d) Upload bukti bayar (siswa/ortu) + verifikasi manual (admin)
   - e) Dashboard rekap keuangan admin
   - f) Tampilan riwayat keuangan gabungan per siswa
5. Setiap sub-bagian selesai: beri diagram alur singkat (`Route → Controller → Model → View`), penjelasan tiap file, cara edit kalau saya mau ubah sesuatu, cara test manual (manfaatkan data seeder), lalu **tunggu saya konfirmasi paham** sebelum `git commit`
6. Buat dokumentasi di `docs/keuangan-sekolah.md` merangkum modul ini, termasuk cara kerja integrasi ke PPDB

### Batasan Penting
- Jangan hilangkan/pindahkan data `ppdb_payments` yang sudah ada — cuma tambah kolom `student_id` dan isi otomatis, riwayat lama tetap utuh
- Uji skenario nyata: buat 1 applicant PPDB dummy, proses sampai lolos+daftar ulang, pastikan `student_id` di `ppdb_payments`-nya terisi otomatis dan muncul di riwayat gabungan
- Styling ikuti aturan project: Bootstrap 5 default, kecuali saya minta Tailwind untuk halaman tertentu
- Kalau ada bagian yang ambigu, tanya saya dulu — jangan berimprovisasi sendiri

---

**Mulai dari poin 9.1 (migration + model), tampilkan hasilnya sebelum lanjut.**
