# Dokumentasi Modul PPDB Online — Paskola Laravel

> Dibuat: {{ date('Y-m-d') }}  
> Status: ✅ Selesai (Sub-bagian a–f)

---

## 1. Ringkasan Alur Sistem

```
[Publik] Buka /ppdb → Pilih Gelombang → Isi Form → Upload Dokumen → Submit
           ↓
     Dapat Kode Pendaftaran (misal: PPDB-2026-00001)
           ↓
[Panitia] Verifikasi Dokumen satu per satu
     → Jika ada yang invalid: status → "Perlu Revisi" (pendaftar upload ulang)
     → Jika semua valid:      status → "Terverifikasi"
           ↓
[Panitia] Input Nilai Seleksi (Matematika, B.Indonesia, IPA, dll)
[Panitia] Override Status → "Lolos Seleksi" atau "Tidak Lolos"
           ↓
[Panitia] Proses Daftar Ulang → Otomatis buat akun User + data Student
           ↓
     Status → "Daftar Ulang Selesai"
           ↓
[Siswa] Data masuk ke tabel students (siswa resmi)
```

---

## 2. Tabel Database

| Tabel | Fungsi |
|---|---|
| `ppdb_waves` | Gelombang pendaftaran (kuota, biaya, jadwal) |
| `ppdb_applicants` | Pusat data pendaftar (satu record per calon siswa) |
| `ppdb_documents` | Dokumen yang diupload (1-ke-banyak ke applicant) |
| `ppdb_payments` | Riwayat pembayaran (opsional, jika gelombang berbayar) |
| `ppdb_selection_scores` | Nilai seleksi per mata uji (fleksibel) |
| `ppdb_uniform_orders` | Data seragam (1-ke-1 ke applicant) |
| `ppdb_reregistrations` | Daftar ulang + FK ke `students` (siswa resmi) |

---

## 3. File yang Dibuat

### Migrations (`database/migrations/`)
- `2026_07_15_000001_create_ppdb_waves_table.php`
- `2026_07_15_000002_create_ppdb_applicants_table.php`
- `2026_07_15_000003_create_ppdb_documents_table.php`
- `2026_07_15_000004_create_ppdb_payments_table.php`
- `2026_07_15_000005_create_ppdb_selection_scores_table.php`
- `2026_07_15_000006_create_ppdb_uniform_orders_table.php`
- `2026_07_15_000007_create_ppdb_reregistrations_table.php`

### PHP Enums (`app/Enums/`)
- `PpdbApplicantStatus.php` — 7 status dengan `label()` dan `badgeClass()`
- `PpdbDocumentStatus.php` — pending / valid / invalid
- `PpdbPaymentStatus.php` — pending / paid / failed / expired
- `PpdbReregistrationStatus.php` — pending / completed

### Models (`app/Models/`)
- `PpdbWave.php` — `hasFee()`, `remainingQuota()`
- `PpdbApplicant.php` — `generateRegistrationCode()`, `averageScore()`, `allDocumentsValid()`
- `PpdbDocument.php` — `$docTypes`, `docTypeLabel()`
- `PpdbPayment.php`
- `PpdbSelectionScore.php`
- `PpdbUniformOrder.php` — `description()` untuk rekap
- `PpdbReregistration.php`

### Controllers (`app/Http/Controllers/Ppdb/`)
- `PpdbPublicController.php` — portal publik (form, simpan, cek status)
- `PpdbAdminController.php` — dashboard panitia (verifikasi, seleksi, daftar ulang, rekap)

### Views (`resources/views/`)
**Publik (`ppdb/`):**
- `layout.blade.php` — layout portal PPDB
- `index.blade.php` — landing page
- `register.blade.php` — form pendaftaran + JS conditional seragam
- `success.blade.php` — halaman sukses + tampil kode
- `cek-status.blade.php` — form login ulang
- `status.blade.php` — halaman detail status pendaftar

**Admin (`admin/ppdb/`):**
- `index.blade.php` — daftar pendaftar + filter + statistik
- `show.blade.php` — detail + verifikasi dokumen + input nilai + daftar ulang
- `waves.blade.php` — kelola gelombang
- `_wave-form.blade.php` — partial form gelombang
- `uniform-recap.blade.php` — rekap kebutuhan seragam

### Seeder & Factory
- `database/seeders/PpdbSeeder.php` — 2 gelombang + 10 pendaftar dummy
- `database/factories/PpdbApplicantFactory.php`
- `database/factories/PpdbWaveFactory.php`

---

## 4. URL Penting

| URL | Fungsi | Akses |
|---|---|---|
| `/ppdb` | Portal PPDB (landing page) | Publik |
| `/ppdb/daftar` | Form pendaftaran baru | Publik |
| `/ppdb/sukses` | Halaman sukses + tampil kode | Publik (via session) |
| `/ppdb/cek-status` | Login ulang cek status | Publik |
| `/ppdb/status/{kode}` | Detail status pendaftar | Publik (via session) |
| `/admin/ppdb` | Dashboard panitia | Admin/Super Admin |
| `/admin/ppdb/gelombang` | Kelola gelombang | Admin/Super Admin |
| `/admin/ppdb/pendaftar/{id}` | Detail + aksi per pendaftar | Admin/Super Admin |
| `/admin/ppdb/rekap-seragam` | Rekap kebutuhan seragam | Admin/Super Admin |

---

## 5. Cara Test Manual

### Test Pendaftaran Baru:
1. Buka `http://localhost:8000/ppdb`
2. Klik "Daftar Sekarang"
3. Isi semua field → Pilih gender Perempuan → pastikan field kerudung/bawahan muncul
4. Upload semua dokumen → Submit
5. Catat kode yang tampil di halaman sukses

### Test Cek Status:
1. Buka `http://localhost:8000/ppdb/cek-status`
2. Masukkan kode + tanggal lahir → klik Lihat Status

### Test Verifikasi Dokumen (Admin):
1. Login sebagai Admin → buka `/admin/ppdb`
2. Klik Detail pada salah satu pendaftar
3. Di bagian Verifikasi Dokumen → klik Valid atau Tolak (dengan catatan)

### Test Daftar Ulang:
1. Set status pendaftar menjadi "Lolos Seleksi" via Override Status
2. Scroll ke bawah → isi form Daftar Ulang → klik Proses
3. Cek tabel `students` dan `users` — record baru harus muncul

---

## 6. Keputusan Arsitektur

- **Login ulang**: Kode Pendaftaran + Tanggal Lahir (tanpa OTP) — sederhana, tidak butuh infrastruktur email
- **Status**: PHP Enum class (bukan MySQL ENUM) — bisa tambah status baru tanpa migration
- **Dokumen**: Disimpan di `storage/public/ppdb/dokumen/{kode}/`
- **Password default siswa baru**: format tanggal lahir `ddmmyyyy` (harus ganti sendiri setelah login pertama)
- **Seragam conditional**: JavaScript vanilla sederhana, tidak butuh library

---

## 7. Yang Perlu Ditambah di Masa Depan

- [ ] Upload ulang dokumen oleh pendaftar jika status `need_revision`
- [ ] Integrasi pembayaran (Midtrans/Xendit) untuk gelombang berbayar
- [ ] Notifikasi email/WhatsApp saat status berubah
- [ ] Export Excel daftar pendaftar
- [ ] Auto-seleksi berdasarkan nilai (saat ini masih manual override)
