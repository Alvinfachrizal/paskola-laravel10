# Aturan Kerja — Folder QA

Aturan ini berlaku untuk siapa pun (manusia atau AI) yang bekerja di folder `qa/`.

## 1. QA bersifat READ-ONLY terhadap kode aplikasi
- DILARANG mengubah, menambah, atau menghapus file apa pun di luar folder `qa/` — ini termasuk `app/`, `routes/`, `resources/`, `database/migrations/`, `config/`, `.env`, dan file manapun yang bukan bagian dari dokumentasi QA
- Kalau menemukan bug atau masalah performa (termasuk N+1), CATAT di checklist, JANGAN diperbaiki langsung di sesi ini
- Perbaikan kode adalah pekerjaan terpisah, dikerjakan lewat prompt/sesi lain yang secara eksplisit ditujukan untuk itu

## 2. Tidak ada perintah yang mengubah data
- DILARANG menjalankan `php artisan migrate:fresh`, `migrate:rollback`, `db:wipe`, atau perintah lain yang menghapus/mengubah struktur maupun isi database yang sedang dipakai untuk development, KECUALI secara eksplisit diminta dan dikonfirmasi ulang oleh saya di pesan itu juga
- Kalau QA butuh mencoba sesuatu yang berisiko mengubah data (misal untuk mengetes skenario tertentu), gunakan database/environment terpisah khusus testing, atau minta saya konfirmasi dulu sebelum menjalankan apa pun yang berisiko

## 3. Tidak ada commit di luar folder qa/
- Kalau memakai git, commit yang dibuat selama sesi QA hanya boleh berisi perubahan di dalam folder `qa/`
- Kalau secara tidak sengaja ada file lain yang berubah (misal karena menjalankan sebuah command), JANGAN di-commit — laporkan ke saya dulu apa yang berubah dan kenapa

## 4. Kalau ragu, berhenti dan tanya
- Kalau suatu langkah di prompt tampak membutuhkan perubahan kode aplikasi untuk bisa dilanjutkan (bukan cuma dokumentasi), STOP, jangan dikerjakan, jelaskan ke saya situasinya dan tunggu instruksi
