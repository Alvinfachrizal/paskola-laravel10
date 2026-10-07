# Role Access Matrix — Paskola SIMS

> Disusun berdasarkan pembacaan langsung kode Policy: `ExamPolicy.php`, `QuestionPolicy.php`, `ExamParticipantPolicy.php`, `StudentGradePolicy.php`, `ReportCardPolicy.php`, `AcademicEventPolicy.php`, `EventCategoryPolicy.php`, serta logika di middleware dan controller masing-masing modul.
>
> **Keterangan kolom "Sudah Diuji":** Isi `Y` jika skenario ini sudah diverifikasi secara manual saat testing, `N` jika belum.

---

## 1. Auth & Akses Sistem

| Role | Aksi | Diizinkan | Dilarang | Sudah Diuji |
|---|---|---|---|---|
| Semua Role | Login | ✅ | | N |
| Semua Role | Logout | ✅ | | N |
| Guest (belum login) | Akses `/dashboard` | ❌ → redirect `/login` | | N |
| Siswa | Akses halaman Admin | ❌ → 403 | | N |
| Ortu | Akses halaman Admin | ❌ → 403 | | N |
| Guru | Akses manajemen user | ❌ → 403 | | N |

---

## 2. Data Master (Administrasi)

| Role | Modul | Aksi Diizinkan | Aksi Dilarang | Sudah Diuji |
|---|---|---|---|---|
| Super Admin, Admin | Siswa | CRUD lengkap | | N |
| Kepala Sekolah | Siswa | Read-only | Tambah/Edit/Hapus | N |
| Guru | Siswa | ❌ tidak ada akses | | N |
| Siswa, Ortu | Siswa | ❌ tidak ada akses | | N |
| Super Admin, Admin | Guru | CRUD lengkap | | N |
| Super Admin, Admin | Kelas, Mapel, Tahun Ajaran | CRUD lengkap | | N |

---

## 3. LMS (Materi & Tugas)

| Role | Modul | Aksi Diizinkan | Aksi Dilarang | Sudah Diuji |
|---|---|---|---|---|
| Guru | Materi | Buat, Edit, Hapus materi kelasnya | Edit/hapus materi guru lain | N |
| Guru | Tugas | Buat, Edit, Hapus, Nilai submission | Edit/hapus tugas guru lain | N |
| Siswa | Materi | Lihat materi kelasnya | Buat/Edit/Hapus | N |
| Siswa | Tugas | Lihat & kumpulkan tugas kelasnya | Lihat submission siswa lain | N |
| Ortu | Materi/Tugas | ❌ tidak ada akses langsung | | N |
| Admin/Kepsek | LMS | Read-only (monitoring) | Tambah/edit tugas/materi | N |

---

## 4. Kalender Akademik

| Role | Aksi | Diizinkan | Dilarang | Sudah Diuji |
|---|---|---|---|---|
| Admin, Kepala Sekolah | Lihat kalender | ✅ | | N |
| Admin, Kepala Sekolah | Buat event (semua kelas, termasuk libur) | ✅ | | N |
| Admin, Kepala Sekolah | Kelola kategori event | ✅ | | N |
| Guru | Lihat kalender | ✅ | | N |
| Guru | Buat event | ✅ (semua kelas, TANPA kategori libur) | Buat event dengan `is_holiday = true` | N |
| Guru | Kelola kategori event | ❌ → 403 | | N |
| Siswa, Ortu | Lihat kalender | ✅ | | N |
| Siswa, Ortu | Buat/Edit/Hapus event | ❌ → tidak ada tombol | | N |

---

## 5. Jadwal Pelajaran

| Role | Aksi | Diizinkan | Dilarang | Sudah Diuji |
|---|---|---|---|---|
| Admin, Kepsek | Setting hari aktif, time slot, ruangan | ✅ | | N |
| Admin, Kepsek | Buat/Edit/Hapus jadwal | ✅ | | N |
| Guru | Lihat jadwal miliknya | ✅ (grid mingguan) | Lihat jadwal guru lain | N |
| Guru | Edit jadwal | ❌ | | N |
| Siswa | Lihat jadwal kelasnya | ✅ (grid mingguan) | Lihat jadwal kelas lain | N |
| Siswa | Edit jadwal | ❌ | | N |

---

## 6. Nilai & Rapor

| Role | Modul | Aksi Diizinkan | Aksi Dilarang | Sudah Diuji |
|---|---|---|---|---|
| Admin, Kepala Sekolah | Nilai & Rapor | Read semua + Publish rapor | Input/edit nilai langsung | N |
| Guru (mapel) | Nilai | Input/edit nilai HANYA mapel yang diajar, HANYA kelas yang diajar | Nilai mapel/kelas guru lain | N |
| Guru (wali kelas) | Rapor | Verifikasi rapor kelas yang diwalikan | Ubah nilai langsung | N |
| Siswa | Rapor | Lihat rapor milik sendiri (status published) | Lihat rapor siswa lain | N |
| Siswa | `student_grades` | ❌ tidak bisa akses | | N |
| Ortu | Rapor | Lihat rapor anak yang terkait (status published) | | N |

---

## 7. PPDB Online

| Role | Aksi | Diizinkan | Dilarang | Sudah Diuji |
|---|---|---|---|---|
| Guest/Publik | Akses portal, daftar, cek status | ✅ | | N |
| Admin, Super Admin | Dashboard panitia, verifikasi, seleksi, daftar ulang | ✅ | | N |
| Kepala Sekolah | Dashboard PPDB | Read-only | Verifikasi/aksi | N |
| Guru, Siswa, Ortu | Panel admin PPDB | ❌ → 403 | | N |

---

## 8. Keuangan Sekolah

| Role | Aksi | Diizinkan | Dilarang | Sudah Diuji |
|---|---|---|---|---|
| Admin (Bendahara) | CRUD Jenis Tagihan, Verifikasi Pembayaran, Dashboard Rekap | ✅ | | N |
| Siswa | Lihat tagihan sendiri, Upload bukti bayar | ✅ | Lihat tagihan siswa lain | N |
| Ortu | Lihat tagihan anak, Upload bukti bayar | ✅ | Lihat tagihan siswa lain | N |
| Guru | Akses modul keuangan | ❌ → 403 | | N |
| Siswa | Verifikasi pembayaran | ❌ → 403 | | N |

---

## 9. Ujian Online

| Role | Aksi | Diizinkan | Dilarang | Sudah Diuji |
|---|---|---|---|---|
| Guru | Bank Soal: CRUD soal miliknya | ✅ | CRUD soal guru lain | N |
| Guru | Ujian: Buat, Edit, Publish, Hapus (hanya miliknya) | ✅ | Akses ujian guru lain | N |
| Guru | Lihat peserta & hasil ujian miliknya | ✅ | Lihat hasil ujian guru lain | N |
| Guru | Approve nilai ujian | ✅ (hanya ujian miliknya) | Approve ujian guru lain | N |
| Admin, Kepala Sekolah | Ujian: Read-only global | ✅ | Buat/Edit/Publish ujian | N |
| Siswa | Ikut ujian yang ditujukan untuk kelasnya | ✅ | Ikut ujian kelas lain | N |
| Siswa | Lihat soal ujian aktif | ✅ (tanpa `is_correct`) | Melihat kunci jawaban | N |
| Ortu | Akses modul ujian | ❌ | | N |

---

## 10. Manajemen Modul (Toggle Fitur)

| Role | Aksi | Diizinkan | Dilarang | Sudah Diuji |
|---|---|---|---|---|
| Super Admin | Aktifkan/nonaktifkan modul | ✅ | | N |
| Admin | Toggle modul | ❌ (hanya Super Admin) | | N |
| Semua Role | Akses modul yang nonaktif | ❌ → 403 via middleware | | N |
