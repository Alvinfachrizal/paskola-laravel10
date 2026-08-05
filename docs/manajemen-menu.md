# Dokumentasi: Modul Manajemen Menu (Toggle Modul)

Dibuat: 2026-08-04 | Terakhir diperbarui: 2026-08-04

---

## 1. Tujuan

Halaman **Pengaturan Modul** memungkinkan Superadmin mengaktifkan atau menonaktifkan modul-modul besar dalam sistem sesuai kebutuhan sekolah — tanpa menghapus data apapun.

---

## 2. Cara Akses

- Login sebagai **Super Admin**
- Klik **Pengaturan Modul** di sidebar (bagian bawah menu Admin)
- URL: `/settings/modules`

---

## 3. Daftar Modul & Dependency

| Key | Nama Tampilan | Core | Bergantung Pada | Keterangan |
|---|---|:---:|---|---|
| `auth` | Autentikasi | ✅ | — | Login/session — tidak bisa dimatikan |
| `data_dasar` | Administrasi & Data Master | ✅ | — | Kelas, Mapel, Tahun Ajaran, Guru, Siswa |
| `dashboard` | Dashboard | ✅ | — | Halaman utama semua role |
| `kalender_akademik` | Kalender Akademik | ❌ | — | Dibutuhkan oleh Jadwal Pelajaran |
| `jadwal_pelajaran` | Jadwal Pelajaran | ❌ | `kalender_akademik` | Harus matikan Jadwal dulu sebelum Kalender |
| `lms` | LMS (Materi & Tugas) | ❌ | — | Mandiri |
| `nilai_rapor` | Nilai & Rapor | ❌ | — | Mandiri |
| `ppdb` | PPDB Online | ❌ | — | Dibutuhkan oleh Keuangan Sekolah |
| `keuangan_sekolah` | Keuangan Sekolah | ❌ | `ppdb` | Harus matikan Keuangan dulu sebelum PPDB |

---

## 4. Aturan Toggle

1. **Modul core** (tanda 🔴 di UI) — Toggle-nya disabled, tidak bisa disentuh.
2. **Menonaktifkan modul** yang masih punya dependent aktif → **ditolak keras** dengan pesan yang menyebutkan nama modul yang bergantung.
3. **Menonaktifkan modul** — data TIDAK dihapus, hanya akses diblokir di route-level dan link menu disembunyikan.

---

## 5. Cara Kerja Teknis

```
Toggle diSubmit (POST) → ModuleController@toggle
  → ModuleService::toggle($module)
    → jika ingin nonaktifkan: canDeactivate() → cek is_core, cek dependents aktif
    → jika aman: update is_active, hapus cache
  → back() + flash message
```

### File-file Utama

| File | Fungsi |
|---|---|
| `app/Models/Module.php` | Model dengan relasi `dependencies()` dan `dependents()` |
| `app/Services/ModuleService.php` | Logic toggle, cache, validasi dependency |
| `app/Http/Middleware/EnsureModuleActive.php` | Blokir route jika modul nonaktif |
| `app/Http/Controllers/Settings/ModuleController.php` | Controller untuk halaman settings |
| `resources/views/settings/modules/index.blade.php` | UI toggle card per modul |
| `resources/views/settings/modules/disabled.blade.php` | Halaman "Modul Tidak Aktif" |
| `database/seeders/ModuleSeeder.php` | Seed data modul & dependency |

---

## 6. Cache

Status modul di-cache dengan key `modules_status` selama **1 jam**. Cache otomatis dihapus setiap kali ada toggle status oleh superadmin. Ini memastikan tidak ada query DB di setiap request sidebar/middleware.

---

## 7. Menambah Modul Baru (Panduan untuk Developer)

Setiap kali modul besar baru dibangun, **wajib** lakukan langkah berikut:

### a. Tambahkan baris ke `ModuleSeeder.php`
```php
[
    'key'         => 'nama_modul',    // lowercase, underscore
    'name'        => 'Nama Tampilan',
    'description' => 'Keterangan singkat fungsi modul ini.',
    'icon'        => 'bi-icon-name',  // Bootstrap Icons
    'is_core'     => false,
    'is_active'   => true,
    'sort_order'  => 10,              // urutan tampil di UI
],
```

### b. Jika ada dependency, tambahkan ke array `$dependencies`
```php
['module' => 'modul_baru', 'depends_on' => 'modul_yang_dibutuhkan'],
```

### c. Jalankan seeder
```bash
php artisan db:seed --class=ModuleSeeder
```

### d. Terapkan middleware ke route group
```php
Route::middleware(['auth', 'module.active:nama_modul'])->...
```

### e. Update sidebar di `navigation-bootstrap.blade.php`
```blade
@if($moduleStatus['nama_modul'] ?? true)
    <a href="{{ route('nama.route') }}" ...>Menu Baru</a>
@endif
```

### f. Update dokumen ini (`docs/manajemen-menu.md`)
Tambahkan baris baru ke Tabel di bagian 3.

---

## 8. Skenario Test Manual

| Skenario | Langkah | Hasil yang Diharapkan |
|---|---|---|
| Nonaktifkan Auth | Toggle Auth OFF | Tombol disabled, tidak bisa diklik |
| Nonaktifkan Kalender saat Jadwal aktif | Toggle Kalender OFF | Flash error: "Nonaktifkan Jadwal Pelajaran dulu" |
| Nonaktifkan PPDB saat Keuangan aktif | Toggle PPDB OFF | Flash error: "Nonaktifkan Keuangan Sekolah dulu" |
| Nonaktifkan LMS | Toggle LMS OFF | Berhasil; menu LMS hilang dari sidebar; akses URL `/admin/lms-*` redirect ke halaman nonaktif |
| Aktifkan kembali LMS | Toggle LMS ON | Berhasil; menu LMS muncul kembali; URL bisa diakses |
