# Checklist QA — Keuangan Sekolah

> Sumber skenario: `docs/prompt-modul-keuangan-sekolah.md`, kode `app/Http/Controllers/Finance/`, migration `bill_types`, `student_bills`, `payments`, `StudentFinanceService.php`.

## Status: [ ] Belum diuji / [ ] Sedang diuji / [ ] Lulus / [ ] Ada bug

---

## Skenario Fungsional — Master Jenis Tagihan

- [ ] **CRUD Jenis Tagihan** — Admin tambah jenis tagihan baru (misal: "SPP Bulan Oktober") → tersimpan di `bill_types`
- [ ] **Jenis tagihan tersedia untuk di-assign ke siswa**

## Skenario Fungsional — Tagihan Siswa

- [ ] **Tagihan awal otomatis saat siswa baru dibuat** — Setelah siswa baru ditambahkan (via Admin atau daftar ulang PPDB), tagihan awal (Uang Gedung, Seragam, dll) otomatis dibuat oleh `StudentObserver`
- [ ] **Generate tagihan SPP bulanan** — Jalankan Artisan Command tagihan berulang → tagihan SPP bulan baru terbuat untuk semua siswa aktif
- [ ] **Siswa bisa lihat tagihan** — Login sebagai Siswa → buka halaman keuangan → semua tagihan tampil dengan status (belum bayar / lunas)
- [ ] **Ortu bisa lihat tagihan anak** — Login sebagai Ortu → buka halaman keuangan → tagihan anak terkait tampil

## Skenario Fungsional — Pembayaran

- [ ] **Siswa/Ortu upload bukti bayar** — Pilih tagihan yang belum lunas, upload file bukti pembayaran → status tagihan berubah ke "Menunggu Verifikasi"
- [ ] **Admin verifikasi pembayaran** — Admin membuka daftar pembayaran yang menunggu, klik "Verifikasi" → status berubah ke "Lunas"
- [ ] **Admin tolak pembayaran** — Admin klik "Tolak" dengan keterangan → status kembali ke "Belum Bayar" agar siswa bisa upload ulang
- [ ] **Dashboard rekap keuangan Admin** — Admin buka dashboard keuangan → tampil total tagihan, total terkumpul, dan grafik ringkasan

## Skenario Fungsional — Riwayat Gabungan

- [ ] **Riwayat PPDB + tagihan reguler tampil bersama** — Buka halaman riwayat keuangan siswa yang sudah pernah daftar PPDB → pembayaran pendaftaran dan tagihan sekolah tampil dalam satu timeline yang berurutan (`StudentFinanceService::getFullHistory()`)

---

## Skenario Batasan Akses (Role)

- [ ] **Siswa tidak bisa verifikasi pembayaran sendiri** — Login sebagai Siswa, coba akses endpoint verifikasi → 403
- [ ] **Guru tidak punya akses ke modul keuangan** — Login sebagai Guru, akses `/finance/` → 403 atau redirect
- [ ] **Siswa A tidak bisa lihat tagihan Siswa B** — Akses URL tagihan siswa lain → 403 atau redirect ke data sendiri

---

## Skenario Edge Case (WAJIB)

- [ ] **Riwayat keuangan gabungan (PPDB + Finance) tampil benar per siswa** — Untuk siswa yang masuk via PPDB, cek `ppdb_payments.student_id` terisi → riwayat PPDB muncul di halaman keuangan
- [ ] **Tagihan dengan status "Lunas" tidak bisa diupload ulang** — Tagihan yang sudah terverifikasi tidak memiliki tombol "Upload Bukti"
- [ ] **Generate tagihan tidak duplikat** — Jalankan Artisan Command SPP dua kali di bulan yang sama → tagihan tidak muncul dua kali untuk siswa yang sama

---

## Bug Ditemukan (isi saat testing)

| Tanggal | Deskripsi Bug | Tingkat Keparahan | Status |
|---|---|---|---|
| | | | |
