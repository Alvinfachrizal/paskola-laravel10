# Modul 2: Dashboard Monitoring (Berbasis Role)

Modul ini berfungsi sebagai halaman utama (beranda) aplikasi setelah pengguna berhasil login. Tampilan dan informasi yang disajikan akan berbeda secara dinamis bergantung pada peran (Role) pengguna tersebut.

## Diagram Alur Singkat
`Route (/dashboard)` ➡️ `Controller (DashboardController@index)` ➡️ `Cek Role User (Spatie)` ➡️ `View Spesifik (admin, guru, siswa, ortu)`

## Fungsi Tiap File
- `routes/web.php`: Rute `/dashboard` diarahkan ke `DashboardController@index`. Rute ini dilindungi oleh *middleware* otentikasi sehingga tamu tidak bisa mengaksesnya.
- `app/Http/Controllers/DashboardController.php`: *Controller* tunggal untuk menangani dasbor. Di dalamnya terdapat pengecekan menggunakan `$user->hasRole('...')` untuk mengarahkan ke *View* yang tepat.
- `resources/views/layouts/app-bootstrap.blade.php`: *Template* utama halaman menggunakan Bootstrap 5 CDN.
- `resources/views/layouts/navigation-bootstrap.blade.php`: Komponen bilah navigasi (Navbar) Bootstrap 5, menampilkan nama dan peran pengguna, serta tombol *Logout*.
- `resources/views/dashboard/*.blade.php`:
  - **`admin.blade.php`**: Dasbor untuk Super Admin, Admin, dan Kepala Sekolah. Menampilkan rekap global seperti total siswa, guru, kelas, dan mapel.
  - **`guru.blade.php`**: Dasbor untuk Guru. Menampilkan jadwal hari ini, tugas yang butuh penilaian, dan materi yang terpublikasi.
  - **`siswa.blade.php`**: Dasbor untuk Siswa. Menampilkan rekap kehadiran, tugas baru yang harus dikerjakan, dan rata-rata nilai.
  - **`ortu.blade.php`**: Dasbor untuk Orang Tua. Menampilkan status tagihan keuangan, kehadiran anak, dan tugas tertunda milik anak.
  - **`default.blade.php`**: Tampilan kosong jika peran tidak dikenali.

## Panduan Perubahan (Di Mana Harus Edit?)
- **Menambah/Mengurangi Data Statistik di Dasbor:** Edit *Controller* `DashboardController.php` untuk melempar (*pass*) data variabel dari database ke View, lalu tampilkan variabel tersebut di file `.blade.php` masing-masing.
- **Mengubah Desain:** Buka file `dashboard/[role].blade.php`. Desain saat ini sudah menggunakan *class* Bootstrap 5 (seperti `card`, `alert`, `row`, `col-md-4`).
- **Ubah Navigasi/Menu:** Buka `layouts/navigation-bootstrap.blade.php`. Tambahkan tautan menu `<a class="nav-link">` di dalam sana.

## Cara Test Manual
1. Login dengan akun Admin (`admin@paskola.com`), perhatikan URL dan tampilan yang diberikan (menampilkan kotak jumlah Siswa, Guru, dll).
2. Tekan tombol Logout (di kanan atas / dropdown nama akun).
3. Coba login dengan akun Guru (`guru@paskola.com`), tampilannya akan berubah total menampilkan "Jadwal Hari Ini".
4. Lakukan hal yang sama untuk Siswa (`siswa@paskola.com`).
