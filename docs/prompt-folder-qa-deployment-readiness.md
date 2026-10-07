# Prompt: Susun Folder QA & Deployment Readiness — project Paskola Laravel

Copy-paste ke Claude Code/Cursor **di dalam project Laravel Paskola**. Ini bukan prompt bangun modul baru — ini menyusun dokumentasi testing & checklist sebelum launching, berdasarkan modul-modul yang sudah dibangun.

---

## PROMPT

Kamu adalah senior QA engineer yang juga sabar membimbing pemula (ingat: saya masih belajar, jelaskan tiap istilah QA yang mungkin asing buat saya, jangan berasumsi saya sudah paham).

Tugasmu: susun folder `qa/` di root project, berisi checklist testing dan dokumen kesiapan deployment, berdasarkan seluruh modul yang sudah dibangun di project ini.

### -1. Buat `qa/RULES.md` Dulu, Sebelum Apa Pun Lain (WAJIB, baca dan patuhi ini sepanjang sesi)

Buat file ini duluan, isinya persis aturan di bawah, lalu **patuhi aturan ini di SELURUH sisa sesi** (bukan cuma saat membuat file ini):

```markdown
# Aturan Kerja — Folder QA

Aturan ini berlaku untuk siapa pun (manusia atau AI) yang bekerja di folder `qa/`.

## 1. QA bersifat READ-ONLY terhadap kode aplikasi
- DILARANG mengubah, menambah, atau menghapus file apa pun di luar folder `qa/` — ini termasuk `app/`, `routes/`, `resources/`, `database/migrations/`, `config/`, `.env`, dan file manapun yang bukan bagian dari dokumentasi QA
- Kalau menemukan bug atau masalah performa (termasuk N+1), CATAT di checklist, JANGAN diperbaiki langsung di sesi ini
- Perbaikan kode adalah pekerjaan terpisah, dikerjakan lewat prompt/sesi lain yang secara eksplisit ditujukan untuk itu

## 2. Tidak ada perintah yang mengubah data
- DILARANG menjalankan `php artisan migrate:fresh`, `migrate:rollback`, `db:wipe`, atau perintah lain yang menghapus/mengubah struktur maupun isi database yang sedang dipakai untuk development, KECUALI secara eksplisit diminta dan dikonfirmasi ulang oleh saya di pesan itu juga
- Kalau QA butuh mencoba sesuatu yang berisiko mengubah data (misal untuk mengetes skenario tertentu), gunakan database/environment terpisah khusus testing, atau minta saya konfirmasi dulu sebelum menjalankan apa pun yang berisiko

## 3. Tidak ada commit di luar folder qa/
- Kalau memakai git, commit yang dibuat selama sesi QA hanya boleh berisi perubahan di dalam folder `qa/`
- Kalau secara tidak sengaja ada file lain yang berubah (misal karena menjalankan sebuah command), JANGAN di-commit — laporkan ke saya dulu apa yang berubah dan kenapa

## 4. Kalau ragu, berhenti dan tanya
- Kalau suatu langkah di prompt tampak membutuhkan perubahan kode aplikasi untuk bisa dilanjutkan (bukan cuma dokumentasi), STOP, jangan dikerjakan, jelaskan ke saya situasinya dan tunggu instruksi
```

### 0. Langkah Pertama: Audit Modul yang Sudah Ada (WAJIB sebelum menyusun apa pun)
Baca semua file di `docs/` (dokumentasi tiap modul yang sudah kita buat sepanjang proses build), dan cek struktur folder `app/`, `routes/`, serta migration yang ada untuk memastikan kamu tahu persis modul apa saja yang sudah jadi. Tampilkan daftar modul yang kamu temukan ke saya sebelum lanjut, supaya saya bisa koreksi kalau ada yang terlewat atau salah baca.

### 1. Struktur Folder yang Dibuat

```
qa/
├── RULES.md
├── README.md
├── checklists/
│   ├── 01-auth-dan-role.md
│   ├── 02-data-dasar.md
│   ├── 03-lms.md
│   ├── 04-kalender-akademik.md
│   ├── 05-jadwal-pelajaran.md
│   ├── 06-nilai-rapor.md
│   ├── 07-ppdb.md
│   ├── 08-keuangan-sekolah.md
│   ├── 09-ujian-online.md
│   ├── 00-performance-n-plus-1.md
│   └── (tambahkan file lain kalau ada modul yang belum tercantum di atas)
├── role-access-matrix.md
└── deployment-readiness/
    ├── checklist.md
    ├── environment-checklist.md
    └── backup-rollback-plan.md
```

### 2. Isi Tiap File Checklist Modul (`qa/checklists/*.md`)

Untuk SETIAP modul, buat checklist dengan format berikut. **Ambil skenario test dari bagian "Batasan Penting" dan "Cara Kerja" di setiap prompt pembangunan modul yang sudah kita jalankan sebelumnya** (kamu bisa menyimpulkannya dari isi `docs/<nama-modul>.md` dan dari kode yang sudah ada) — jangan mengarang skenario baru yang tidak relevan dengan modul tersebut:

```markdown
# Checklist QA — [Nama Modul]

## Status: [ ] Belum diuji / [ ] Sedang diuji / [ ] Lulus / [ ] Ada bug

## Skenario Fungsional
- [ ] Skenario 1: ...
- [ ] Skenario 2: ...

## Skenario Batasan Akses (Role)
- [ ] [Role A] tidak bisa akses data milik [Role B]
- [ ] ...

## Skenario Edge Case
- [ ] ...

## Bug Ditemukan (isi saat testing)
| Tanggal | Deskripsi Bug | Tingkat Keparahan | Status |
|---|---|---|---|
| | | | |
```

Khusus modul-modul berikut, WAJIB sertakan skenario ini (karena sudah eksplisit kita sepakati sebelumnya saat merancang modulnya):
- **Jadwal Pelajaran**: skenario bentrok guru/kelas/ruang harus ditolak sistem
- **Kalender Akademik**: hari libur harus otomatis meniadakan tampilan jadwal
- **Nilai & Rapor**: guru mapel hanya bisa pegang mapel & kelasnya sendiri; wali kelas tidak bisa ubah nilai langsung; siswa hanya lihat rapor sendiri yang sudah published
- **PPDB**: field seragam conditional sesuai gender; riwayat `ppdb_payments` tersambung ke `student_id` setelah siswa resmi
- **Keuangan Sekolah**: riwayat keuangan gabungan (PPDB + tagihan reguler) tampil benar per siswa
- **Ujian Online**: kode ujian milik siswa lain ditolak; urutan soal tidak berubah saat refresh; jawaban ditolak setelah waktu habis; `is_correct` tidak pernah terkirim ke browser siswa; guru tidak bisa akses ujian guru lain

### 2b. `qa/checklists/00-performance-n-plus-1.md` — Pengujian Query N+1

Ini checklist tersendiri, terpisah dari checklist fungsional per modul, karena sifatnya teknis dan butuh tool khusus.

**Langkah yang perlu dikerjakan:**
1. Install `barryvdh/laravel-debugbar` sebagai dev dependency (`composer require barryvdh/laravel-debugbar --dev`), jelaskan ke saya cara mengaktifkan/menonaktifkannya karena ini HANYA untuk development, harus mati total di production
2. Buka satu per satu halaman yang berpotensi N+1, catat jumlah query yang muncul di Debugbar untuk masing-masing, dengan kondisi data seeder yang representatif (minimal 20-30 baris data, bukan 2-3 baris, supaya N+1 kelihatan bedanya):
   - Dashboard Monitoring (semua role)
   - Daftar siswa per kelas (Administrasi Data Dasar)
   - Halaman Nilai & Rapor (daftar nilai per kelas, dan halaman verifikasi wali kelas)
   - Grid Jadwal Pelajaran (guru dan siswa)
   - Riwayat keuangan gabungan per siswa (PPDB + Keuangan Sekolah)
   - Daftar peserta & hasil Ujian Online
   - Halaman manapun lain yang menampilkan list dengan data relasi (misal nama terkait dari tabel lain)
3. Untuk setiap halaman yang query-nya tampak tidak wajar (jumlah query ikut bertambah sebanding dengan jumlah baris data yang ditampilkan — ini ciri khas N+1), **JANGAN langsung diperbaiki di sesi ini** (lihat `qa/RULES.md` — QA bersifat read-only terhadap kode aplikasi). Cukup catat lokasinya (nama file, baris kira-kira, query Eloquent yang jadi penyebab) supaya bisa diperbaiki lewat sesi/prompt terpisah yang memang ditujukan untuk perbaikan kode
4. Catat hasil temuan dalam format tabel:

```markdown
| Halaman | Jumlah Query | Status | File & Query Penyebab (kalau N+1) |
|---|---|---|---|
| Dashboard Admin | | [ ] Wajar / [ ] N+1 ditemukan | |
```

5. Target wajar: jumlah query per halaman **tidak boleh bertambah mengikuti jumlah baris data**. Idealnya di bawah 15-20 query untuk halaman biasa, independen dari apakah datanya 10 baris atau 500 baris. Halaman yang tidak memenuhi ini dicatat sebagai temuan, bukan langsung diperbaiki
6. Pastikan Laravel Debugbar **tidak aktif di environment production** (cek `.env` production, package ini seharusnya cuma jalan kalau `APP_DEBUG=true` dan environment development)
7. Setelah checklist ini selesai, daftar temuan N+1 (kalau ada) dirangkum di bagian akhir `qa/README.md` sebagai pekerjaan lanjutan yang masih harus dikerjakan di sesi terpisah

### 3. `qa/role-access-matrix.md`

Buat 1 tabel besar berisi SEMUA kombinasi role x modul yang ada di sistem, dengan kolom: Role, Modul, Aksi yang Diizinkan, Aksi yang DILARANG, Sudah Diuji (Y/N). Susun ini dengan membaca seluruh Policy/Gate yang sudah dibuat di tiap modul — jangan menebak, baca kode aslinya.

### 4. `qa/deployment-readiness/checklist.md`

Buat checklist 8 tahap berikut, tiap tahap punya sub-checklist konkret yang disesuaikan dengan kondisi project ini (bukan generic):

1. **Uji fungsional tiap modul** — tautkan ke file-file di `qa/checklists/`, termasuk `00-performance-n-plus-1.md` (jumlah query tiap halaman sudah dicek dan tidak ada N+1 yang belum diperbaiki)
2. **Uji batasan akses (role & policy)** — tautkan ke `qa/role-access-matrix.md`
3. **Amankan data pribadi siswa** — checklist konkret: HTTPS aktif, `APP_DEBUG=false` di production, password di-hash (Laravel default bcrypt, cek tidak ada yang disimpan plain text), cek tidak ada data sensitif (NISN, nilai, data ortu) yang muncul di log Laravel (`storage/logs`) atau response API yang tidak perlu
4. **Siapkan infrastruktur produksi** — checklist: domain siap, SSL terpasang, `.env` production terpisah dari development, `APP_ENV=production`, database production terpisah dari database development di Laragon
5. **Migrasi & validasi data riil** — checklist: data seeder/dummy sudah dihapus dari database production, data riil sekolah (siswa, guru, kelas) sudah diinput dan diverifikasi sampel beberapa baris
6. **Backup & rencana pemulihan** — tautkan ke `qa/deployment-readiness/backup-rollback-plan.md`
7. **Latih pengguna sebelum go-live** — checklist: materi pelatihan per role sudah disiapkan, jadwal pelatihan ditentukan
8. **Soft launch / uji coba terbatas** — checklist: modul/kelas mana yang jadi pilot sudah ditentukan, durasi paralel dengan proses manual ditentukan, kriteria "siap diperluas" ditentukan

### 5. `qa/deployment-readiness/environment-checklist.md`
Buat perbandingan konfigurasi `.env` antara development (Laragon) dan production, kolom: Variabel, Nilai Development, Nilai Production (placeholder `[ISI]` untuk yang spesifik ke hosting pilihan saya), Catatan. Sertakan minimal: `APP_ENV`, `APP_DEBUG`, `APP_URL`, `DB_*`, `MAIL_*` (kalau modul manapun sudah pakai notifikasi email), session/cache driver.

### 6. `qa/deployment-readiness/backup-rollback-plan.md`
Buat dokumen rencana backup (apa yang di-backup, seberapa sering, disimpan di mana — beri beberapa opsi dengan penjelasan singkat untuk pemula) dan rencana rollback (langkah konkret kalau deployment baru bermasalah: cara kembali ke versi sebelumnya, siapa yang harus dihubungi, target waktu pemulihan).

### 7. `qa/README.md`
Ringkasan cara pakai folder ini: urutan membaca/mengisi dokumen, siapa yang sebaiknya mengisi checklist (kamu sendiri, atau dibagi ke orang lain kalau ada tim), dan kapan folder ini dianggap "lulus" untuk lanjut ke langkah go-live.

### Cara Kerja
1. Audit dulu (poin 0), tampilkan daftar modul yang ditemukan, tunggu konfirmasi saya
2. Buat seluruh struktur folder dan file sekaligus (ini dokumentasi, bukan kode aplikasi, jadi tidak perlu dipecah per sub-bagian dengan konfirmasi berulang seperti modul-modul sebelumnya) — tapi tetap jelaskan ke saya ringkasan isi tiap file setelah selesai
3. Untuk setiap file checklist modul, sebutkan secara eksplisit dari mana skenario testing itu diambil (misal: "diambil dari docs/jadwal-pelajaran.md bagian validasi bentrok") — supaya saya bisa percaya checklist ini bukan dikarang asal, tapi memang mencerminkan apa yang sudah kita rancang dan bangun
4. Setelah semua file dibuat, berikan saya ringkasan: ada berapa modul yang punya checklist, berapa total skenario test yang tercatat, dan modul mana (kalau ada) yang menurutmu datanya kurang lengkap di `docs/` sehingga checklist-nya masih dangkal dan perlu saya lengkapi manual

### Batasan Penting
- **Patuhi `qa/RULES.md` yang kamu buat sendiri di poin -1, sepanjang sesi ini, tanpa pengecualian**
- **Ini murni dokumentasi**, jangan menulis kode aplikasi baru, jangan mengubah migration/model/controller yang sudah ada
- Jangan mengarang skenario test yang tidak berdasar dari modul yang benar-benar sudah dibangun — kalau ragu suatu modul ada fitur tertentu, cek dulu ke kode asli, jangan asumsi
- Checklist harus bisa langsung dipakai (actionable), bukan generic placeholder seperti "test semua fitur" — setiap baris harus spesifik ke modul ini, bukan nasihat umum QA
- Kalau ada bagian yang ambigu (misal: belum tahu sudah ada modul manajemen menu/belum, atau provider email belum ditentukan), tanya saya dulu — jangan berimprovisasi sendiri

---

**Mulai dari poin 0 (audit modul), tampilkan hasilnya sebelum lanjut membuat file.**
