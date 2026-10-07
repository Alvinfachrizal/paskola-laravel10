# QA Folder — Panduan Penggunaan

> Folder `qa/` adalah pusat dokumentasi pengujian dan kesiapan deployment untuk project **Paskola SIMS**. Folder ini bersifat **dokumentasi murni** — tidak ada kode aplikasi di sini.

---

## Struktur Folder

```
qa/
├── RULES.md                          ← Baca ini PERTAMA. Aturan kerja di folder ini.
├── README.md                         ← File ini. Panduan penggunaan.
├── role-access-matrix.md             ← Tabel siapa bisa akses apa (berdasarkan role)
│
├── checklists/                       ← Checklist per modul
│   ├── 00-performance-n-plus-1.md   ← Pengujian query berlebih (N+1)
│   ├── 01-auth-dan-role.md
│   ├── 02-data-dasar.md
│   ├── 03-lms.md
│   ├── 04-kalender-akademik.md
│   ├── 05-jadwal-pelajaran.md
│   ├── 06-nilai-rapor.md
│   ├── 07-ppdb.md
│   ├── 08-keuangan-sekolah.md
│   └── 09-ujian-online.md
│
└── deployment-readiness/             ← Dokumen kesiapan go-live
    ├── checklist.md                  ← Checklist 8 tahap sebelum launching
    ├── environment-checklist.md      ← Perbandingan .env dev vs production
    └── backup-rollback-plan.md       ← Rencana backup & cara kembali jika ada masalah
```

---

## Urutan Membaca & Mengisi

### Untuk Memulai Testing:
1. Baca **`RULES.md`** — pahami batasan apa yang boleh dan tidak boleh dilakukan selama QA
2. Baca **`role-access-matrix.md`** — pahami siapa bisa akses apa, ini dasar dari hampir semua skenario batasan akses
3. Buka checklist modul **satu per satu**, mulai dari `01-auth-dan-role.md` lalu lanjut berurutan
4. Isi tabel "Bug Ditemukan" di setiap checklist jika menemukan masalah
5. Ubah **Status** di bagian atas setiap checklist menjadi `[x] Lulus` jika semua skenario berhasil diuji

### Untuk Pengujian Performa:
- Install `barryvdh/laravel-debugbar` (`composer require barryvdh/laravel-debugbar --dev`) terlebih dahulu
- Isi tabel di `00-performance-n-plus-1.md` sambil membuka halaman-halaman yang disebutkan
- **Jangan perbaiki N+1 di sesi ini** — catat temuannya saja

### Untuk Persiapan Go-Live:
- Buka `deployment-readiness/checklist.md` dan centang satu per satu
- Isi variabel di `environment-checklist.md` dengan nilai production yang sesungguhnya
- Pastikan `backup-rollback-plan.md` sudah dipahami sebelum deployment pertama

---

## Siapa yang Mengisi Checklist?

| Dokumen | Siapa yang Mengisi |
|---|---|
| Checklist modul (01–09) | Pengembang atau tim QA (bisa dilakukan sendiri jika belum ada tim QA) |
| `00-performance-n-plus-1.md` | Pengembang (butuh akses ke Debugbar) |
| `role-access-matrix.md` | Pengembang yang melakukan pengujian akses |
| `deployment-readiness/checklist.md` | Pengembang + Kepala Sekolah/Admin Sekolah (beberapa tahap perlu keputusan non-teknis) |
| `backup-rollback-plan.md` | Pengembang yang isi bagian teknis; Kepala Sekolah/IT sekolah yang isi kontak darurat |

---

## Kapan Folder Ini Dianggap "Lulus"?

Folder QA dianggap lulus dan project siap go-live ketika:

1. ✅ **Semua checklist modul** (01–09) berstatus **"Lulus"**
2. ✅ **Tidak ada bug terbuka** yang berstatus belum selesai di tabel "Bug Ditemukan"
3. ✅ **Semua baris** di `role-access-matrix.md` sudah diuji (**Y**)
4. ✅ **Tidak ada temuan N+1** yang belum diperbaiki di `00-performance-n-plus-1.md`
5. ✅ **Semua 8 tahap** di `deployment-readiness/checklist.md` sudah di-centang

---

## Ringkasan Status QA Saat Ini

| Modul | Checklist | Status Saat Ini |
|---|---|---|
| Auth & Role | `01-auth-dan-role.md` | ⬜ Belum diuji |
| Data Master | `02-data-dasar.md` | ⬜ Belum diuji |
| LMS | `03-lms.md` | ⬜ Belum diuji |
| Kalender Akademik | `04-kalender-akademik.md` | ⬜ Belum diuji |
| Jadwal Pelajaran | `05-jadwal-pelajaran.md` | ⬜ Belum diuji |
| Nilai & Rapor | `06-nilai-rapor.md` | ⬜ Belum diuji |
| PPDB Online | `07-ppdb.md` | ⬜ Belum diuji |
| Keuangan Sekolah | `08-keuangan-sekolah.md` | ⬜ Belum diuji |
| Ujian Online | `09-ujian-online.md` | ⬜ Belum diuji |
| Performa (N+1) | `00-performance-n-plus-1.md` | ⬜ Belum diuji |

---

## Catatan untuk Pengembang: Modul yang Cheklist-nya Masih Dangkal

Berdasarkan kelengkapan dokumentasi di folder `docs/`:

- **LMS (`03-lms.md`)**: Tidak ada file dokumentasi khusus LMS di `docs/` (tidak ada `docs/lms.md`). Checklist disusun berdasarkan pembacaan kode controller langsung. Jika ada logika khusus LMS yang tidak tercakup, perlu ditambahkan manual.
- **Dashboard (`09-ujian-online.md` + terkait)**: Dokumentasi ujian online di `docs/ujian-online.md` singkat. Skenario keamanan kritis sudah ditambahkan berdasarkan spesifikasi di `ExamPolicy.php`.

---

## Pekerjaan Lanjutan Setelah QA Selesai

*(Diisi setelah testing dilakukan — catat temuan N+1 dan bug yang perlu perbaikan di sesi terpisah)*

| Prioritas | Temuan | File Terkait | Jenis Perbaikan |
|---|---|---|---|
| | | | |
