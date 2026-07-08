# Skema Database - Sistem Informasi Manajemen Sekolah (SIMS)

Dokumen ini menjelaskan struktur database (berbasis PostgreSQL) yang digunakan dalam aplikasi SIMS. Skema database dirancang modular dengan pemisahan tabel berdasarkan domain fungsional (Autentikasi, Akademik, Profil Pengguna, LMS, dan Komunikasi). 

Database menggunakan UUID sebagai tipe *primary key* utama untuk semua entitas untuk mendukung skalabilitas dan keamanan referensi.

---

## 1. Inti & Autentikasi (Core & Auth)

### `schools`
Tabel utama untuk konfigurasi sekolah. Disiapkan agar dapat berevolusi menjadi multi-tenant (SaaS) di masa depan.
- `id` (UUID, PK)
- `name` (String)
- `npsn` (String, Unique, Nullable)
- `address`, `phone`, `email` (String, Nullable)
- `logo_url` (String, Nullable)
- `level` (Enum: 'SD', 'SMP', 'SMA', 'SMK', Nullable)
- `settings` (JSONB) - Konfigurasi tambahan

### `users`
Tabel sentral untuk akun pengguna platform (Login).
- `id` (UUID, PK)
- `school_id` (UUID, FK -> schools)
- `nik` (String, Nullable)
- `name` (String)
- `email` (String, Unique)
- `phone` (String, Nullable)
- `password_hash` (String)
- `role` (Enum UserRole) - Cth: SISWA, GURU, dll.
- `is_active` (Boolean)
- `email_verified` (Boolean)
- `avatar_url` (String, Nullable)
- `last_login_at` (Timestamp, Nullable)

### `refresh_tokens`
Menyimpan token refresh untuk mengelola sesi otentikasi JWT secara aman.
- `id` (UUID, PK)
- `user_id` (UUID, FK -> users)
- `token_hash` (String)
- `device_info`, `ip_address` (String, Nullable)
- `is_revoked` (Boolean)
- `expires_at` (Timestamp)

### `audit_logs`
Catatan riwayat aksi kritikal (Security & Tracking).
- `id` (UUID, PK)
- `user_id` (UUID, FK -> users)
- `school_id` (UUID, Nullable)
- `action` (String)
- `entity_type` (String, Nullable)
- `entity_id` (UUID, Nullable)
- `old_values`, `new_values` (JSONB)
- `ip_address` (String, Nullable)

---

## 2. Akademik (Academic)

### `school_years`
Tahun Ajaran dan Semester berjalan.
- `id` (UUID, PK)
- `school_id` (UUID, FK -> schools)
- `academic_year` (String)
- `semester` (Enum: ganjil, genap)
- `start_date`, `end_date` (Date)
- `is_active` (Boolean)

### `majors`
Daftar Jurusan di sekolah (misal: IPA, IPS, RPL, TKJ).
- `id` (UUID, PK)
- `school_id` (UUID, FK -> schools)
- `name` (String)
- `type` (Enum MajorType)
- `description` (Text, Nullable)
- `is_active` (Boolean)

### `classes` (School Class)
Daftar rombongan belajar / kelas.
- `id` (UUID, PK)
- `school_id` (UUID, FK -> schools)
- `school_year_id` (UUID, FK -> school_years)
- `major_id` (UUID, FK -> majors, Nullable)
- `name` (String)
- `grade` (SmallInt) - Tingkat/Tingkat Kelas (misal 10, 11, 12)
- `homeroom_teacher_id` (UUID, FK -> teachers)
- `room_number` (String, Nullable)
- `max_students` (SmallInt)
- `is_active` (Boolean)

### `subjects`
Mata pelajaran yang diajarkan.
- `id` (UUID, PK)
- `school_id` (UUID, FK -> schools)
- `code` (String, Unique)
- `name` (String)
- `type` (Enum SubjectType)
- `weekly_hours` (SmallInt)
- `description` (Text, Nullable)
- `is_active` (Boolean)

---

## 3. Profil Pengguna (Profiles)

### `teachers`
Data profil detail guru / staf akademik.
- `id` (UUID, PK)
- `user_id` (UUID, FK -> users)
- `school_id` (UUID, FK -> schools)
- `nip` (String, Unique, Nullable)
- `name`, `gender`, `birth_place`, `birth_date`, `religion`, `address`, `phone`
- `photo_url` (String, Nullable)
- `last_education`, `subject_specialty` (String, Nullable)
- `employment_type` (Enum)
- `join_date` (Date, Nullable)
- `status` (Enum TeacherStatus)

### `students`
Data profil detail siswa serta wali/orang tua.
- `id` (UUID, PK)
- `user_id` (UUID, FK -> users, Nullable)
- `school_id` (UUID, FK -> schools)
- `nisn`, `nis` (String, Nullable)
- `name`, `gender`, `birth_place`, `birth_date`, `religion`, `address`, `phone`
- `photo_url` (String, Nullable)
- `entry_year` (SmallInt)
- `status` (Enum StudentStatus)
- **Data Wali**: `parent_name`, `parent_phone`, `parent_job`, `parent_user_id`

### `student_classes`
Relasi penghubung/histori yang merekam siswa masuk ke kelas mana pada tahun ajaran apa.
- `id` (UUID, PK)
- `student_id` (UUID, FK -> students)
- `class_id` (UUID, FK -> classes)
- `school_year_id` (UUID, FK -> school_years)
- `roll_number` (SmallInt, Nullable) - Nomor urut presensi
- `is_active` (Boolean)

---

## 4. E-Learning / LMS (Learning Management System)

### `lms_materials`
Modul materi pembelajaran yang di-upload oleh guru.
- `id` (UUID, PK)
- `school_id`, `teacher_id`, `class_id`, `subject_id` (UUID, FKs)
- `title` (String)
- `description` (Text, Nullable)
- `type` (Enum MaterialType: document, video, link, dll)
- `file_url` (String)

### `lms_assignments`
Tugas / PR dari guru ke siswa.
- `id` (UUID, PK)
- `school_id`, `teacher_id`, `class_id`, `subject_id` (UUID, FKs)
- `title` (String)
- `description` (Text)
- `due_date` (Timestamp)
- `max_score` (Int)

### `lms_submissions`
Pengumpulan jawaban tugas dari siswa.
- `id` (UUID, PK)
- `assignment_id` (UUID, FK -> lms_assignments)
- `student_id` (UUID, FK -> users)
- `file_url`, `text_content` (String, Nullable)
- `submitted_at` (Timestamp)
- `score` (Int, Nullable)
- `feedback` (Text, Nullable)
- `status` (Enum SubmissionStatus)

---

## 5. Komunikasi (Communication)

### `announcements`
Pengumuman dan papan informasi elektronik untuk siswa/guru.
- `id` (UUID, PK)
- `school_id` (UUID, FK -> schools)
- `author_id` (UUID, FK -> users)
- `title` (String)
- `content` (Text)
- `targetRoles` (Array of Enum UserRole, Nullable)
- `targetClassIds` (Array of UUID, Nullable)
- `is_pinned` (Boolean)
