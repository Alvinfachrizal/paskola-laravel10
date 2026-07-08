# Modul 1: Auth & Role Management

Modul ini bertanggung jawab untuk mengatur otentikasi (Login/Logout) dan pembagian hak akses (Role-Based Access Control) bagi semua pengguna sistem Paskola.

## Diagram Alur Singkat
- **Login:** `Route (routes/auth.php)` ➡️ `Controller (AuthenticatedSessionController@store)` ➡️ `Model (User)` ➡️ `View (resources/views/auth/login.blade.php)`
- **Registrasi / Seeder (Data Awal):** `Seeder (database/seeders/RoleAndUserSeeder.php)` ➡️ `Model (User, Role)` ➡️ `Database`

## Fungsi Tiap File
- `routes/auth.php`: Mendefinisikan semua URL yang berkaitan dengan otentikasi seperti `/login`, `/logout`, dan `/password/reset`.
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`: Menangani logika ketika pengguna menekan tombol "Login" (mencocokkan email & password) dan "Logout" (menghapus sesi).
- `app/Http/Requests/Auth/LoginRequest.php`: Memvalidasi *input* form login (memastikan email formatnya benar dan password diisi).
- `app/Models/User.php`: Model Eloquent untuk berinteraksi dengan tabel `users`. Sudah dilengkapi *Trait* `HasUuids` (agar otomatis pakai UUID) dan `HasRoles` (dari Spatie, agar user bisa diberi role).
- `database/seeders/RoleAndUserSeeder.php`: Skrip untuk memasukkan data awal ke database. Membuat role (Super Admin, Admin, Guru, dll) dan membuat 4 akun contoh untuk kita *testing*.
- `resources/views/auth/login.blade.php`: Tampilan halaman form login.

## Panduan Perubahan (Di Mana Harus Edit?)
- **(a) Ubah tampilan form login:** Edit file `resources/views/auth/login.blade.php`.
- **(b) Ubah validasi input (misal ingin login pakai NIK/Username bukan Email):** Edit file `app/Http/Requests/Auth/LoginRequest.php` dan `AuthenticatedSessionController.php`.
- **(c) Ubah alur setelah sukses login (redirect kemana?):** Buka file `app/Providers/RouteServiceProvider.php` (ubah konstanta `HOME`) atau edit fungsi `store` di `AuthenticatedSessionController`.
- **(d) Tambah Role baru (misal: "Alumni"):** Buka `database/seeders/RoleAndUserSeeder.php`, tambahkan di array `$roles`, lalu jalankan ulang `php artisan db:seed`.

## Cara Test Manual
1. Jalankan server Laravel (buka tab terminal/cmd baru, jalankan `php artisan serve`).
2. Buka browser dan akses `http://localhost:8000/login`.
3. Coba login menggunakan salah satu akun *dummy* yang sudah kita buat:
   - **Email:** `superadmin@paskola.com` atau `admin@paskola.com` atau `guru@paskola.com` atau `siswa@paskola.com`
   - **Password:** `password123`
4. Jika berhasil masuk ke halaman `/dashboard`, berarti proses *Auth* (otentikasi) sudah sukses bekerja menggantikan sistem JWT lama!
