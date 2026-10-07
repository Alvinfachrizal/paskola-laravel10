# Checklist QA — PPDB Online

> Sumber skenario: `docs/ppdb.md` (bagian "Cara Test Manual" dan alur sistem), kode `app/Http/Controllers/Ppdb/PpdbPublicController.php`, `PpdbAdminController.php`, migration `ppdb_*`.

## Status: [ ] Belum diuji / [ ] Sedang diuji / [ ] Lulus / [ ] Ada bug

---

## Skenario Fungsional — Portal Publik (Calon Siswa)

- [ ] **Landing page PPDB tampil** — Buka `/ppdb` tanpa login → halaman informasi dan daftar gelombang tampil
- [ ] **Form pendaftaran diisi lengkap** — Isi semua field wajib → klik Submit → dapat kode pendaftaran (format: `PPDB-2026-XXXXX`)
- [ ] **Field seragam conditional** — Pilih **gender Perempuan** → field kerudung dan rok/bawahan khusus perempuan muncul secara otomatis (JavaScript). Pilih **Laki-laki** → field tersebut tersembunyi
- [ ] **Upload dokumen** — Upload semua dokumen yang dipersyaratkan (Kartu Keluarga, Akta Lahir, dll) → file tersimpan di `storage/public/ppdb/dokumen/{kode}/`
- [ ] **Login ulang cek status** — Buka `/ppdb/cek-status`, masukkan Kode Pendaftaran + Tanggal Lahir → bisa masuk ke halaman detail status
- [ ] **Upload ulang dokumen** — Jika status dokumen `need_revision`, pendaftar bisa upload ulang file pengganti

## Skenario Fungsional — Panel Panitia (Admin)

- [ ] **Melihat daftar pendaftar** — Admin buka `/admin/ppdb` → daftar semua pendaftar tampil dengan filter status
- [ ] **Verifikasi dokumen satu per satu** — Admin klik tombol "Valid" atau "Tolak" (dengan catatan) per item dokumen
- [ ] **Input nilai seleksi** — Admin input nilai Matematika, Bahasa Indonesia, dll → rata-rata dihitung otomatis
- [ ] **Override status manual** — Admin bisa set status ke "Lolos Seleksi" atau "Tidak Lolos" secara manual
- [ ] **Proses Daftar Ulang** — Admin proses daftar ulang untuk pendaftar dengan status "Lolos Seleksi" → otomatis membuat akun `users` baru + record `students` baru
- [ ] **Rekap kebutuhan seragam** — Admin buka `/admin/ppdb/rekap-seragam` → tabel rekap jumlah dan ukuran seragam per gender tampil (bisa dicetak)
- [ ] **Manajemen Gelombang** — Admin tambah gelombang baru dengan kuota, biaya, dan jadwal tertentu

---

## Skenario Batasan Akses (Role)

- [ ] **Guru tidak bisa akses panel panitia PPDB** — Login sebagai Guru, akses `/admin/ppdb` → 403
- [ ] **Siswa aktif tidak bisa mendaftar PPDB** — (Jika ada validasi) siswa aktif tidak bisa mengakses form PPDB sebagai calon baru
- [ ] **Status pendaftar tidak bisa mundur sembarangan** — Pastikan alur status berjalan satu arah yang logis (misal: dari "Terverifikasi" tidak bisa kembali ke "Pending" tanpa alasan)

---

## Skenario Edge Case (WAJIB)

- [ ] **Field seragam gender Perempuan wajib terisi** — Submit form dengan gender Perempuan tapi field kerudung dikosongkan → validasi backend menolak
- [ ] **Riwayat `ppdb_payments` tersambung ke `student_id`** — Setelah daftar ulang selesai, record di `ppdb_payments` memiliki `student_id` yang menunjuk ke record siswa yang baru dibuat (patch `add_student_id_to_ppdb_payments_table.php`)
- [ ] **Gelombang berbayar menampilkan informasi biaya** — Gelombang dengan `fee > 0` menampilkan informasi biaya pendaftaran di landing page
- [ ] **Kode pendaftaran yang salah ditolak** — Login ulang dengan kode yang tidak ada di database → pesan error yang jelas (bukan error 500)
- [ ] **Kuota gelombang habis** — Jika `sisa_kuota = 0`, form pendaftaran menampilkan "Kuota Penuh" dan tidak bisa disubmit

---

## Bug Ditemukan (isi saat testing)

| Tanggal | Deskripsi Bug | Tingkat Keparahan | Status |
|---|---|---|---|
| | | | |
