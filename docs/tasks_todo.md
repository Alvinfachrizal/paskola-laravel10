# Modul 3: Administrasi Data Dasar (Master Data)

## Tujuan
Membangun antarmuka dan logika backend untuk mengelola data master akademik dan profil pengguna, meliputi Tahun Ajaran, Jurusan, Kelas, Mata Pelajaran, Guru, dan Siswa.

## Rencana Kerja (Task List)

### 1. Persiapan Model & Relasi
- [x] Update Model `SchoolYear`: tambahkan `$fillable` dan fungsi scope/relasi.
- [x] Update Model `Major`: tambahkan `$fillable` dan relasi ke `SchoolClass`.
- [x] Update Model `Subject`: tambahkan `$fillable`.
- [x] Update Model `SchoolClass`: tambahkan `$fillable` dan relasi ke `SchoolYear`, `Major`, dan `Teacher` (Wali Kelas).
- [x] Update Model `Teacher`: tambahkan `$fillable` dan relasi ke `User`.
- [x] Update Model `Student`: tambahkan `$fillable` dan relasi ke `User` (Siswa & Wali).
- [x] Update Model `StudentClass`: tambahkan `$fillable` untuk riwayat kelas siswa.

### 2. Backend (Controllers & Form Requests)
- [x] Buat `SchoolYearController` beserta validasi request.
- [x] Buat `MajorController` beserta validasi request.
- [x] Buat `SubjectController` beserta validasi request.
- [x] Buat `SchoolClassController` beserta validasi request.
- [x] Buat `TeacherController` beserta validasi request.
- [x] Buat `StudentController` beserta validasi request.
- [x] Daftarkan Web Routes (dilindungi middleware auth & role admin).

### 3. Frontend (Blade Views)
*Menggunakan estetika premium, Bootstrap 5 + styling kustom*
- [x] Halaman Master Tahun Ajaran (Index, Create, Edit).
- [x] Halaman Master Jurusan (Index, Create, Edit).
- [x] Halaman Master Mata Pelajaran (Index, Create, Edit).
- [x] Halaman Master Kelas (Index, Create, Edit).
- [x] Halaman Manajemen Data Guru (Index, Create, Edit, Detail).
- [x] Halaman Manajemen Data Siswa (Index, Create, Edit, Detail).

### 4. Navigasi & Integrasi
- [x] Tambahkan menu "Akademik" & "Pengguna" di Sidebar/Navbar Dashboard Admin.
- [x] Lakukan End-to-End Testing untuk alur CRUD master data.

---
*Status: Selesai. Modul 3 telah selesai dan terintegrasi.*
