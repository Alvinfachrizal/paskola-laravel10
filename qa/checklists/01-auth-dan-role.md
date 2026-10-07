# Checklist QA — Auth & Role Management

> Sumber skenario: `docs/auth-and-roles.md`, kode `app/Http/Controllers/Auth/`, `database/seeders/RoleAndUserSeeder.php`, middleware `auth` dan `role` di `routes/web.php`.

## Status: [ ] Belum diuji / [ ] Sedang diuji / [ ] Lulus / [ ] Ada bug

---

## Skenario Fungsional

- [ ] **Login berhasil** — Login menggunakan email `admin@paskola.com` + password `password123` → diarahkan ke `/dashboard`
- [ ] **Login gagal** — Input password salah → muncul pesan error, tidak masuk ke dashboard
- [ ] **Logout** — Klik Logout → sesi dihapus, akses ke `/dashboard` diarahkan ke `/login`
- [ ] **Redirect setelah login** — Setiap role diarahkan ke dashboard yang sesuai setelah login
  - Super Admin/Admin → halaman admin
  - Guru → dashboard guru
  - Siswa → dashboard siswa
  - Ortu → dashboard ortu
- [ ] **Remember me / sesi expired** — Setelah sesi habis, pengguna diarahkan ke halaman login dan tidak crash

---

## Skenario Batasan Akses (Role)

- [ ] **Siswa tidak bisa akses halaman admin** — Login sebagai Siswa, akses `/admin/students` → 403 atau redirect
- [ ] **Guru tidak bisa akses manajemen user** — Login sebagai Guru, coba akses `/admin/users` → 403 atau redirect
- [ ] **Ortu tidak bisa akses area Guru** — Login sebagai Ortu, akses `/exam/exams/create` → 403 atau redirect
- [ ] **URL langsung** — Tanpa login, akses `/dashboard` → diarahkan ke `/login`
- [ ] **6 role tersedia** — Pastikan role berikut ada di database: `Super Admin`, `Admin`, `Kepala Sekolah`, `Guru`, `Siswa`, `Ortu`

---

## Skenario Edge Case

- [ ] **Email tidak terdaftar** — Login dengan email yang tidak ada di database → pesan error yang tepat
- [ ] **Password dikosongkan** — Submit form login tanpa password → validasi frontend/backend mencegah request
- [ ] **Akun tanpa role** — Jika ada user tanpa role yang ter-assign → sistem tidak crash, tampilkan halaman yang informatif
- [ ] **Concurrent session** — Login dari dua browser berbeda dengan akun yang sama → periksa tidak ada konflik data

---

## Bug Ditemukan (isi saat testing)

| Tanggal | Deskripsi Bug | Tingkat Keparahan | Status |
|---|---|---|---|
| | | | |
