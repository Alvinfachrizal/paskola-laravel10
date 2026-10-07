# Checklist QA — LMS (Materi, Tugas, Submission)

> Sumber skenario: Kode `LmsMaterialController.php`, `LmsAssignmentController.php`, `LmsSubmissionController.php`, migration `lms_materials`, `lms_assignments`, `lms_submissions`.

## Status: [ ] Belum diuji / [ ] Sedang diuji / [ ] Lulus / [ ] Ada bug

---

## Skenario Fungsional — Materi

- [ ] **Guru upload materi** — Guru membuat materi baru (dokumen, video, atau link) → materi muncul di halaman materi kelas
- [ ] **Siswa lihat materi** — Login sebagai siswa → buka materi yang dibuat guru → file bisa diunduh atau link bisa dibuka
- [ ] **Guru hapus materi** — Guru menghapus materi (HANYA milik sendiri) → record hilang dari database

## Skenario Fungsional — Tugas

- [ ] **Guru buat tugas** — Guru mengisi form tugas (judul, deadline, max_score, file opsional) → tugas tersimpan
- [ ] **Siswa kumpulkan tugas** — Siswa membuka tugas, klik "Kumpulkan", upload file dan notes → status submission tersimpan
- [ ] **Kumpul ulang (Siswa)** — Siswa mengumpulkan tugas lagi → submission lama tergantikan (sistem menggunakan `updateOrCreate`)
- [ ] **Guru hapus tugas** — Guru menghapus tugas (HANYA milik sendiri)
- [ ] **Guru nilai tugas** — Guru membuka submission siswa → input nilai (`score`) dan feedback
- [ ] **Sinkronisasi Nilai Otomatis (KRITIS)** — Setelah guru input nilai tugas, cek tabel `student_grades` → nilai otomatis masuk dan memicu kalkulasi ulang rapor (`ReportCard::recalculate`)
- [ ] **Siswa lihat nilai tugas** — Setelah dinilai, siswa bisa melihat nilai yang diberikan guru

---

## Skenario Batasan Akses (Role)

- [ ] **Siswa tidak bisa buat tugas** — Login sebagai Siswa, akses `/lms/assignments/create` → 403
- [ ] **Guru hanya lihat submission untuk tugasnya** — Guru A tidak bisa lihat submission dari tugas Guru B
- [ ] **Siswa hanya lihat submission milik sendiri** — Siswa A tidak bisa lihat file submission Siswa B via URL langsung
- [ ] **Siswa hanya lihat tugas dari kelasnya** — Tugas dari kelas lain tidak muncul di dashboard siswa
- [ ] **Ortu tidak bisa kumpulkan tugas** — Login sebagai Ortu, coba kumpulkan tugas → 403

---

## Skenario Edge Case

- [ ] **Upload file melampaui batas ukuran** — Upload file besar → sistem menolak dengan pesan yang jelas (bukan error 500)
- [ ] **Kumpulkan tugas setelah deadline** — Jika ada batas deadline, sistem menolak atau menandai terlambat
- [ ] **Tugas tanpa submission dinilai** — Guru mencoba nilai tugas yang belum dikumpulkan siswa → sistem menangani dengan benar
- [ ] **Materi dengan file yang dihapus dari storage** — Jika file fisik terhapus, link unduhan tidak error 500

---

## Bug Ditemukan (isi saat testing)

| Tanggal | Deskripsi Bug | Tingkat Keparahan | Status |
|---|---|---|---|
| | | | |
