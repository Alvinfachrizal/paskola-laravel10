# Checklist QA — Ujian Online (Pilihan Ganda)

> Sumber skenario: `docs/ujian-online.md` (bagian "Cara Pengujian Manual"), kode `app/Policies/ExamPolicy.php`, `app/Policies/QuestionPolicy.php`, `app/Http/Controllers/Exam/ExamSessionController.php`. Skenario keamanan kritis diambil dari spesifikasi arsitektur di `docs/ujian-online.md` bagian "Logika & RBAC Policy".

## Status: [ ] Belum diuji / [ ] Sedang diuji / [ ] Lulus / [ ] Ada bug

---

## Skenario Fungsional — Bank Soal

- [ ] **Guru tambah soal baru** — Guru mengisi teks soal, 4 pilihan jawaban, menandai 1 jawaban benar → tersimpan di `questions` dan `question_options`
- [ ] **Import soal format Aiken** — Guru paste teks soal dengan format `A. ... / JAWABAN: B` → soal masuk ke bank soal secara massal
- [ ] **Guru edit soal** — Guru mengubah teks soal atau pilihan jawaban → tersimpan dengan benar
- [ ] **Guru hapus soal** — Guru menghapus soal dari bank soal → soal hilang dari daftar
- [ ] **Filter soal per mapel** — Di halaman bank soal, filter berdasarkan mata pelajaran → hanya soal mapel tersebut yang tampil

## Skenario Fungsional — Manajemen Ujian

- [ ] **Guru buat ujian baru** — Guru mengisi judul, pilih mapel, pilih kelas, set durasi, set rentang waktu, pilih soal dari bank soal → tersimpan di `exams` dan `exam_questions`
- [ ] **Guru publish ujian** — Klik Publish → status berubah dari `draft` ke `published` dan peserta otomatis di-generate dari daftar siswa kelas terkait (`exam_participants`)
- [ ] **Guru lihat daftar peserta** — Setelah publish, tombol "Peserta" aktif → daftar nama siswa dengan status ujian masing-masing tampil
- [ ] **Guru lihat hasil ujian** — Setelah ujian berlangsung, tombol "Hasil" aktif → daftar nilai siswa tampil
- [ ] **Guru approve nilai** — Guru menekan "Approve" pada hasil ujian siswa → status nilai berubah menjadi approved
- [ ] **Guru buka ujian susulan** — Guru mengisi `allowed_from` dan `allowed_until` untuk siswa yang belum mulai → siswa bisa mengikuti ujian di rentang waktu tersebut

## Skenario Fungsional — Sesi Ujian (Siswa)

- [ ] **Siswa masukkan kode ujian** — Login sebagai Siswa, masukkan kode dari `exam_participants.code` → masuk ke halaman ujian
- [ ] **Soal tampil sesuai urutan acak yang tersimpan** — Urutan soal di-generate saat pertama kali memulai, disimpan di `question_order` di `exam_participants` → refresh halaman, urutan TETAP sama
- [ ] **Auto-save jawaban** — Siswa pilih jawaban → jawaban langsung tersimpan ke server (bisa dicek di tabel `exam_answers`), tanpa perlu klik submit manual
- [ ] **Timer aktif dan akurat** — Timer countdown tampil di layar sesuai durasi yang diset guru
- [ ] **Jawaban ditolak setelah waktu habis** — Setelah waktu habis, siswa mencoba submit/pilih jawaban → server menolak dengan error, tidak tersimpan
- [ ] **Finalisasi saat waktu habis** — Ketika siswa membuka halaman ujian setelah waktu habis, secara otomatis dialihkan ke halaman hasil (lazy finalization)

---

## Skenario Batasan Akses (Role) — KRITIS

- [ ] **Guru A tidak bisa akses ujian Guru B** — Login sebagai Guru A, coba akses URL `/exam/exams/{id_ujian_guru_B}/edit` → 403 dari `ExamPolicy::update()`
- [ ] **Guru A tidak bisa lihat hasil ujian Guru B** — Akses halaman peserta/hasil ujian milik Guru B → 403 dari `ExamPolicy::viewResults()`
- [ ] **Admin/Kepsek tidak bisa buat atau edit ujian** — Login sebagai Admin, akses `/exam/exams/create` → 403 dari `ExamPolicy::create()`
- [ ] **Siswa dari kelas yang tidak dituju tidak bisa ikut ujian** — Siswa Kelas X-B memasukkan kode ujian yang ditujukan untuk Kelas X-A → ditolak
- [ ] **Guru tidak bisa akses bank soal guru lain** — Guru A tidak bisa melihat soal-soal yang dibuat Guru B (privat per guru)

---

## Skenario Keamanan (WAJIB)

- [ ] **`is_correct` TIDAK pernah terkirim ke browser siswa** — Saat mengerjakan ujian, buka DevTools → Network tab → cek response JSON/HTML saat soal dimuat → field `is_correct` tidak muncul di response apapun yang bisa dibaca siswa
- [ ] **Kode ujian milik siswa lain ditolak** — Siswa A mencoba memasukkan kode milik Siswa B → sistem menolak (kode di `exam_participants` bersifat unik per siswa)
- [ ] **URL direct ke halaman ujian siswa lain diblokir** — Siswa A mencoba akses URL sesi ujian Siswa B langsung → 403

---

## Skenario Edge Case

- [ ] **Ujian tanpa soal tidak bisa di-publish** — Coba publish ujian yang belum memiliki soal → sistem menolak
- [ ] **Siswa yang sudah selesai tidak bisa jawab ulang** — Siswa dengan status `selesai` tidak bisa mengakses halaman ujian kembali untuk mengubah jawaban
- [ ] **Refresh saat mengerjakan** — Siswa refresh halaman → urutan soal dan jawaban yang sudah dipilih tetap sama

---

## Bug Ditemukan (isi saat testing)

| Tanggal | Deskripsi Bug | Tingkat Keparahan | Status |
|---|---|---|---|
| | | | |
