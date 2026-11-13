# 📢 Dokumentasi Modul F: Pengumuman & Notifikasi (Web Push PWA)

## 📌 Ringkasan Modul
Modul Pengumuman & Notifikasi berfungsi sebagai pusat broadcast informasi dari sekolah (Admin, Kepala Sekolah, atau Guru) ke seluruh warga sekolah (Guru, Siswa, dan Orang Tua). Modul ini dilengkapi dengan dukungan **Web Push Notification (PWA)** sehingga pengguna dapat menerima notifikasi instan langsung di layar HP/browser mereka tanpa perlu aplikasi native.

---

## 🗄️ 1. Skema Database

### Tabel `announcements`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | UUID (PK) | Identifier pengumuman |
| `school_id` | UUID (FK -> schools) | Sekolah pemilik pengumuman |
| `author_id` | UUID (FK -> users) | Pembuat pengumuman |
| `title` | VARCHAR(255) | Judul pengumuman |
| `content` | TEXT | Isi lengkap pengumuman |
| `target_roles` | JSON (nullable) | Filter role: `["Guru","Siswa","Orang Tua"]` (null = semua) |
| `target_class_ids` | JSON (nullable) | Filter kelas spesifik (null = semua kelas) |
| `is_pinned` | BOOLEAN | Disematkan di paling atas |
| `priority` | VARCHAR(20) | `normal` atau `urgent` |
| `published_at` | TIMESTAMP (nullable) | Tanggal publikasi (null = Draft) |
| `expires_at` | TIMESTAMP (nullable) | Batas waktu tampil (opsional) |
| `created_at` / `updated_at` | TIMESTAMP | Audit waktu pembuatan/perubahan |

### Tabel `push_subscriptions`
Menyimpan token push subscriber per browser/perangkat:
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGSERIAL (PK) | Identifier langganan |
| `user_id` | UUID (FK -> users) | Pengguna pemilik subscription |
| `endpoint` | TEXT | Push endpoint dari browser (FCM/Mozilla/Apple) |
| `endpoint_hash` | VARCHAR(64) | SHA-256 virtual generated column untuk unique index |
| `public_key` | VARCHAR(255) | P-256 DH public key dari browser |
| `auth_token` | VARCHAR(255) | Auth secret key dari browser |

### Tabel `announcement_reads`
Tracking apakah pengumuman sudah dibaca oleh user tertentu:
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGSERIAL (PK) | Identifier read log |
| `announcement_id` | UUID (FK -> announcements) | ID Pengumuman |
| `user_id` | UUID (FK -> users) | ID Pengguna yang membaca |
| `read_at` | TIMESTAMP | Waktu pertama kali dibaca |

---

## 🔐 2. Hak Akses & RBAC (`AnnouncementPolicy`)

| Role | Lihat Daftar | Buat Baru | Edit / Hapus | Publish / Unpublish |
|---|---|---|---|---|
| **Super Admin / Admin** | Semua (Draft & Published) | ✅ Semua target | ✅ Semua pengumuman | ✅ |
| **Kepala Sekolah** | Semua (Draft & Published) | ✅ Semua target | ✅ Semua pengumuman | ✅ |
| **Guru** | Miliknya sendiri & Published target Guru | ✅ Target Siswa/Ortu di kelasnya | ✅ Hanya miliknya yang masih Draft | ✅ Miliknya sendiri |
| **Siswa & Orang Tua** | Hanya yang **Published** & sesuai role/kelas | ❌ | ❌ | ❌ |

---

## 📱 3. Arsitektur Web Push & PWA (100% Ready)

### 3.1 Komponen PWA Lengkap
1. **Web App Manifest (`public/manifest.json`)**:
   - `id`: `paskola-sims-app`
   - `start_url`: `/` (aman dari session timeout & mendukung routing guest/auth)
   - `display`: `standalone`
   - Dukungan ikon maskable & standard (192x192 & 512x512)
   - App shortcuts: Akses cepat ke Dashboard & Pengumuman
2. **Service Worker (`public/sw.js` v2.0.0)**:
   - Pre-caching asset penting (ikon, manifest, offline.html)
   - Strategi `Network-First with Cache Fallback` untuk navigasi halaman
   - Background push handler + action buttons ("Lihat Detail", "Tutup")
   - Focus tab aktif atau buka tab baru saat notifikasi diklik
3. **PWA Install Prompt UI (`resources/views/components/pwa-prompt.blade.php`)**:
   - **Android / Desktop Chrome**: Menangkap event `beforeinstallprompt` dan memunculkan floating card *"Pasang Aplikasi Paskola"*.
   - **iOS Safari (iPhone/iPad)**: Deteksi otomatis Safari iOS dan memunculkan petunjuk visual *"Tekan tombol Bagikan (Share) lalu pilih 'Tambahkan ke Layar Utama'"*.
   - **Offline Indicator**: Pill floating otomatis muncul jika jaringan pengguna putus saat berselancar di aplikasi.
4. **Push Notification Permission Banner (`resources/views/components/pwa-push-permission.blade.php`)**:
   - Muncul otomatis di bagian atas halaman (Dashboard, dsb.) bagi pengguna yang belum mengaktifkan izin notifikasi.

### 3.2 Alur Notifikasi Web Push
```
[Browser User / PWA]
     │
     ├─ 1. Registrasi Service Worker (`/sw.js`) & PWA Manifest (`/manifest.json`)
     ├─ 2. Permintaan Izin Notifikasi (Notification.requestPermission())
     ├─ 3. Kirim Subscription ke POST `/push/subscribe`
     │
[Server Laravel]
     │
     ├─ 4. Pengumuman dibuat & dipublish (`AnnouncementController@publish` / `@store`)
     ├─ 5. `WebPushService::sendToAnnouncement($announcement)` dipanggil via background queue/afterResponse
     ├─ 6. Kirim payload JSON ke Web Push Endpoint menggunakan autentikasi VAPID (RFC 8292)
     │
[Push Service (Google FCM / Mozilla / Apple)]
     │
     ▼
[Notifikasi Muncul di Layar HP Pengguna]
     │
     └─ 7. User klik notifikasi ➔ SW membuka tab `/announcements/{id}`
```

### VAPID Keys Setup
Dapat digenerate ulang kapan saja menggunakan perintah artisan:
```bash
php artisan webpush:generatekeys
```
Kunci disimpan di `.env`:
- `VAPID_PUBLIC_KEY`
- `VAPID_PRIVATE_KEY`
- `VAPID_SUBJECT`

---

## 🌐 4. Endpoint & Routes

| Method | URI | Name | Middleware / Access |
|---|---|---|---|
| `GET` | `/announcements` | `announcements.index` | Auth, module.active:pengumuman |
| `GET` | `/announcements/create` | `announcements.create` | Admin, Kepsek, Guru |
| `POST` | `/announcements` | `announcements.store` | Admin, Kepsek, Guru |
| `GET` | `/announcements/{announcement}` | `announcements.show` | Auth (Auto mark-read) |
| `GET` | `/announcements/{announcement}/edit` | `announcements.edit` | Admin, Kepsek, Guru (Author) |
| `PUT` | `/announcements/{announcement}` | `announcements.update` | Admin, Kepsek, Guru (Author) |
| `DELETE` | `/announcements/{announcement}` | `announcements.destroy` | Admin, Kepsek, Guru (Author) |
| `POST` | `/announcements/{announcement}/publish` | `announcements.publish` | Admin, Kepsek, Guru (Author) |
| `POST` | `/announcements/{announcement}/unpublish` | `announcements.unpublish` | Admin, Kepsek, Guru (Author) |
| `GET` | `/announcements/api/unread-count` | `announcements.unread-count` | Auth (Polling badge unread) |
| `POST` | `/push/subscribe` | `push.subscribe` | Auth |
| `POST` | `/push/unsubscribe` | `push.unsubscribe` | Auth |
| `GET` | `/push/status` | `push.status` | Auth |

---

## 🧪 5. Testing & Verifikasi

### Test PWA (`tests/Feature/PwaTest.php`)
```bash
php artisan test --filter=PwaTest
```
- ✅ `test_manifest_file_is_accessible_and_valid`
- ✅ `test_service_worker_file_exists_and_contains_push_logic`
- ✅ `test_offline_page_exists`
- ✅ `test_login_page_renders_pwa_meta_tags`
- ✅ `test_authenticated_layout_renders_pwa_meta_and_prompts`

### Test Pengumuman & Push API (`tests/Feature/AnnouncementTest.php`)
```bash
php artisan test --filter=AnnouncementTest
```

Cakupan pengujian:
1. `test_user_can_view_announcements_index` — Memastikan halaman index render normal (HTTP 200).
2. `test_admin_can_create_and_publish_announcement` — Verifikasi Admin bisa buat pengumuman published.
3. `test_guru_can_create_announcement` — Verifikasi Guru bisa buat pengumuman.
4. `test_siswa_cannot_create_announcement` — Verifikasi proteksi RBAC Policy (HTTP 403 untuk Siswa).
5. `test_siswa_cannot_view_draft_announcement` — Memastikan draft tidak bisa diintip oleh Siswa.
6. `test_announcement_auto_marks_as_read_when_viewed` — Memastikan status baca tercatat otomatis.
7. `test_unread_count_api_works` — Memastikan endpoint badge notifikasi menghitung sisa belum dibaca.
8. `test_push_subscription_store_and_destroy` — Memastikan API langganan notifikasi web push berfungsi dengan baik.

