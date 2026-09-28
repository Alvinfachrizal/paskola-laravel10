# Modul Ujian Online Pilihan Ganda

Modul ini memfasilitasi pembuatan, pelaksanaan, dan penilaian ujian pilihan ganda secara online.

## 1. Fitur Utama
- **Bank Soal**: Guru dapat membuat soal beserta poin per opsi, menyimpan di bank soal, dan menggunakan soal yang sama di ujian-ujian berbeda. Soal dan pilihan dapat memiliki gambar.
- **Manajemen Ujian**: Ujian dapat diatur untuk kelas tertentu, memiliki durasi pengerjaan spesifik, rentang waktu ujian (start_at, end_at), dan opsi `show_score` (apakah siswa langsung bisa lihat nilai atau menunggu approval).
- **Pengacakan Opsi**: Opsi soal (A,B,C,D) *tidak* diacak posisinya, melainkan urutan soal antar siswa yang diacak. Urutan ini di-generate pada saat pertama siswa memulai dan disimpan di `question_order` agar tetap sama meskipun direfresh.
- **Keamanan Waktu**: Waktu ujian diverifikasi sepenuhnya oleh server, baik ketika mengambil soal maupun saat mengumpulkan jawaban. Ketika waktu habis, finalisasi `waktu_habis` dilakukan secara lazy tanpa memerlukan laravel scheduler.
- **Ujian Susulan**: Guru dapat membuka rentang waktu khusus (allowed_from, allowed_until) untuk siswa yang berstatus `belum_mulai`.
- **Approval Hasil**: Guru dapat mengulas detail per jawaban, lalu melakukan approve nilai. *Catatan: Nilai pada modul ini TIDAK disinkronkan atau masuk ke rapor `student_grades` saat ini (sesuai spesifikasi fase pertama).*

## 2. Struktur Tabel
1. **`questions`**: Data utama pertanyaan (terhubung ke mapel, guru).
2. **`question_options`**: Opsi A-E untuk soal, menampung boolean `is_correct`.
3. **`exams`**: Data setting ujian.
4. **`exam_questions`**: Pivot tabel ujian dan soal beserta poin per soal.
5. **`exam_classes`**: Pivot tabel untuk mendaftarkan kelas peserta.
6. **`exam_participants`**: Tabel paling vital, menghubungkan ujian dan siswa. Memuat token unik `code`, `status`, `question_order` (JSON), dan informasi waktu mulai/selesai/skor.
7. **`exam_answers`**: Tabel penyimpan jawaban siswa (opsi yang dipilih dan stempel waktu).

## 3. Logika & RBAC Policy
Pemisahan hak akses:
- **Guru**: CRUD pada Bank Soal dan Ujian *hanya untuk milik mereka sendiri*.
- **Admin/Kepala Sekolah**: Punya akses Read-Only untuk melihat soal dan hasil secara global.
- **Siswa**: Hanya dapat mengakses ujian yang menargetkan kelasnya. Saat ujian, hanya dapat melihat dan menyimpan baris jawaban mereka masing-masing. *Kunci is_correct TIDAK pernah dikirim ke browser.*

## 4. Cara Pengujian Manual (Skenario)
1. **Guru A** masuk ke bank soal, menambah 5 soal, buat Ujian untuk **Kelas X-A**, simpan poin tiap soal, lalu tekan **Publish**.
2. **Siswa X-A 1** mengambil token `code` dari halaman guru, login, masukkan `code`, mulai ujian.
3. Siswa refresh, urutan soal tetap sama.
4. Siswa menjawab beberapa, lalu mematikan tab. Jawaban sudah auto-save.
5. Jika waktu habis, ketika siswa masuk kembali, ia dialihkan ke halaman Result.
6. **Guru A** dapat melihat hasil dan meng-klik **Approve Semua**.
