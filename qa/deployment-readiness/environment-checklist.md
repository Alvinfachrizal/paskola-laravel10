# Environment Configuration Checklist

> Dokumen ini membandingkan konfigurasi `.env` antara environment **Development** (komputer lokal/Laragon) dan **Production** (server hosting). Tujuannya agar tidak ada konfigurasi development yang terbawa ke production.
>
> Kolom "Nilai Production" yang bertanda `[ISI]` artinya Anda perlu mengisi dengan nilai yang spesifik ke hosting pilihan Anda.

---

## Perbandingan Konfigurasi `.env`

| Variabel | Nilai Development (Laragon) | Nilai Production | Catatan |
|---|---|---|---|
| `APP_NAME` | `Paskola` | `Paskola` atau nama sekolah | Nama yang tampil di tab browser |
| `APP_ENV` | `local` | `production` | **WAJIB** diubah. Nilai `local` mengaktifkan fitur debug yang berbahaya |
| `APP_DEBUG` | `true` | `false` | **WAJIB** `false`. Jika `true` di production, detail error (path file, isi variabel, dll) bisa terlihat pengguna |
| `APP_URL` | `http://localhost:8000` | `https://[domain-sekolah.com]` | URL harus HTTPS di production |
| `APP_KEY` | `base64:...` (generate lokal) | `base64:...` (generate baru di server) | Jalankan `php artisan key:generate` di server production. JANGAN copy dari development |
| `DB_CONNECTION` | `pgsql` | `pgsql` | Sama (PostgreSQL) |
| `DB_HOST` | `127.0.0.1` | `[ISI: host database hosting]` | Bisa berbeda di hosting (cek cPanel/panel hosting) |
| `DB_PORT` | `5432` | `5432` | Port default PostgreSQL, biasanya sama |
| `DB_DATABASE` | `paskola_dev` | `[ISI: nama database production]` | **HARUS** berbeda dari database development |
| `DB_USERNAME` | `postgres` | `[ISI: username database hosting]` | |
| `DB_PASSWORD` | (password lokal) | `[ISI: password database hosting]` | Gunakan password yang kuat dan unik |
| `SESSION_DRIVER` | `database` | `database` atau `redis` | Jika traffic tinggi, Redis lebih efisien |
| `SESSION_LIFETIME` | `120` | `120` | Dalam menit. Sesuaikan kebijakan sekolah |
| `CACHE_DRIVER` | `file` | `file` atau `redis` | |
| `QUEUE_CONNECTION` | `sync` | `sync` atau `database` | Jika ada fitur notifikasi email/notif, gunakan `database` |
| `MAIL_MAILER` | `log` | `[ISI: smtp]` | Development pakai `log` (email masuk ke log file). Production harus pakai SMTP sungguhan |
| `MAIL_HOST` | (tidak perlu) | `[ISI: smtp.provider.com]` | Misal: smtp.gmail.com, atau dari Mailtrap, Mailgun, Brevo |
| `MAIL_PORT` | (tidak perlu) | `587` | Port standar TLS |
| `MAIL_USERNAME` | (tidak perlu) | `[ISI: email pengirim]` | |
| `MAIL_PASSWORD` | (tidak perlu) | `[ISI: app password]` | Untuk Gmail, gunakan "App Password", bukan password utama |
| `MAIL_FROM_ADDRESS` | (tidak perlu) | `[ISI: noreply@domain-sekolah.com]` | Alamat pengirim yang tampil di email |
| `MAIL_FROM_NAME` | (tidak perlu) | `Paskola SIMS` | |
| `FILESYSTEM_DISK` | `local` | `local` atau `s3` | Jika file upload banyak (PPDB, LMS), pertimbangkan storage cloud seperti S3 |
| `LOG_CHANNEL` | `stack` | `stack` atau `daily` | `daily` membuat log terpisah per hari, lebih mudah dikelola |
| `LOG_LEVEL` | `debug` | `error` | Di production, hanya log error saja. Jangan `debug` karena log bisa sangat besar |

---

## Catatan Penting Tambahan

### Setelah Deploy ke Production, Jalankan:
```bash
# Optimasi untuk production (cache konfigurasi dan route)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Pastikan storage link sudah dibuat (untuk file upload)
php artisan storage:link
```

### Yang TIDAK Boleh Ada di `.env` Production:
- `APP_DEBUG=true`
- `APP_ENV=local`
- `DB_DATABASE` yang sama dengan development
- Akses credential database development

### Cara Generate APP_KEY Baru di Server:
```bash
php artisan key:generate
```
Jangan gunakan `APP_KEY` yang sama dengan development.
