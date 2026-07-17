# 🚀 Paskola - Sistem Informasi Manajemen Sekolah (SIMS) 

Dokumen ini berfungsi sebagai **Master Project Plan & Tracker** yang digunakan oleh AI (Gemini) untuk memastikan pengembangan proyek tetap terukur, sistematis, dan sesuai dengan rencana arsitektur awal tanpa melenceng (*scope creep*). 

**AI harus selalu mengacu pada dokumen ini sebagai pedoman sebelum membuat arsitektur atau perubahan besar.**

---

## 📌 1. Visi Produk & Arsitektur
- **Tujuan**: Platform terpusat untuk aktivitas akademik, administrasi, dan komunikasi sekolah untuk menggantikan proses manual.
- **Arsitektur (Baru)**: Monolith dengan `Laravel 10 + Blade`.
- **Styling (CSS)**: `Bootstrap 5 (CDN)` sebagai fondasi utama. Tailwind CSS opsional via CDN untuk halaman khusus.
- **Database**: `PostgreSQL`. Saat ini *single-tenant* (satu sekolah), namun struktur skema dirancang sedemikian rupa agar mendukung *multi-tenant* di masa depan (menggunakan tabel `schools`).

---

## 📋 2. Roadmap & Status Modul (Checklist Pengembangan)

Beri tanda `[x]` pada fitur yang sudah tuntas (Backend & Frontend) dan teruji sepenuhnya.

### A. Fondasi & Autentikasi (Core)
- [x] Desain Skema Database (ERD) -> `docs/database.md`
- [x] Inisialisasi Backend & Frontend (NestJS + Next.js)
- [x] Modul Autentikasi (Login multi-role, JWT, Refresh Token)
- [x] Role-Based Access Control (RBAC) (Super Admin, Admin Sekolah, Kepsek, Guru, Siswa, Ortu)

### B. Administrasi & Akademik (Master Data)
- [x] Skema Database Akademik & Profil
- [x] Manajemen Entitas Akademik (Tahun Ajaran, Semester, Jurusan, Kelas, Mata Pelajaran)
- [x] Manajemen Pengguna: Profil Guru dan Staf (Admin Dashboard)
- [x] Manajemen Pengguna: Data Induk Siswa & Wali (Admin Dashboard)

### C. Learning Management System (LMS)
- [x] Skema Database LMS (Materials, Assignments, Submissions)
- [x] API & UI Upload/Manajemen Materi Pembelajaran
- [x] API & UI Manajemen Tugas & Ujian Online
- [x] API & UI Pengumpulan Tugas Siswa (Submission) — modal kumpul, status cek, kumpul ulang
- [x] Penilaian & Riwayat Nilai Tugas (via student_grades + report_cards)
- [x] RBAC: Guru CRUD tugas/materi, lihat & nilai submission; Siswa hanya kumpul & lihat milik sendiri

### D. Kehadiran & Absensi (Attendance)
- [ ] Skema Database Absensi (Presensi Harian & Mata Pelajaran)
- [ ] API & UI Presensi oleh Guru (Hadir/Izin/Sakit/Alfa)
- [ ] API & UI Pengajuan Izin Online (Siswa/Ortu) & Approval Guru
- [ ] Rekap & Dashboard Kehadiran (Persentase)

### E. Manajemen Nilai & Rapor (Grading)
- [x] Skema Database: semesters, grade_weights, student_grades, report_cards, grade_change_logs
- [x] Bobot nilai per komponen per mapel per semester (validasi total 100%)
- [x] Input nilai manual oleh guru mapel (UTS, UAS, Praktik)
- [x] Kalkulasi Nilai Akhir Otomatis (berbobot, auto-recalculate)
- [x] Verifikasi Rapor oleh Wali Kelas (draft→terverifikasi)
- [x] Publish Rapor oleh Admin/Kepsek
- [x] Halaman Rapor Read-only untuk Siswa & Ortu (hanya status published)
- [x] Audit Log Perubahan Nilai (grade_change_logs)
- [x] Laravel Policy: StudentGradePolicy & ReportCardPolicy
- [x] RBAC Route: Guru input nilai & bobot; Siswa/Ortu hanya akses /rapor; publish hanya Admin/Kepsek
- [ ] Generate Rapor PDF/Cetak

### F. Komunikasi & Pengumuman
- [x] Skema Database `announcements` terbuat
- [ ] API & UI Broadcast Pengumuman (Sekolah -> Guru/Siswa/Ortu)
- [ ] Infrastruktur Notifikasi (Email / Push Notification via FCM / WhatsApp)
- [ ] (Opsional) Fitur Chat/Pesan Internal

### G. Keuangan Sekolah (Finance)
- [ ] Skema Database Tagihan & Pembayaran
- [ ] API & UI Manajemen Tagihan (SPP, Uang Gedung, dll)
- [ ] Integrasi Payment Gateway (Midtrans/Xendit)
- [ ] Riwayat & Status Pembayaran untuk Dashboard Ortu

### H. PPDB Online (Penerimaan Siswa Baru)
- [x] Landing Page Pendaftaran (Portal publik `/ppdb`) — `docs/ppdb.md`
- [x] Form Pendaftaran, Upload Dokumen, dan Data Seragam (Conditional JS)
- [x] Login ulang calon siswa (Kode Pendaftaran + Tanggal Lahir, tanpa OTP)
- [x] Dashboard Panitia: Verifikasi Dokumen per item
- [x] Dashboard Panitia: Input Nilai Seleksi + Override Status manual
- [x] Alur Daftar Ulang: otomatis buat akun `User` + data `Student`
- [x] Dashboard Panitia: Rekap Kebutuhan Seragam (printable)
- [x] Manajemen Gelombang PPDB (multi-gelombang, berbayar/gratis)
- [x] Upload ulang dokumen oleh pendaftar (jika status `need_revision`)
- [ ] Integrasi Payment Gateway untuk gelombang berbayar
- [ ] Notifikasi email/WhatsApp otomatis saat status berubah

### I. Dashboard & Monitoring (Analytics)
- [x] Dashboard Berbasis Role: Admin (Statistik Global)
- [x] Dashboard Berbasis Role: Guru (Jadwal, Tugas Aktif)
- [x] Dashboard Berbasis Role: Siswa & Ortu (Tugas Mendatang, Tagihan, Kehadiran)

---

## 🛠️ 3. Pedoman Kerja AI (Strict Development Rules)

AI **WAJIB** mematuhi aturan berikut selama beroperasi:

1. **Simplicity First (Core Principle)**
   - Buat setiap perubahan sesederhana mungkin. Minimalkan jumlah baris kode yang terdampak.
   - Hindari *scope creep* yang berpotensi memicu bug baru.
2. **API First & Decoupled**
   - Jangan pernah menyatukan *business logic* di frontend (contoh: server-rendered forms tanpa API layer). Semua transaksi data harus dilakukan via REST API Backend. Frontend hanya sebagai konsumen API.
3. **Kualitas Desain (Web Application Aesthetics)**
   - **Gunakan estetika premium**. *Vibrant colors, dark modes, glassmorphism*, tipografi modern (Inter/Roboto), dan animasi mikro (hover effects, transitions).
   - *Mobile-first approach*. Web harus nyaman diakses dari *smartphone* sejak hari pertama.
4. **Autonomous Bug Fixing**
   - Jika menerima laporan *bug* atau kegagalan *pipeline* (CI/CD), segera perbaiki mandiri berdasarkan log/error. **Jangan meminta panduan langkah-demi-langkah dari *user*.** (Ownership!).
5. **Quality and Elegance (Staff Engineer Standard)**
   - Untuk setiap perubahan non-trivial, evaluasi: *Apakah ada cara yang lebih elegan/bersih?*
   - Jika sebuah perbaikan terasa "dipaksakan", tulis ulang (*refactor*) dengan solusi yang lebih masuk akal dan komprehensif.
6. **No Placeholder UI**
   - Jangan pernah membiarkan halaman frontend kosong atau dipenuhi *lorem ipsum*. Buatlah demo UI yang fungsional jika API belum siap sepenuhnya.
7. **Verifikasi Fungsionalitas**
   - Jangan pernah menandai tugas sebagai "Selesai" tanpa bukti nyata (*test pass*, log bersih, atau demo).

---

## 🎯 4. Fokus Saat Ini (Current Sprint)

### ✅ Modul yang Sudah Selesai
| Modul | Keterangan |
|---|---|
| A. Fondasi & Auth | Login multi-role, RBAC, JWT |
| B. Administrasi Master Data | Siswa, Guru, Kelas, Mapel, Tahun Ajaran |
| C. LMS (lengkap) | Materi, Tugas, Submission, Penilaian via student_grades |
| E. Nilai & Rapor (inti) | Bobot, input nilai, kalkulasi otomatis, verifikasi, publish, RBAC policy |
| H. PPDB Online (inti) | Portal publik, dashboard panitia, daftar ulang, rekap seragam, upload ulang |
| I. Dashboard Role | Admin, Guru, Siswa, Ortu |

### 🔄 Sprint Berikutnya (Rekomendasi Urutan)
1. **Kehadiran & Absensi** (Modul D — Presensi harian oleh Guru)
2. **Pengumuman** (Modul F — Broadcast ke peran tertentu)
3. **Keuangan / SPP** (Modul G)
4. **Generate PDF Rapor** (Modul E — PDF cetak rapor resmi)
5. **PPDB — Fitur Lanjutan** (notifikasi, payment gateway)

*(Catatan: File ini harus rutin diperbarui ketika sebuah Modul MVP telah diselesaikan. Terakhir diperbarui: 2026-07-16 setelah Modul Nilai & Rapor selesai.)*
