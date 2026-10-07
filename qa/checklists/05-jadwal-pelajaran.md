# Checklist QA — Jadwal Pelajaran (Timetable)

> Sumber skenario: `docs/prompt-modul-jadwal-pelajaran.md` (bagian validasi bentrok), kode `app/Http/Controllers/Timetable/`, migration `schedules`, `time_slots`, `rooms`, `academic_days`.

## Status: [ ] Belum diuji / [ ] Sedang diuji / [ ] Lulus / [ ] Ada bug

---

## Skenario Fungsional

- [ ] **Setting Hari Aktif** — Admin mengaktifkan/menonaktifkan hari sekolah (Senin-Sabtu) → tersimpan di `academic_days`
- [ ] **Setting Jam Pelajaran (Time Slot)** — Admin tambah jam pelajaran (misal 07:00–07:45) → tersimpan di `time_slots`
- [ ] **Manajemen Ruangan** — Admin tambah, edit, hapus ruangan → tersimpan di `rooms`
- [ ] **Buat Jadwal** — Admin membuat jadwal: Senin, jam ke-1, Kelas X-A, Mapel MTK, Guru A, Ruang 101 → tersimpan di `schedules`
- [ ] **Tampil Grid Jadwal Guru** — Login sebagai Guru → buka halaman jadwal → grid mingguan tampil hanya jadwal guru tersebut
- [ ] **Tampil Grid Jadwal Siswa** — Login sebagai Siswa → buka halaman jadwal → grid mingguan tampil hanya jadwal kelas siswa tersebut
- [ ] **Hari Libur Ditandai di Grid** — Jika ada event `is_holiday = true` di kalender akademik, grid jadwal untuk hari itu tidak menampilkan jadwal (menggunakan `AcademicCalendarService::isHoliday()`)

---

## Skenario Batasan Akses (Role)

- [ ] **Siswa hanya lihat jadwal kelasnya** — Login sebagai Siswa, akses jadwal → hanya tampil jadwal kelas yang diikuti siswa tersebut, bukan jadwal seluruh sekolah
- [ ] **Guru hanya lihat jadwal miliknya** — Login sebagai Guru, akses jadwal → hanya tampil jadwal yang dia mengajar
- [ ] **Siswa/Guru tidak bisa edit jadwal** — Tidak ada tombol Edit/Hapus jadwal di halaman jadwal siswa/guru

---

## Skenario Bentrok (WAJIB — Ini inti validasi modul ini)

- [ ] **Bentrok Guru** — Coba assign Guru A di Senin jam ke-1 untuk dua kelas berbeda secara bersamaan → sistem MENOLAK dengan pesan "Guru sudah terjadwal di slot ini"
- [ ] **Bentrok Kelas** — Coba assign Kelas X-A di Senin jam ke-1 untuk dua mapel berbeda → sistem MENOLAK dengan pesan "Kelas sudah ada jadwal di slot ini"
- [ ] **Bentrok Ruangan** — Coba assign Ruang 101 di Senin jam ke-1 untuk dua kelas berbeda → sistem MENOLAK dengan pesan "Ruangan sudah terpakai di slot ini"
- [ ] **Edit jadwal tidak menyebabkan bentrok sendiri** — Guru mengedit jadwalnya sendiri (tanpa mengubah slot) → sistem tidak mendeteksi sebagai bentrok

---

## Skenario Edge Case

- [ ] **Hari tidak aktif tidak bisa dijadwalkan** — Coba buat jadwal di hari yang tidak aktif (misal Sabtu jika Sabtu nonaktif) → sistem menolak atau tidak menampilkan hari tersebut
- [ ] **Time slot yang dihapus** — Jika time slot dihapus sementara ada jadwal yang menggunakannya → sistem tidak crash saat menampilkan jadwal
- [ ] **Tidak ada jadwal** — Jika belum ada jadwal sama sekali, halaman grid tampil kosong (bukan error)

---

## Bug Ditemukan (isi saat testing)

| Tanggal | Deskripsi Bug | Tingkat Keparahan | Status |
|---|---|---|---|
| | | | |
