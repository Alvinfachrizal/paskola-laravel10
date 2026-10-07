# Checklist QA — Nilai & Rapor (Grading)

> Sumber skenario: `docs/prompt-modul-ujian-rapor.md`, kode `app/Policies/StudentGradePolicy.php`, `app/Policies/ReportCardPolicy.php`, migration `grade_weights`, `student_grades`, `report_cards`, `grade_change_logs`.

## Status: [ ] Belum diuji / [ ] Sedang diuji / [ ] Lulus / [ ] Ada bug

---

## Skenario Fungsional — Bobot Nilai

- [ ] **Admin/Guru set bobot nilai** — Set bobot UTS=30%, UAS=40%, Tugas=30% untuk satu mapel di satu semester → total 100% → tersimpan di `grade_weights`
- [ ] **Bobot tidak bisa melebihi 100%** — Coba set bobot yang totalnya melebihi 100% → sistem menolak dengan validasi
- [ ] **Bobot berlaku per mapel per semester** — Bobot Matematika Semester 1 berbeda dari Matematika Semester 2 → tersimpan terpisah

## Skenario Fungsional — Input & Kalkulasi Nilai

- [ ] **Guru input nilai** — Guru input nilai UTS untuk satu siswa di satu mapel → tersimpan di `student_grades`
- [ ] **Kalkulasi nilai akhir otomatis** — Setelah semua komponen diinput, nilai akhir dihitung otomatis menggunakan bobot yang sudah diset
- [ ] **Recalculate saat bobot berubah** — Jika bobot diubah, nilai akhir semua siswa di mapel tersebut ikut dihitung ulang

## Skenario Fungsional — Verifikasi & Publish Rapor

- [ ] **Verifikasi oleh Wali Kelas** — Wali Kelas bisa mengubah status rapor dari `draft` → `terverifikasi`
- [ ] **Publish oleh Admin/Kepsek** — Admin/Kepsek mengubah status rapor dari `terverifikasi` → `published`
- [ ] **Siswa lihat rapor** — Login sebagai Siswa, buka halaman rapor → hanya muncul jika status `published`
- [ ] **Ortu lihat rapor anak** — Login sebagai Ortu, buka halaman rapor → hanya muncul rapor anak yang terkait
- [ ] **Audit log perubahan nilai** — Setiap perubahan nilai tercatat di `grade_change_logs` (siapa yang ubah, nilai lama, nilai baru, kapan)

---

## Skenario Batasan Akses (Role)
*(Diambil dari kode `StudentGradePolicy.php` dan `ReportCardPolicy.php`)*

- [ ] **Guru mapel hanya input nilai mapelnya sendiri** — Guru Matematika tidak bisa mengakses form input nilai Fisika → 403 dari `StudentGradePolicy`
- [ ] **Guru mapel hanya input nilai kelasnya sendiri** — Guru yang mengajar Kelas X-A tidak bisa input nilai untuk siswa Kelas X-B → 403
- [ ] **Wali Kelas tidak bisa ubah nilai langsung** — Login sebagai wali kelas (bukan guru mapel), coba edit nilai siswa di kelasnya → 403, hanya bisa verifikasi status rapor
- [ ] **Siswa tidak bisa akses `student_grades` langsung** — Akses `/grades/input` atau endpoint terkait sebagai Siswa → 403
- [ ] **Siswa hanya lihat rapor dirinya sendiri** — Login sebagai Siswa A, coba akses URL rapor Siswa B → 403
- [ ] **Rapor belum published tidak kelihatan siswa** — Rapor berstatus `draft` atau `terverifikasi` → tidak muncul di halaman siswa
- [ ] **Admin tidak bisa publish tanpa diverifikasi wali kelas terlebih dahulu** — Coba publish rapor yang masih `draft` → sistem menolak atau memperingatkan

---

## Skenario Edge Case

- [ ] **Nilai diinput sebelum bobot diset** — Jika bobot belum ada, nilai akhir 0 atau sistem memperingatkan
- [ ] **Komponen nilai tidak lengkap** — UTS diinput tapi UAS belum → kalkulasi nilai akhir tidak crash
- [ ] **Rapor published tidak bisa diubah nilainya** — Coba edit nilai saat rapor sudah published → sistem menolak

---

## Bug Ditemukan (isi saat testing)

| Tanggal | Deskripsi Bug | Tingkat Keparahan | Status |
|---|---|---|---|
| | | | |
