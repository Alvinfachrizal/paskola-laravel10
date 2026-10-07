# Checklist QA — Data Master (Administrasi)

> Sumber skenario: Kode `StudentController.php`, `TeacherController.php`, `SchoolClassController.php`, `SubjectController.php`, `SchoolYearController.php`, `MajorController.php`, migration tabel `students`, `teachers`, `school_classes`, `subjects`, `school_years`, `majors`.

## Status: [ ] Belum diuji / [ ] Sedang diuji / [ ] Lulus / [ ] Ada bug

---

## Skenario Fungsional — Manajemen Siswa

- [ ] **Tambah siswa baru** — Admin mengisi form siswa lengkap → data tersimpan di tabel `students`
- [ ] **Edit data siswa** — Admin mengubah nama/NISN siswa → perubahan tersimpan
- [ ] **Hapus siswa** — Admin menghapus siswa → record terhapus dari `students` dan relasi terkait tidak error
- [ ] **Assign siswa ke kelas** — Siswa berhasil di-assign ke `school_classes` via `student_classes`
- [ ] **Siswa otomatis dibuat saat Daftar Ulang PPDB** — Setelah proses daftar ulang PPDB, record siswa baru muncul di tabel `students` dan akun `users` ikut terbuat

## Skenario Fungsional — Manajemen Guru

- [ ] **Tambah guru baru** — Admin mengisi form guru → data tersimpan di `teachers` dan akun `users` terkait terbuat
- [ ] **Assign guru sebagai wali kelas** — Kolom `homeroom_teacher_id` di `school_classes` berhasil di-set
- [ ] **Assign guru mengajar mapel** — Guru dapat di-assign ke mapel tertentu

## Skenario Fungsional — Master Akademik

- [ ] **CRUD Tahun Ajaran** — Admin bisa menambah, mengedit, menghapus tahun ajaran; hanya satu yang bisa berstatus `active`
- [ ] **CRUD Jurusan (Major)** — Admin bisa mengelola jurusan
- [ ] **CRUD Kelas** — Admin bisa membuat kelas baru yang terhubung ke jurusan dan tahun ajaran
- [ ] **CRUD Mata Pelajaran** — Admin bisa menambah mata pelajaran baru

---

## Skenario Batasan Akses (Role)

- [ ] **Guru tidak bisa akses CRUD siswa** — Login sebagai Guru, akses `/admin/students/create` → 403
- [ ] **Siswa tidak bisa akses CRUD siswa** — Login sebagai Siswa, akses `/admin/students` → 403
- [ ] **Hanya Admin yang bisa hapus data master** — Kepala Sekolah tidak bisa menghapus data siswa (verifikasi di UI dan route)

---

## Skenario Edge Case

- [ ] **NISN duplikat** — Coba tambah siswa dengan NISN yang sudah ada → sistem menolak dengan pesan error
- [ ] **Hapus tahun ajaran yang masih aktif** — Sistem harus mencegah atau memperingatkan
- [ ] **Kelas tanpa siswa** — Kelas kosong tetap bisa dibuat dan tidak error saat diakses
- [ ] **Guru yang juga wali kelas dihapus** — Verifikasi tidak ada error di database karena FK constraint

---

## Bug Ditemukan (isi saat testing)

| Tanggal | Deskripsi Bug | Tingkat Keparahan | Status |
|---|---|---|---|
| | | | |
