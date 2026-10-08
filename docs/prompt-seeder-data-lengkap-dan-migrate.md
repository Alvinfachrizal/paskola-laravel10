# Prompt: Isi Data Lengkap Semua Modul (Seeder) + Siapkan Migrate — project Paskola Laravel

Copy-paste ke Claude Code/Cursor/Antigravity **di dalam project Laravel Paskola**.

**Urutan pemakaian:** jalankan prompt ini **SEBELUM** prompt QA (`prompt-folder-qa-deployment-readiness.md`), karena pengujian QA (terutama cek N+1 dan skenario tiap role) butuh data yang cukup banyak dan beragam, bukan 2-3 baris data contoh.

**Beda dengan prompt QA:** prompt QA bersifat read-only terhadap kode aplikasi. Prompt ini **memang menulis kode** (seeder, dan kemungkinan perbaikan migrasi), jadi aturannya berbeda dan jangan dicampur dalam satu sesi dengan sesi QA.

---

## PROMPT

Kamu adalah senior Laravel engineer yang juga sabar membimbing pemula (ingat: saya masih belajar, jelaskan tiap istilah yang mungkin asing, jangan berasumsi saya sudah paham).

Tugasmu ada dua:
1. **Menyiapkan migrasi** supaya seluruh database bisa dibangun dari nol dengan satu perintah, tanpa error
2. **Membuat seeder lengkap** yang mengisi data demo yang realistis dan beragam untuk SEMUA modul yang sudah dibangun

### ATURAN KESELAMATAN (WAJIB, baca dulu sebelum menjalankan apa pun)

1. **Jangan pernah menjalankan `migrate:fresh`, `migrate:refresh`, `db:wipe`, atau `--seed` di database yang berisi data yang saya anggap berharga.** Perintah ini MENGHAPUS SEMUA DATA lalu membuatnya ulang
2. **Gunakan database terpisah khusus demo** (misal `paskola_demo`), bukan database development yang sedang saya pakai. Siapkan lewat file environment terpisah (`.env.demo`) dan jalankan dengan `--env=demo`, jelaskan ke saya cara membuat databasenya di Laragon (via HeidiSQL/menu Database)
3. **Sebelum menjalankan perintah apa pun yang menghapus data, tampilkan ke saya nama database yang akan dihapus** (ambil dari konfigurasi yang benar-benar aktif, bukan asumsi), lalu tunggu konfirmasi saya
4. **Semua seeder demo WAJIB menolak berjalan di environment production.** Di awal `DatabaseSeeder`, tambahkan pengecekan: kalau `app()->environment('production')`, hentikan dengan pesan error jelas. Ini mencegah akun demo berpassword sederhana masuk ke sistem sungguhan
5. **Jangan mengubah migration yang sudah pernah dijalankan** (yang sudah ada di tabel `migrations` pada database saya) dengan cara mengedit isinya, kecuali saya menyetujui secara eksplisit. Kalau butuh perubahan struktur, buat migration baru. Kalau harus mengedit migration lama untuk memperbaiki urutan/dependency supaya bisa jalan dari nol, jelaskan alasannya ke saya dan minta persetujuan dulu
6. **Jangan mengubah logic aplikasi** (controller, service, policy, view). Tugas ini hanya menyentuh `database/` dan dokumentasi

### 0. Langkah Pertama: Audit (WAJIB sebelum menulis apa pun)

1. Daftar SEMUA migration yang ada (urutan tanggalnya) dan semua model/tabel. Cocokkan dengan modul yang sudah kita bangun: Auth & role, Data Dasar (tahun ajaran, jurusan, kelas, mapel, siswa, guru, semester), LMS, Kalender Akademik, Jadwal Pelajaran, Nilai & Rapor, PPDB, Keuangan Sekolah, Ujian Online, dan Manajemen Menu (kalau sudah dibangun). Tampilkan daftar modul yang kamu temukan ke saya, dan beri tahu kalau ada yang tidak kamu temukan
2. Baca seeder & factory yang **sudah ada** dari proses build sebelumnya. Jangan menimpa atau menduplikasinya buta-butaan: laporkan apa yang sudah ada, dan usulkan apakah dipakai ulang, dirapikan, atau diganti
3. Cek struktur Data Dasar yang ada (apakah memakai jurusan atau tidak, jenjang sekolahnya apa) untuk menentukan bentuk data kelas yang realistis. **Kalau jenjang sekolah tidak jelas dari kode, tanya saya**, jangan menebak
4. Tampilkan hasil audit, tunggu konfirmasi saya

### 1. Menyiapkan Migrate

Setelah audit disetujui:

1. Pada database demo yang KOSONG, jalankan `migrate` dari nol dan tampilkan hasilnya. Perbaiki masalah yang muncul sesuai aturan keselamatan poin 5 (urutan foreign key, tabel yang dirujuk belum dibuat, dll)
2. Cek bahwa setiap migration punya method `down()` yang benar, lalu uji `migrate:rollback` penuh dan `migrate` ulang **hanya di database demo**
3. Cek konsistensi: setiap model punya tabel yang ada, nama tabel/kolom di migration cocok dengan yang dipakai model dan relasinya
4. **Laporkan, jangan langsung tambahkan,** usulan index tambahan (misal kolom `status`, `due_date`, `start_at`, foreign key yang sering di-filter) beserta alasannya. Penambahan index dibuat sebagai migration baru HANYA setelah saya setuju
5. Buat ringkasan: berapa tabel, berapa migration, dan apakah `migrate:fresh` dari nol berhasil bersih

### 2. Aturan Umum Data Seeder

- **Bahasa & format Indonesia**: Faker locale `id_ID`, nama orang Indonesia, alamat Indonesia, nomor telepon format Indonesia, NISN 10 digit
- **Deterministik**: set seed Faker tetap (misal `fake()->seed(2026)`) supaya hasil seeding sama setiap kali dijalankan dan bisa dibandingkan antar sesi
- **Realistis & berkorelasi**: data antar modul harus nyambung (siswa yang punya nilai harus ada di kelas yang punya jadwal dan guru yang mengajar mapelnya), bukan acak tanpa relasi
- **Beragam status**: tiap modul yang punya status harus punya contoh untuk SETIAP status (supaya semua tampilan dan alur bisa dicoba)
- **Volume cukup untuk menguji performa**: minimal beberapa kelas dengan 25-35 siswa per kelas, supaya masalah N+1 dan paginasi benar-benar kelihatan
- **Seeder harus menghormati aturan bisnis aplikasi.** Karena seeder menulis langsung ke database, validasi aplikasi (misal validasi bentrok jadwal, validasi bobot nilai 100%, validasi tepat 1 jawaban benar per soal) tidak otomatis berjalan. Seeder sendiri yang WAJIB memastikan data yang dibuat valid
- Struktur: 1 seeder per modul (`Database\Seeders\...`), dipanggil dari `DatabaseSeeder` dengan urutan mengikuti dependency (poin 3)
- Gunakan `Hash::make` untuk semua password, jangan pernah menyimpan plain text

### 3. Urutan Seeding & Isi Data per Modul

Urutan wajib mengikuti dependency antar modul:

1. **Role, user, dan akun demo.** Satu akun demo per role yang ada (superadmin, admin/bendahara, kepala sekolah, guru, wali kelas, siswa, orang tua). Password demo seragam dan jelas (misal `password`), email berpola (`admin@demo.test`, dst). Dokumentasikan semuanya di `docs/seeding.md`
2. **Data Dasar.** 2 tahun ajaran (yang sekarang aktif + 1 sebelumnya), semester dengan SATU yang `is_active = true`, jurusan (kalau dipakai), sekitar 6-9 kelas, mapel yang realistis untuk jenjangnya, sekitar 15-25 guru dengan relasi guru-mapel-kelas yang jelas, wali kelas per kelas, 25-35 siswa per kelas (tiap siswa terhubung ke akun user dan ke orang tua)
3. **Kalender Akademik.** Kategori event (minimal: libur nasional, libur sekolah, ujian, kegiatan; yang libur punya `is_holiday = true`), event sekolah-wide dan beberapa event khusus 1 kelas yang dibuat guru. Untuk libur nasional yang tanggalnya tetap (1 Januari, 1 Mei, 1 Juni, 17 Agustus, 25 Desember), gunakan tanggalnya. **Untuk libur keagamaan yang tanggalnya bergeser tiap tahun, jangan mengarang tanggal "pasti"**: pakai tanggal perkiraan dan tandai jelas di `docs/seeding.md` bahwa tanggal tersebut hanya data demo dan harus diverifikasi ke SKB resmi sebelum dipakai sungguhan
4. **Jadwal Pelajaran.** Hari aktif (5 atau 6 hari), time slot dengan durasi yang tidak seragam termasuk shift, beberapa ruang (tetap dan umum), dan **jadwal penuh untuk semua kelas yang TIDAK bentrok** (guru, kelas, ruang). Buat generator yang menjamin tidak ada bentrok, lalu **jalankan validasi bentrok bawaan aplikasi atau query verifikasi** pada hasil akhir untuk membuktikannya
5. **LMS.** Materi, tugas, pengumpulan tugas dengan beragam status (belum dikumpul, sudah dikumpul, sudah dinilai), nilai tugas
6. **Ujian Online.** Bank soal pilihan ganda per mapel (minimal 20 soal untuk 2-3 mapel, tepat 1 jawaban benar per soal, sebagian soal memakai gambar placeholder yang dibuat sederhana atau dikosongkan kalau tidak bisa dibuat), beberapa ujian dengan status berbeda (draft, published, closed), peserta dengan kode unik, dan **peserta di setiap status**: belum mulai, mengerjakan, selesai, waktu habis, menunggu approve, sudah approve, serta 1-2 peserta dengan jendela susulan. Untuk peserta yang sudah mengerjakan, buat `question_order` acak yang valid dan jawaban yang konsisten dengan skornya
7. **Nilai & Rapor.** Bobot nilai per mapel (**total tepat 100%**), nilai siswa dari kedua sumber (`lms` dan `manual`), rapor dengan status di semua tahap (draft, perlu verifikasi, terverifikasi, published), sebagian dengan deskripsi capaian dan sebagian kosong
8. **PPDB.** Gelombang pendaftaran, pendaftar di SEMUA status (pending, verified, need_revision, selected, rejected, re-registered), dokumen dengan status valid/invalid/pending, pembayaran pendaftaran, skor seleksi, data seragam (ukuran huruf S-XXL; field kerudung & jenis bawahan terisi untuk perempuan dan kosong/default untuk laki-laki sesuai aturan modul), dan **beberapa pendaftar yang sudah resmi jadi siswa dengan `ppdb_payments.student_id` terisi** (supaya riwayat gabungan di modul Keuangan bisa diuji)
9. **Keuangan Sekolah.** Jenis tagihan (SPP berulang, uang gedung sebagai tagihan awal, uang komite, seragam), tagihan beberapa bulan untuk siswa aktif dengan status beragam (belum bayar, menunggu verifikasi, lunas, terlambat), pembayaran manual dengan status pending/verified/rejected, sebagian siswa dengan nominal tagihan override (keringanan)
10. **Manajemen Menu** (kalau modulnya sudah dibangun): seed tabel `modules` dan `module_dependencies` sesuai kondisi aktual, jangan menebak daftar modulnya, ambil dari hasil audit

### 4. Verifikasi Setelah Seeding (WAJIB, tampilkan hasilnya ke saya)

1. Jalankan `migrate:fresh --seed --env=demo` **setelah saya konfirmasi nama database** (aturan keselamatan poin 3), pastikan selesai tanpa error
2. Jalankan `--seed` dua kali berturut-turut pada database yang sudah dimigrasi ulang untuk memastikan hasilnya konsisten (jumlah data sama)
3. Tampilkan tabel ringkasan jumlah baris per tabel utama
4. Query pembuktian integritas, dan tampilkan hasilnya:
   - Tidak ada jadwal bentrok (guru/kelas/ruang pada time slot yang sama)
   - Total bobot nilai tiap mapel = 100
   - Tiap soal punya tepat 1 jawaban benar
   - Hanya 1 semester yang `is_active`
   - Tidak ada foreign key yatim (orphan)
5. Login manual dengan tiap akun demo dan konfirmasi bahwa dashboard role-nya terbuka (jelaskan ke saya langkah ceknya, jangan hanya klaim berhasil)

### 5. Dokumentasi

Buat `docs/seeding.md` berisi: cara membuat database demo di Laragon, cara menjalankan seeding (perintah lengkap dengan `--env=demo`), **daftar akun demo per role beserta peringatan keras bahwa akun ini hanya untuk demo/testing dan tidak boleh ada di production**, ringkasan isi data per modul, catatan soal tanggal libur keagamaan yang perlu diverifikasi, dan cara mereset database demo ke kondisi awal

### Cara Kerja
1. Audit (poin 0), tampilkan hasilnya, tunggu konfirmasi saya
2. Siapkan database demo dan migrate dari nol (poin 1), tampilkan hasilnya
3. Buat seeder **per modul secara berurutan** sesuai poin 3. Setiap 1-2 modul selesai, jelaskan ke saya apa yang dibuat dan cara memeriksanya, lalu **tunggu konfirmasi saya** sebelum lanjut
4. Verifikasi menyeluruh (poin 4), lalu dokumentasi (poin 5)
5. Jangan `git commit` sebelum saya konfirmasi paham dan hasilnya sesuai

### Batasan Penting
- Hanya menyentuh `database/`, `.env.demo` (contoh, jangan menyertakan kredensial asli ke git), dan `docs/seeding.md`
- Jangan membuat data yang melanggar aturan bisnis yang sudah kita sepakati antar modul
- Kalau suatu modul ternyata struktur tabelnya berbeda dari yang saya sebut di prompt ini, **ikuti struktur nyata di kode**, dan beri tahu saya perbedaannya
- Kalau ada bagian yang ambigu, tanya saya dulu, jangan berimprovisasi sendiri

---

**Mulai dari poin 0 (audit), tampilkan hasilnya sebelum menjalankan perintah apa pun.**
