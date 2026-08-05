# Prompt: Update Modul PPDB (Patch) — Sambungkan ppdb_payments ke Data Siswa

Copy-paste ke Claude Code/Cursor **di dalam project Laravel Paskola**. Ini BUKAN prompt bangun modul baru — ini patch kecil untuk modul PPDB yang SUDAH jadi, dijalankan SEBELUM modul Keuangan Sekolah dibangun.

---

## PROMPT

Modul PPDB sudah selesai dan berjalan. Sebelum kita bangun modul Keuangan Sekolah, saya perlu 1 perubahan kecil di modul PPDB yang sudah ada:

### Perubahan yang Diperlukan

1. **Buat migration baru** (jangan edit migration lama yang sudah jalan) untuk menambah kolom `student_id` (FK ke tabel `students`, nullable) di tabel `ppdb_payments` yang sudah ada

2. **Tambahkan logic baru** di bagian proses "daftar ulang selesai & data siswa resmi dibuat" (cari kode yang sudah ada untuk ini): setelah data siswa baru berhasil dibuat, **update semua baris `ppdb_payments`** milik applicant tersebut (`where applicant_id = ...`) dengan mengisi kolom `student_id` yang baru dibuat

3. **Jangan ubah data yang sudah ada** — kolom baru ini nullable, jadi data pembayaran PPDB yang sudah tercatat sebelumnya tetap aman, cuma applicant yang BARU lolos+daftar ulang setelah patch ini yang akan otomatis terisi `student_id`-nya

### Verifikasi Setelah Patch
1. Cek migration baru berhasil jalan (`php artisan migrate`) tanpa merusak data yang sudah ada — tampilkan hasilnya ke saya
2. Buat 1 skenario test: proses 1 applicant dummy dari lolos sampai daftar ulang selesai, pastikan `student_id` di `ppdb_payments` miliknya benar-benar terisi setelah itu
3. Jelaskan ke saya bagian kode mana yang diubah (nama file, baris kira-kira), supaya saya paham apa yang berubah dari kode yang sudah ada

Setelah patch ini dikonfirmasi berhasil, baru kita lanjut ke pembangunan modul Keuangan Sekolah yang baru.
