# Prompt: Bangun Modul Ujian Online Pilihan Ganda — dalam project Paskola Laravel

Copy-paste ke Claude Code/Cursor **di dalam project Laravel Paskola**, dijalankan setelah modul LMS dan Administrasi Data Dasar selesai (butuh data guru, mapel, kelas, siswa, dan relasi guru-mapel-kelas yang sudah ada).

---

## PROMPT

Kamu adalah senior Laravel engineer yang juga sabar membimbing pemula (ingat: saya masih belajar, jelaskan alur tiap bagian, jangan asumsikan saya paham istilah teknis tanpa penjelasan singkat — ikuti format "ringkasan alur modul" yang sudah kita pakai di modul-modul sebelumnya).

Bangun **modul Ujian Online pilihan ganda** untuk project Laravel Paskola.

### 0. Langkah Pertama: Audit LMS yang Sudah Ada (WAJIB sebelum coding)
Modul LMS sebelumnya berisi Materials, Assignments, Submissions, dan Grading. Sebelum membuat tabel apa pun, cek apakah sudah ada tabel/model untuk kuis, soal, atau ujian di project. Tampilkan hasil pengecekanmu ke saya. Kalau ada yang tumpang tindih, jangan menduplikasi — tanya saya dulu apakah diperluas atau dibuat terpisah.

### 1. Konsep Dasar (final, sudah disepakati)
- Guru membuat soal **pilihan ganda**. Soal bisa dikelola lewat **bank soal** (dibuat sekali, dipakai ulang di banyak ujian) **dan** bisa dibuat langsung saat menyusun ujian. Keduanya memakai **satu tabel `questions`**, dibedakan kolom `in_bank`
- Soal bisa **bergambar** (gambar di soal, dan gambar di pilihan jawaban)
- **Semua siswa mendapat soal yang sama, hanya URUTAN soalnya yang diacak per siswa.** Pilihan jawaban (A, B, C, D) TIDAK diacak
- Setiap siswa punya **kode ujian unik**, terikat ke akunnya. Siswa login, lalu memasukkan kodenya untuk memulai. Kode milik siswa lain harus ditolak (supaya berbagi kode tidak ada gunanya)
- **Waktu ujian ketat**: dihitung di server. Kalau waktu habis, siswa tidak bisa mengerjakan lagi, jawaban yang sudah tersimpan tetap dinilai
- **Ujian susulan**: siswa yang tidak hadir (belum pernah memulai) boleh ikut susulan memakai kode yang sama, setelah dibuka oleh guru
- **Nilai TIDAK masuk ke rapor / `student_grades`.** Nilai hanya dicatat di modul ini. Guru memvalidasi hasil lewat approval. Fitur bantu menghitung nilai final adalah **fase berikutnya, jangan dibangun sekarang**

### 2. Skema Database

Buat migration + Eloquent Model untuk:

- **`questions`**: `id, teacher_id (FK), subject_id (FK), question_text, image_path (nullable), in_bank (boolean)`
- **`question_options`**: `id, question_id (FK), option_text (nullable), image_path (nullable), is_correct (boolean), position (int, urutan tetap A/B/C/D)`
  - Validasi: setiap soal punya minimal 2 dan maksimal 5 pilihan, dengan **tepat 1** jawaban benar. Setiap pilihan harus punya teks atau gambar
- **`exams`**: `id, teacher_id (FK), subject_id (FK), title, description (nullable), start_at, end_at, duration_minutes, show_score (enum: after_submit/after_approve, default after_approve), status (enum: draft/published/closed)`
- **`exam_questions`**: `id, exam_id (FK), question_id (FK), points (decimal, default 1)`. Unique pada (exam_id, question_id)
- **`exam_classes`**: `id, exam_id (FK), class_id (FK)`
- **`exam_participants`** (pusat modul ini, 1 baris = 1 siswa di 1 ujian): `id, exam_id (FK), student_id (FK), code (unique), question_order (json, nullable), started_at (nullable), finished_at (nullable), score (decimal, nullable), status (enum: belum_mulai/mengerjakan/selesai/waktu_habis), allowed_from (nullable), allowed_until (nullable), grade_status (enum: menunggu_approve/approved), approved_by (FK ke users, nullable), approved_at (nullable)`. Unique pada (exam_id, student_id)
  - `allowed_from` dan `allowed_until` dipakai sebagai jendela waktu khusus untuk ujian susulan
- **`exam_answers`**: `id, participant_id (FK), question_id (FK), option_id (FK, nullable), answered_at`. Unique pada (participant_id, question_id)

**Jangan buat relasi ke `student_grades`.** Semua status dikelola sebagai PHP Enum class, bukan enum kolom MySQL.

### 3. Logika Inti (WAJIB, bangun sebagai service terpisah, misal `ExamSessionService`)

**a. Pembuatan peserta & kode.** Saat guru memilih kelas dan mempublish ujian, sistem membuat 1 baris `exam_participants` untuk setiap siswa aktif di kelas itu, lengkap dengan kode unik acak (8 karakter, hindari karakter yang mirip seperti 0/O dan 1/I/l). Sediakan tombol "Sinkronkan peserta" untuk siswa yang ditambahkan ke kelas setelah ujian dipublish.

**b. Validasi saat siswa memasukkan kode**, dengan urutan pengecekan:
1. Kode ada
2. `participant.student_id` sama dengan siswa yang sedang login
3. Ujian berstatus `published`
4. Waktu sekarang berada dalam jendela ujian: `exams.start_at` sampai `exams.end_at`, atau `allowed_from` sampai `allowed_until` kalau peserta punya jendela khusus
5. Status peserta `belum_mulai` atau `mengerjakan` (untuk melanjutkan)

Batasi percobaan kode salah (Laravel RateLimiter, misal 5 kali per 10 menit per akun). Pesan error boleh spesifik ("kode salah", "ujian belum dibuka", "ujian sudah ditutup") tapi jangan pernah mengungkap kode ini milik siapa.

**c. Memulai & mengacak soal.** Saat kode valid pertama kali: isi `started_at`, ubah status jadi `mengerjakan`, dan buat `question_order` dengan mengacak daftar id soal, lalu **simpan sekali** sebagai JSON. Setiap kali siswa refresh atau melanjutkan, pakai `question_order` yang tersimpan. **Jangan pernah mengacak ulang.**

**d. Timer di server.** Batas waktu = yang lebih awal antara `started_at + duration_minutes` dan penutup jendela efektif (`end_at` atau `allowed_until`). Setiap request muat soal atau simpan jawaban harus mengecek batas ini. Kalau lewat: tolak request, ubah status jadi `waktu_habis`, hitung nilai dari jawaban yang sudah tersimpan. Timer di JavaScript hanya tampilan (ambil sisa waktu dari server) dan boleh auto-submit saat 0, tapi **server adalah sumber kebenaran**.

**e. Finalisasi peserta yang menutup browser.** Peserta yang berstatus `mengerjakan` tapi sudah lewat batas waktu harus otomatis difinalisasi. Implementasikan finalisasi "malas" (dicek saat guru membuka daftar hasil atau saat siswa membuka ulang), dan jelaskan ke saya trade-off-nya dibanding Laravel Scheduler (ingat catatan Laragon soal scheduler yang sudah pernah kita bahas).

**f. Simpan jawaban otomatis.** Setiap siswa memilih jawaban, kirim lewat AJAX ke endpoint yang mengecek batas waktu lalu menyimpan (upsert) ke `exam_answers`. Siswa boleh mengubah jawaban selama waktu belum habis dan boleh berpindah antar soal bebas.

**g. Keamanan jawaban.** Data soal yang dikirim ke browser siswa HANYA berisi id soal, teks, url gambar, dan id + teks + posisi pilihan. **`is_correct` tidak boleh ada di HTML, JSON, atau response mana pun ke siswa.** Penilaian sepenuhnya di server.

**h. Penilaian.** Skor = (jumlah poin soal yang dijawab benar / jumlah poin seluruh soal di ujian) x 100. Jawaban kosong bernilai 0. Simpan di `exam_participants.score` saat submit atau waktu habis.

**i. Ujian susulan.** Guru punya tombol "Buka susulan" di halaman peserta. Guru memilih peserta yang berstatus `belum_mulai` (default: semua yang belum mulai), lalu mengisi `allowed_from` dan `allowed_until`. Siswa memakai kode yang sama. Peserta yang sudah pernah memulai (`mengerjakan`, `selesai`, `waktu_habis`) **tidak bisa** diberi susulan lewat fitur ini. Fitur reset untuk yang sudah mulai TIDAK termasuk di cakupan ini.

**j. Approval guru.** Guru melihat daftar hasil (nama, skor, waktu mulai/selesai, status) dan bisa melihat detail jawaban per siswa (soal, jawaban dipilih, benar atau salah). Guru bisa approve per siswa atau "approve semua yang sudah selesai". Approve mengisi `grade_status = approved`, `approved_by`, `approved_at`. **Approve TIDAK menulis ke `student_grades` dan tidak mengubah rapor.** Sebelum approve, nilai terlihat oleh siswa sesuai pengaturan `show_score` di ujian.

### 4. Batasan Role (WAJIB pakai Laravel Policy)
| Role | Kewenangan |
|---|---|
| **Guru** | CRUD soal miliknya sendiri (bank soal bersifat pribadi per guru) untuk mapel yang diampu. Membuat ujian hanya untuk mapel dan kelas yang dia ajar (cek relasi guru-mapel-kelas yang sudah ada). Melihat hasil, membuka susulan, dan approve hanya untuk ujian miliknya |
| **Siswa** | Hanya melihat ujian yang kelasnya terdaftar, hanya mengakses baris `exam_participants` miliknya, tidak bisa melihat soal sebelum memulai |
| **Admin/Kepala Sekolah** | Read-only untuk melihat semua ujian dan hasil |
| **Wali kelas / Orang tua** | Tidak ada akses di modul ini |

### 5. Tampilan
- **Guru**: halaman bank soal (daftar, filter mapel, form tambah/edit soal dengan upload gambar dan pilihan jawaban dinamis, penanda jawaban benar), form buat ujian (judul, mapel, kelas, jadwal, durasi, pilih soal dari bank atau buat soal baru langsung, atur poin), halaman detail ujian (daftar peserta + kode + status, tombol cetak daftar kode per kelas dengan tampilan ramah cetak, tombol buka susulan), halaman hasil dan approval
- **Siswa**: menu Ujian (daftar ujian kelasnya beserta jadwal dan status), input kode, halaman mengerjakan (satu soal per layar, grid nomor soal dengan penanda sudah dijawab, timer terlihat jelas, tombol sebelumnya/berikutnya/selesai dengan konfirmasi), halaman hasil sesuai `show_score`
- Halaman siswa **wajib nyaman di HP** (banyak siswa mengerjakan lewat HP). Uji di lebar layar HP
- Upload gambar: hanya jpg/png/webp, maksimal 2 MB, nama file diacak saat disimpan

### 6. Cara Kerja (ikuti proses per-modul yang sudah kita sepakati sebelumnya)
1. Lakukan audit LMS (poin 0), tampilkan hasilnya, tunggu konfirmasi saya
2. Buat migration + model untuk semua tabel di poin 2, tampilkan ke saya untuk saya cek sebelum lanjut
3. Buat Seeder + Factory: beberapa guru dan mapel, sekitar 20 soal (sebagian bergambar pakai gambar placeholder), beberapa ujian dengan peserta yang berstatus beragam
4. Bangun `ExamSessionService` beserta unit test untuk skenario ini, tampilkan hasil test sebelum lanjut ke UI:
   - kode milik siswa lain ditolak
   - urutan soal tetap sama setelah dimuat berulang kali
   - jawaban ditolak setelah waktu habis
   - siswa `belum_mulai` bisa masuk saat jendela susulan dibuka, siswa yang sudah selesai tidak bisa
   - perhitungan skor benar untuk berbagai kombinasi jawaban
5. Buat Policy sesuai poin 4, tampilkan untuk saya cek
6. Bangun per sub-bagian secara berurutan, **tunggu konfirmasi saya paham alurnya** sebelum lanjut:
   - a) Bank soal + upload gambar
   - b) Buat ujian, pilih soal dan kelas, generate peserta + kode
   - c) Halaman peserta + cetak daftar kode
   - d) Siswa: input kode + halaman mengerjakan (autosave, timer)
   - e) Submit / waktu habis + penilaian
   - f) Ujian susulan
   - g) Guru: hasil, detail jawaban, approval
7. Setiap sub-bagian selesai: beri diagram alur singkat (`Route → Controller → Model → View`), penjelasan tiap file, cara edit kalau saya mau ubah sesuatu, cara test manual, lalu **tunggu saya konfirmasi paham** sebelum `git commit`
8. Buat dokumentasi di `docs/ujian-online.md`, termasuk catatan bahwa fitur hitung nilai final adalah fase berikutnya

### Batasan Penting
- **Jangan menulis ke `student_grades`** dan jangan mengubah modul Nilai & Rapor sama sekali
- Jangan membangun fitur hitung nilai final, reset peserta yang sudah memulai, atau log pindah tab kecuali saya minta (log pindah tab bisa saya tambahkan belakangan, tanya dulu)
- Uji skenario nyata sebelum saya anggap selesai: login sebagai siswa A lalu coba kode siswa B (harus ditolak), refresh saat ujian berjalan (urutan soal harus sama), buat ujian berdurasi 1 menit dan pastikan jawaban ditolak setelah waktu habis, buka tab Network di browser dan pastikan tidak ada `is_correct` di response siswa, login sebagai guru A dan pastikan tidak bisa melihat atau mengubah ujian guru B, coba akses ujian dari siswa kelas yang tidak terdaftar
- Kalau modul Manajemen Menu (toggle modul) sudah dibangun, daftarkan modul ini ke tabel `modules` beserta dependency-nya
- Styling ikuti aturan project: Bootstrap 5 default, kecuali saya minta Tailwind untuk halaman tertentu
- Kalau ada bagian yang ambigu, tanya saya dulu, jangan berimprovisasi sendiri

---

**Mulai dari poin 6.1 (audit LMS), tampilkan hasilnya sebelum lanjut.**
