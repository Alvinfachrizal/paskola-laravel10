# Rencana Backup & Rollback — Paskola SIMS

> Dokumen ini menjelaskan apa yang perlu di-backup, seberapa sering, disimpan di mana, dan apa yang harus dilakukan jika terjadi masalah setelah deployment.
>
> Sengaja ditulis sesederhana mungkin agar bisa dipahami tanpa latar belakang teknis yang dalam.

---

## Bagian 1: Rencana Backup

### Apa yang Perlu Di-backup?

Ada **dua hal** yang wajib di-backup secara teratur:

| Yang Di-backup | Kenapa Penting | Lokasi di Server |
|---|---|---|
| **Database PostgreSQL** | Berisi semua data: siswa, nilai, tagihan, hasil ujian, data PPDB. Ini yang paling penting. | Server database |
| **Folder File Upload** | Berisi dokumen yang diupload: bukti bayar, dokumen PPDB, file materi LMS | `storage/app/public/` |

> **Yang tidak perlu di-backup:** Kode aplikasi (folder `app/`, `resources/`, dll) karena kode ada di Git dan bisa didapatkan kembali kapan saja. Yang tidak bisa dikembalikan hanya **data**.

---

### Seberapa Sering Backup?

| Jenis Backup | Frekuensi | Waktu (Rekomendasi) |
|---|---|---|
| **Backup harian (database)** | Setiap hari | Pukul 01.00 dini hari (traffic sekolah paling sepi) |
| **Backup mingguan (database + file)** | Setiap Minggu | Sabtu malam / Minggu dini hari |
| **Backup sebelum update besar** | Setiap kali ada perubahan kode besar | Manual, sebelum deploy |

---

### Disimpan di Mana?

Gunakan **minimal 2 lokasi berbeda** agar aman jika satu lokasi bermasalah:

**Opsi A: Hosting + Google Drive/Dropbox (Paling Mudah untuk Pemula)**
- Backup otomatis disimpan di server hosting (cek apakah hosting menyediakan fitur backup otomatis di control panel)
- Unduh salinan backup ke Google Drive atau Dropbox pribadi setiap minggu secara manual

**Opsi B: Scheduled Script di Server (Lebih Andal)**
- Buat cron job di server yang menjalankan perintah backup setiap hari:
  ```bash
  # Backup database PostgreSQL
  pg_dump -U [username] [nama_database] > /path/backup/paskola_$(date +%Y%m%d).sql
  
  # Kompresi agar ukurannya kecil
  gzip /path/backup/paskola_$(date +%Y%m%d).sql
  ```
- Hasil backup disimpan di folder `/backup/` di server, lalu di-sync ke storage cloud (Backblaze B2, Wasabi, atau S3 murah)

**Opsi C: Fitur Backup Hosting (Termudah)**
- Banyak hosting (seperti Niagahoster, Rumahweb, dll) menyediakan fitur "Backup" di control panel cPanel
- Aktifkan backup otomatis dari control panel, pilih frekuensi harian
- Pastikan backup juga mencakup database, bukan hanya file

---

### Berapa Lama Backup Disimpan?

| Jenis | Rekomendasi Penyimpanan |
|---|---|
| Backup harian | Simpan 7 hari terakhir (lama lebih dari itu bisa dihapus untuk hemat storage) |
| Backup mingguan | Simpan 4 minggu terakhir |
| Backup bulanan | Simpan 3 bulan terakhir |
| Backup sebelum update | Simpan minimal sampai versi baru terbukti stabil (2 minggu) |

---

### Cara Restore Database dari Backup

Jika terjadi masalah dan perlu mengembalikan database dari file backup (format `.sql` atau `.sql.gz`):

```bash
# Jika file .sql.gz, ekstrak dulu
gunzip paskola_20261001.sql.gz

# Restore ke database (HATI-HATI: ini akan menimpa data yang ada!)
psql -U [username] [nama_database] < paskola_20261001.sql
```

> ⚠️ **Peringatan:** Restore database akan **menghapus semua data terkini** dan menggantinya dengan data dari waktu backup tersebut. Pastikan ini memang yang Anda inginkan sebelum menjalankan perintah ini.

---

## Bagian 2: Rencana Rollback (Kembali ke Versi Sebelumnya)

Rollback adalah tindakan yang dilakukan jika update atau deployment baru menyebabkan masalah serius.

### Kapan Perlu Rollback?

- Ada bug kritis yang muncul setelah deployment (data tidak tersimpan, halaman error 500 untuk semua pengguna, data tampil salah)
- Performa tiba-tiba sangat lambat setelah update
- Fitur penting yang sebelumnya berfungsi sekarang tidak bisa digunakan

### Target Waktu Pemulihan

| Skenario | Target Waktu |
|---|---|
| Bug minor (tidak memblokir penggunaan) | Diperbaiki dalam 1 hari kerja |
| Bug yang memblokir 1 fitur | Rollback atau perbaikan dalam 4 jam |
| Bug kritis yang memblokir seluruh sistem | Rollback dalam 1 jam |

### Siapa yang Dihubungi?

Buat daftar kontak darurat sebelum go-live:

| Peran | Nama | Kontak |
|---|---|---|
| Pengembang Utama | [ISI] | [ISI] |
| Admin Server / Hosting | [ISI] | [ISI nomor HP / email] |
| Admin Sekolah yang Bisa Dikontak | [ISI] | [ISI] |

---

### Langkah Rollback Kode (via Git)

Karena kode menggunakan Git, rollback kode ke versi sebelumnya bisa dilakukan dengan:

```bash
# 1. Lihat daftar commit yang tersedia (cari commit yang terakhir stabil)
git log --oneline -10

# 2. Rollback ke commit tertentu (ganti COMMIT_HASH dengan hash commit yang stabil)
git checkout COMMIT_HASH

# Atau, jika ingin rollback ke versi sebelumnya secara permanen:
git revert HEAD

# 3. Setelah rollback kode, jalankan ulang:
php artisan config:cache
php artisan route:cache
```

### Langkah Rollback Database

Jika update juga mengubah struktur database (migration baru) dan rollback perlu dilakukan:

1. **Restore dari backup** yang dibuat sebelum deployment (lihat Bagian 1 — cara restore)
2. Jika tidak mau restore penuh, bisa rollback migration:
   ```bash
   # HATI-HATI: ini menghapus tabel yang dibuat migration terakhir
   php artisan migrate:rollback
   ```
   > ⚠️ `migrate:rollback` berisiko kehilangan data di tabel yang di-rollback. Selalu backup dulu sebelum menjalankan ini.

---

## Checklist Sebelum Setiap Deployment

Lakukan ini SEBELUM setiap update besar ke production:

- [ ] Backup database production terbaru sudah diambil dan tersimpan
- [ ] Backup folder file upload terbaru sudah diambil
- [ ] Catat hash commit Git yang sedang berjalan di production (`git log --oneline -1`)
- [ ] Sudah ada rencana rollback jika terjadi masalah
- [ ] Deployment dijadwalkan di waktu traffic rendah (dini hari atau akhir pekan)
- [ ] Ada tim/orang yang standby untuk memantau 1-2 jam setelah deployment
