# Checklist QA — Kalender Akademik

> Sumber skenario: `docs/kalender-akademik.md` (bagian "Cara Test Manual" dan "RBAC"), kode `AcademicEventPolicy.php`, `StoreAcademicEventRequest.php`, `AcademicCalendarService.php`. Unit test sudah ada di `tests/Unit/AcademicCalendarServiceTest.php` (5 skenario, semua lulus).

## Status: [ ] Belum diuji / [ ] Sedang diuji / [ ] Lulus / [ ] Ada bug

---

## Skenario Fungsional

- [ ] **Tampil kalender bulanan** — Buka `/calendar` → grid bulanan tampil dengan navigasi ke bulan sebelumnya/berikutnya
- [ ] **Event school-wide muncul di semua kelas** — Admin buat event tanpa memilih kelas → event tampil di kalender semua kelas
- [ ] **Event per kelas terbatas** — Admin buat event khusus Kelas X-A → event hanya tampil saat filter kelas X-A, tidak tampil di kelas lain
- [ ] **Kategori dengan warna berbeda** — Setiap event tampil dengan warna sesuai kategori yang dipilih (hex color dari `event_categories`)
- [ ] **Event multi-hari tampil benar** — Buat event dengan `start_date` dan `end_date` berbeda → semua hari dalam rentang tersebut terisi di grid kalender
- [ ] **HUT RI (17 Agustus) dari seeder** — Navigasi ke bulan Agustus → event HUT RI muncul dengan kategori Hari Libur
- [ ] **CRUD kategori (Admin)** — Admin bisa tambah kategori baru, set warna, set `is_holiday` → tersimpan dan langsung bisa dipakai saat buat event

---

## Skenario Batasan Akses (Role)
*(Diambil dari `docs/kalender-akademik.md` tabel RBAC dan kode `AcademicEventPolicy.php`)*

- [ ] **Siswa/Ortu tidak ada tombol Tambah Event** — Login sebagai Siswa/Ortu → buka `/calendar` → tidak ada tombol "Tambah Event" sama sekali
- [ ] **Guru tidak bisa pilih kategori `is_holiday`** — Login sebagai Guru → buka modal Tambah Event → dropdown kategori tidak menampilkan kategori bertanda Hari Libur (divalidasi juga di `StoreAcademicEventRequest::withValidator()`)
- [ ] **Guru tidak bisa akses Kelola Kategori** — Akses `/calendar/categories` sebagai Guru → 403
- [ ] **Guru hanya bisa edit event miliknya** — Guru A mencoba edit event yang dibuat Guru B → 403

---

## Skenario Edge Case (Kritis — Integrasi dengan Jadwal Pelajaran)

- [ ] **`isHoliday()` mengembalikan `true` untuk hari libur school-wide** — Panggil service dengan tanggal libur nasional → `is_holiday: true`
- [ ] **`isHoliday()` mengembalikan `false` untuk hari biasa** — Panggil service dengan tanggal reguler → `is_holiday: false`
- [ ] **`isHoliday()` untuk kelas spesifik benar** — Event libur Kelas A tidak mempengaruhi Kelas B (`is_holiday: false` untuk kelas B)
- [ ] **Event non-holiday tidak terdeteksi sebagai libur** — Event bertipe "Ulangan Harian" (bukan hari libur) tidak mempengaruhi `isHoliday()`
- [ ] **Grid jadwal pelajaran tidak tampil di hari libur** — Buka jadwal pelajaran pada hari yang ada event `is_holiday = true` → jadwal tidak ditampilkan (integrasi `AcademicCalendarService`)

---

## Bug Ditemukan (isi saat testing)

| Tanggal | Deskripsi Bug | Tingkat Keparahan | Status |
|---|---|---|---|
| | | | |
