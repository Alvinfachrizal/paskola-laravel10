# 🚀 Paskola - Sistem Informasi Manajemen Sekolah (SIMS) 

Dokumen ini berfungsi sebagai **Master Project Plan & Tracker** yang digunakan oleh AI (Gemini) untuk memastikan pengembangan proyek tetap terukur, sistematis, dan sesuai dengan rencana arsitektur awal tanpa melenceng (*scope creep*). 

**AI harus selalu mengacu pada dokumen ini sebagai pedoman sebelum membuat arsitektur atau perubahan besar.**

---

## 📌 1. Visi Produk & Arsitektur
- **Tujuan**: Platform terpusat untuk aktivitas akademik, administrasi, dan komunikasi sekolah untuk menggantikan proses manual.
- **Backend (API-First)**: `Node.js (NestJS) + TypeScript + PostgreSQL`. Backend murni bertindak sebagai REST API (Stateless, JWT) agar 100% siap digunakan oleh Mobile App (React Native/Flutter) di masa depan.
- **Frontend (Web)**: `Next.js + TypeScript + TailwindCSS`. Desain wajib *mobile-first*, estetik premium, responsif, dan dinamis (animasi mikro).
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
- [ ] API & UI Upload/Manajemen Materi Pembelajaran
- [ ] API & UI Manajemen Tugas & Ujian Online
- [ ] API & UI Pengumpulan Tugas Siswa (Submission)
- [ ] Penilaian & Riwayat Nilai Tugas

### D. Kehadiran & Absensi (Attendance)
- [ ] Skema Database Absensi (Presensi Harian & Mata Pelajaran)
- [ ] API & UI Presensi oleh Guru (Hadir/Izin/Sakit/Alfa)
- [ ] API & UI Pengajuan Izin Online (Siswa/Ortu) & Approval Guru
- [ ] Rekap & Dashboard Kehadiran (Persentase)

### E. Manajemen Nilai & Rapor (Grading)
- [ ] API & UI Input Nilai Harian, Tugas, UTS, UAS, Praktik
- [ ] Kalkulasi Nilai Akhir Otomatis
- [ ] Generate Rapor Digital (PDF/Cetak)

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
- [ ] Landing Page Pendaftaran
- [ ] Form Pendaftaran, Upload Dokumen, dan Sistem Seleksi
- [ ] Pembayaran Biaya Pendaftaran
- [ ] Pengumuman Kelulusan

### I. Dashboard & Monitoring (Analytics)
- [ ] Dashboard Berbasis Role: Admin (Statistik Global)
- [ ] Dashboard Berbasis Role: Guru (Jadwal, Tugas Aktif)
- [ ] Dashboard Berbasis Role: Siswa & Ortu (Tugas Mendatang, Tagihan, Kehadiran)

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
- **Status Proyek Terkini**: Infrastruktur awal Backend dan struktur Database telah didirikan. Manajemen Data Induk / Akademik di Backend & Frontend sebagian besar sudah berdiri.
- **Sprint Mendatang**: AI akan menunggu instruksi spesifik dari *User* terkait Modul mana yang akan diprioritaskan selanjutnya (Sesuai instruksi *Prioritas MVP* di dokumen spesifikasi aplikasi sekolah).

*(Catatan: File ini harus rutin diperbarui ketika sebuah Modul MVP telah diselesaikan seluruh proses Testing dan Implementasinya).*
