# 🧠 Project Lessons & Knowledge Base (Paskola SIMS)

Dokumen ini mencatat pola arsitektur, pembelajaran teknis, dan standar implementasi yang berhasil diterapkan agar konsistensi terjaga di seluruh sesi pengerjaan proyek.

---

## 📌 Modul F: Pengumuman & Web Push PWA

### 1. Arsitektur Progressive Web App (PWA) 100% Standar Google
- **Cakupan Global (Bukan Hanya Auth Layout)**:
  - File `manifest.json`, meta tag PWA (`apple-mobile-web-app-capable`, `theme-color`), dan registrasi Service Worker (`sw.js`) **wajib terpasang di semua titik masuk**, termasuk halaman Login (`auth/login.blade.php`), Guest (`layouts/guest.blade.php`), dan portal publik (`ppdb/layout.blade.php`).
  - *Alasan*: Pengguna HP pertama kali mengakses web via link belum login. Jika manifest hanya ada di dashboard, browser HP tidak akan mendeteksi aplikasi sebagai PWA.
- **Konfigurasi `start_url`**:
  - Gunakan `start_url: "/"` (bukan `"/dashboard"`). Ini menjamin bahwa ketika session login habis, pembukaan PWA dari layar utama HP tetap berjalan mulus dan diarahkan ke routing auth tanpa redirect loop.
- **Dukungan Dual-Platform (Android & iOS Safari)**:
  - *Android/Desktop*: Menangkap event browser `beforeinstallprompt` untuk memunculkan tombol custom "Pasang Aplikasi".
  - *iOS Safari*: Tidak memiliki prompt otomatis; wajib menyediakan panduan visual Share ("Bagikan" ➔ "Tambahkan ke Layar Utama"). Notifikasi Web Push di iOS (iOS 16.4+) hanya aktif jika web sudah berada di Home Screen.
  - *Storage Cooldown*: Gunakan `localStorage` untuk menyimpan waktu dismissal (7 hari untuk install prompt, 3 hari untuk notifikasi prompt) agar tidak mengganggu pengalaman pengguna.

### 2. Implementasi Web Push (VAPID) Berdiri Sendiri (Pure PHP)
- **Solusi Anti-Gagal Instalasi Vendor**:
  - Mengirim Web Push Notification dapat dilakukan menggunakan native PHP OpenSSL (`prime256v1` ECDSA) via `WebPushService` tanpa bergantung pada package eksternal yang rentan terhadap masalah SSL certificate / curl error 60 di lingkungan lokal Windows / Laragon.
  - Artisan command `php artisan webpush:generatekeys` mandiri digunakan untuk membuat pasangan kunci EC P-256 dan otomatis memperbarui `.env`.
- **Indeks Endpoint MySQL**:
  - Kolom `endpoint` dari push subscription browser berbentuk URL panjang (`TEXT`). MySQL tidak mengizinkan unique index pada tipe `TEXT` tanpa key length.
  - *Solusi elegan*: Gunakan virtual column `endpoint_hash VARCHAR(64) as (SHA2(endpoint, 256))` untuk unique index gabungan `[user_id, endpoint_hash]`.

### 3. Service Worker & Offline UX
- **Strategi Caching**:
  - Navigasi halaman HTML: *Network-First* dengan fallback ke cache, lalu ke `public/offline.html`.
  - Aset statis (ikon, manifest): *Cache-First*.
  - Menampilkan indikator pill melayang jika koneksi internet terputus saat berselancar di aplikasi.
- **Background Event Push**:
  - Event `notificationclick` memeriksa tab yang sedang terbuka (`clients.matchAll`), memfokuskan jendela yang sudah ada (*focus & navigate*), atau membuka jendela baru jika aplikasi tertutup.

### 4. Standar Testing Laravel
- **CSRF Token di Feature Tests**:
  - Untuk request POST pengujian form Blade, selalu gunakan `$this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)` pada `setUp()` untuk menghindari error 419 (CSRF mismatch).
- **Integritas Database**:
  - Gunakan trait `DatabaseTransactions` alih-alih `RefreshDatabase` saat pengujian dilakukan terhadap database lokal (MySQL) yang sudah berisi data seeder lengkap, sehingga data tidak terhapus setelah pengujian selesai.

