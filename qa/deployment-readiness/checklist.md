# Deployment Readiness Checklist — Paskola SIMS

> Dokumen ini adalah panduan 8 tahap yang harus SEMUA di-centang sebelum sistem dinyatakan siap untuk digunakan oleh sekolah secara nyata (go-live). Jangan skip tahap apapun.

---

## Tahap 1: Uji Fungsional Semua Modul

Pastikan semua checklist modul di bawah ini sudah berstatus **"Lulus"** sebelum lanjut ke tahap berikutnya.

- [ ] [`qa/checklists/01-auth-dan-role.md`](../checklists/01-auth-dan-role.md) — Auth & RBAC → **Lulus**
- [ ] [`qa/checklists/02-data-dasar.md`](../checklists/02-data-dasar.md) — Data Master → **Lulus**
- [ ] [`qa/checklists/03-lms.md`](../checklists/03-lms.md) — LMS (Materi & Tugas) → **Lulus**
- [ ] [`qa/checklists/04-kalender-akademik.md`](../checklists/04-kalender-akademik.md) — Kalender Akademik → **Lulus**
- [ ] [`qa/checklists/05-jadwal-pelajaran.md`](../checklists/05-jadwal-pelajaran.md) — Jadwal Pelajaran → **Lulus**
- [ ] [`qa/checklists/06-nilai-rapor.md`](../checklists/06-nilai-rapor.md) — Nilai & Rapor → **Lulus**
- [ ] [`qa/checklists/07-ppdb.md`](../checklists/07-ppdb.md) — PPDB Online → **Lulus**
- [ ] [`qa/checklists/08-keuangan-sekolah.md`](../checklists/08-keuangan-sekolah.md) — Keuangan → **Lulus**
- [ ] [`qa/checklists/09-ujian-online.md`](../checklists/09-ujian-online.md) — Ujian Online → **Lulus**
- [ ] [`qa/checklists/00-performance-n-plus-1.md`](../checklists/00-performance-n-plus-1.md) — Performa (tidak ada N+1 yang belum diperbaiki) → **Lulus**

---

## Tahap 2: Uji Batasan Akses (Role & Policy)

- [ ] Semua baris di [`qa/role-access-matrix.md`](../role-access-matrix.md) sudah diuji (kolom "Sudah Diuji" semua = **Y**)
- [ ] Tidak ditemukan akses yang seharusnya diblokir tapi ternyata bisa dilakukan
- [ ] Tidak ditemukan akses yang seharusnya diizinkan tapi malah diblokir (false positive)

---

## Tahap 3: Amankan Data Pribadi Siswa

Data siswa (NISN, nilai, data orang tua, dokumen PPDB) bersifat sangat sensitif dan harus dilindungi.

- [ ] **HTTPS aktif** di production — semua traffic terenkripsi, tidak ada yang lewat HTTP biasa
- [ ] **`APP_DEBUG=false`** di file `.env` production — debug mode mati agar error detail (yang bisa bocor informasi sistem) tidak tampil ke pengguna akhir
- [ ] **Password di-hash** — cek tabel `users`, kolom `password` berisi hash bcrypt (dimulai dengan `$2y$`), bukan plain text
- [ ] **Tidak ada data sensitif di log** — cek `storage/logs/laravel.log` → tidak ada NISN, nilai, nama siswa, atau data pribadi lain yang tercatat di log
- [ ] **File dokumen PPDB tidak bisa diakses publik** — coba akses URL file dokumen PPDB langsung tanpa login → harus 403 atau 404
- [ ] **Nilai ujian (`is_correct`) tidak pernah tersedia di browser siswa** — sudah diverifikasi di `qa/checklists/09-ujian-online.md`

---

## Tahap 4: Siapkan Infrastruktur Production

- [ ] **Domain sudah siap** — nama domain sekolah sudah didaftarkan dan DNS sudah mengarah ke server
- [ ] **SSL Certificate terpasang** — sertifikat SSL valid (bisa gunakan Let's Encrypt gratis), tidak ada peringatan "Not Secure" di browser
- [ ] **`.env` production terpisah** — file `.env` di server production berbeda dari `.env` di komputer development (Laragon), terutama konfigurasi database
- [ ] **`APP_ENV=production`** di `.env` production
- [ ] **`APP_DEBUG=false`** di `.env` production (diulang dari Tahap 3 karena kritis)
- [ ] **`APP_URL`** di `.env` production sudah diubah ke URL domain yang benar (bukan `http://localhost:8000`)
- [ ] **Database production terpisah** dari database development — tidak menggunakan database Laragon yang sama

---

## Tahap 5: Migrasi & Validasi Data Riil

- [ ] **Jalankan migrasi di server production** — `php artisan migrate` berhasil tanpa error di database production
- [ ] **Data seeder/dummy sudah TIDAK ada** di database production — tabel tidak berisi siswa bernama "Siswa Demo" atau email berakhiran `@paskola.com`
- [ ] **Data riil sekolah diinput** — data guru, siswa, kelas, tahun ajaran, dan mata pelajaran sudah diinput dan siap digunakan
- [ ] **Sampel data diverifikasi** — cek beberapa data secara manual untuk memastikan tidak ada entri yang salah atau terduplikat

---

## Tahap 6: Backup & Rencana Pemulihan

- [ ] Rencana backup sudah dibuat dan dipahami → lihat [`qa/deployment-readiness/backup-rollback-plan.md`](./backup-rollback-plan.md)
- [ ] Backup pertama database production sudah dilakukan sebelum go-live
- [ ] Tim tahu cara melakukan restore dari backup jika terjadi masalah

---

## Tahap 7: Latih Pengguna Sebelum Go-Live

- [ ] **Materi pelatihan Admin** sudah disiapkan (cara input siswa baru, verifikasi PPDB, manage keuangan)
- [ ] **Materi pelatihan Guru** sudah disiapkan (cara input nilai, buat tugas LMS, buat ujian online)
- [ ] **Jadwal pelatihan** sudah ditetapkan sebelum tanggal go-live
- [ ] **Sesi pelatihan Admin & Tata Usaha** sudah dilaksanakan
- [ ] **Sesi pelatihan Guru** sudah dilaksanakan
- [ ] Ada kontak person (misal: admin sekolah atau pengembang) yang bisa dihubungi saat pengguna mengalami kesulitan

---

## Tahap 8: Soft Launch / Uji Coba Terbatas

- [ ] **Pilot modul/kelas ditentukan** — Misalnya: hanya modul Jadwal + Nilai, untuk Kelas X-A sebagai pilot awal
- [ ] **Durasi parallel run ditentukan** — Berapa lama sistem baru digunakan paralel dengan proses manual (misal: 1 bulan)
- [ ] **Kriteria "siap diperluas" ditentukan** — Misal: "Jika pilot 2 minggu berjalan tanpa bug kritis, modul diperluas ke seluruh kelas"
- [ ] **Mekanisme feedback pengguna ada** — Ada cara mudah bagi guru/siswa untuk melaporkan bug atau kesulitan (grup WhatsApp, form, dll)
- [ ] **Evaluasi pilot dilakukan** — Setelah masa pilot, ada sesi review untuk memutuskan apakah layak go-live penuh
