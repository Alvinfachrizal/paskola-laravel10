# Prompt: Bangun Modul PPDB Online (dalam project Paskola Laravel)

Copy-paste ke Claude Code/Cursor **di dalam project Laravel Paskola** (setelah modul Auth, Administrasi Data Dasar, dan Dashboard sudah selesai sesuai urutan modul yang sudah disepakati).

---

## PROMPT

Kamu adalah senior Laravel engineer yang juga sabar membimbing pemula (ingat: saya masih belajar, jelaskan alur tiap bagian, jangan asumsikan saya paham istilah teknis tanpa penjelasan singkat — ikuti format "ringkasan alur modul" yang sudah kita pakai di modul-modul sebelumnya).

Bangun **modul PPDB (Penerimaan Peserta Didik Baru) Online**, terintegrasi dalam project Laravel Paskola yang sudah ada (bukan project terpisah).

### 1. Keputusan Arsitektur (final, ikuti persis)
- Modul ini **digabung** dalam project Laravel yang sama (bukan aplikasi terpisah)
- Data pendaftar disimpan di tabel **terpisah** dari tabel `students` (siswa aktif) — pendaftar baru "naik level" jadi siswa resmi setelah dinyatakan lolos DAN menyelesaikan daftar ulang
- Halaman pendaftaran (form, upload dokumen, cek status) bersifat **publik**, memakai autentikasi ringan sendiri (BUKAN akun staff/guru/siswa yang sudah ada) — gunakan skema **kode pendaftaran + tanggal lahir** atau **kode pendaftaran + OTP via email/WA** untuk login ulang calon siswa (pilih salah satu yang lebih sederhana untuk diimplementasikan, jelaskan trade-off-nya ke saya)

### 2. Alur Proses (flowchart yang sudah disepakati)
```
Buka portal PPDB → Registrasi akun → Isi formulir & dokumen (termasuk data seragam) → Submit pendaftaran
   → Verifikasi dokumen (panitia) ──[tidak valid]──→ Revisi dokumen (balik ke formulir)
   → Bayar pendaftaran (opsional, lihat poin 4) → Seleksi calon siswa ──[tidak lolos]──→ Selesai
   → Pengumuman lolos → Daftar ulang → Masuk ke data siswa (`students`)
```

### 3. Skema Database (ERD yang sudah disepakati)
Buat migration + Eloquent Model untuk tabel-tabel berikut (nama tabel boleh disesuaikan konvensi Laravel, tapi struktur & relasi harus sama):

- **`ppdb_waves`** — gelombang pendaftaran: `id, name, quota, start_date, end_date`. Default: sistem harus tetap bisa jalan meski cuma ada 1 baris data gelombang (jangan hardcode asumsi "hanya 1 gelombang" di level kode)
- **`ppdb_applicants`** — pendaftar, sebagai pusat relasi: `id, wave_id (FK), registration_code (unique), full_name, nisn, birth_date, gender, address, parent_name, parent_phone, status (enum: pending/verified/need_revision/selected/rejected/re-registered)`
- **`ppdb_documents`** — dokumen terlampir, relasi 1-ke-banyak ke applicant: `id, applicant_id (FK), doc_type, file_path, status (enum: pending/valid/invalid), verified_by, verified_at`
- **`ppdb_payments`** — pembayaran, relasi 1-ke-banyak ke applicant (karena bisa ada pembayaran pendaftaran & daftar ulang terpisah): `id, applicant_id (FK), payment_type, amount, status, paid_at`
- **`ppdb_selection_scores`** — nilai seleksi, relasi 1-ke-banyak ke applicant: `id, applicant_id (FK), score_type, score_value`
- **`ppdb_uniform_orders`** — data seragam, relasi 1-ke-1 ke applicant: `id, applicant_id (FK), gender, pakai_kerudung (boolean, nullable), jenis_bawahan (enum: rok/celana, nullable), ukuran (enum: S/M/L/XL/XXL, WAJIB diisi)`
- **`ppdb_reregistrations`** — daftar ulang, relasi 1-ke-1 ke applicant: `id, applicant_id (FK), status, completed_at`

Status enum sebaiknya dikelola sebagai PHP Enum class di Laravel (bukan enum kolom database MySQL yang kaku), supaya gampang ditambah statusnya nanti tanpa migration baru.

### 4. Detail Field & Aturan Bisnis

**Biaya pendaftaran (opsional, konfigurable):**
- Tambahkan setting/config yang menentukan apakah biaya pendaftaran aktif atau tidak (misal di tabel `ppdb_waves` tambah kolom `registration_fee`, isi `0` atau `null` kalau gratis)
- Kalau `registration_fee > 0`, munculkan langkah pembayaran di alur; kalau tidak, skip otomatis ke tahap seleksi

**Kriteria seleksi:**
- Kombinasi nilai (dari `ppdb_selection_scores`) + kuota per gelombang (`ppdb_waves.quota`)
- Sediakan juga opsi **override manual** oleh panitia (panitia bisa ubah status applicant jadi "selected"/"rejected" secara manual dari dashboard, tidak harus murni otomatis dari skor)

**Data Seragam — WAJIB diisi saat formulir pendaftaran awal, dengan aturan tampilan conditional:**
- Field `gender` (laki-laki/perempuan) — wajib
- **Kalau `gender = perempuan`**: tampilkan field `pakai_kerudung` (ya/tidak, wajib) dan `jenis_bawahan` (rok/celana, wajib) — gunakan JavaScript sederhana (show/hide, tanpa perlu library berat) supaya field ini otomatis muncul begitu pendaftar pilih "perempuan"
- **Kalau `gender = laki-laki`**: field `pakai_kerudung` dan `jenis_bawahan` TIDAK ditampilkan sama sekali di form, backend otomatis set `jenis_bawahan = 'celana'` dan `pakai_kerudung = false`
- Field `ukuran` — dropdown pilihan huruf saja (S/M/L/XL/XXL), **wajib diisi**, berlaku untuk semua jenis seragam sekolah (tidak dipecah per jenis seragam olahraga/OSIS/pramuka — cukup 1 ukuran mewakili semua)

### 5. Fitur Dashboard Panitia (tambahan di luar alur calon siswa)
- Halaman daftar pendaftar dengan filter status, gelombang, dan pencarian
- Halaman verifikasi dokumen per pendaftar (approve/reject dengan catatan revisi)
- Halaman input/override nilai seleksi dan hasil kelulusan
- **Halaman rekap kebutuhan seragam**: tampilkan jumlah pendaftar per kombinasi ukuran + gender + kerudung/rok (misal: "Ukuran M, laki-laki: 12 orang", "Ukuran S, perempuan berkerudung+rok: 8 orang") — untuk memudahkan pemesanan massal ke konveksi

### 6. Cara Kerja (ikuti proses per-modul yang sudah kita sepakati sebelumnya)
1. Buat migration + model dulu untuk semua tabel di poin 3, tampilkan ke saya untuk saya cek sebelum lanjut
2. Buat Seeder + Factory dengan data contoh (beberapa pendaftar dummy dengan variasi status, gender, ukuran seragam) supaya saya bisa langsung coba
3. Bangun per sub-bagian secara berurutan, **tunggu konfirmasi saya paham alurnya** sebelum lanjut ke sub-bagian berikutnya:
   - a) Form pendaftaran publik (termasuk field seragam conditional)
   - b) Login ulang calon siswa (cek status) 
   - c) Dashboard panitia — verifikasi dokumen
   - d) Dashboard panitia — seleksi & pengumuman
   - e) Alur daftar ulang + integrasi otomatis ke tabel `students`
   - f) Dashboard panitia — rekap kebutuhan seragam
4. Setiap sub-bagian selesai: beri diagram alur singkat (`Route → Controller → Model → View`), penjelasan tiap file, cara edit kalau saya mau ubah sesuatu, cara test manual, lalu **tunggu saya konfirmasi paham** sebelum `git commit`
5. Buat dokumentasi di `docs/ppdb.md` merangkum seluruh modul setelah semua sub-bagian selesai

### Batasan Penting
- Jangan ubah struktur tabel modul lain (`students`, `users`, dll) kecuali untuk menambah relasi/kolom yang memang diperlukan modul ini — dan tanya saya dulu sebelum melakukannya
- Styling ikuti aturan project: Bootstrap 5 default, kecuali saya minta Tailwind untuk halaman tertentu
- Kalau ada bagian yang ambigu (misal: skema login ulang calon siswa, atau opsi ukuran seragam mau ditambah/dikurangi dari S-XXL), tanya saya dulu — jangan berimprovisasi sendiri

---

**Mulai dari poin 6.1 (migration + model), tampilkan hasilnya sebelum lanjut.**
