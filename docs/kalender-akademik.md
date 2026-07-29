# Modul Kalender Akademik

**Terakhir diperbarui:** 2026-07-27  
**Status:** ✅ Selesai

---

## Ringkasan Alur Modul

```
Route /calendar
  └── AcademicEventController@index
        ├── AcademicCalendarService::getEventsForPeriod()
        ├── EventCategory (data master kategori)
        ├── SchoolClass (filter kelas)
        └── View: calendar/index.blade.php
              ├── Grid kalender bulanan
              ├── Modal tambah/edit event  ← @can('create')
              └── Daftar event bulan ini

Route /calendar/categories
  └── EventCategoryController@index
        └── View: calendar/categories/index.blade.php
              └── CRUD kategori ← hanya Admin/Kepsek
```

---

## Skema Database

### `event_categories`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | uuid | Primary key |
| `school_id` | uuid FK | Sekolah pemilik kategori |
| `name` | string | Nama kategori (fleksibel, bisa ditambah Admin) |
| `is_holiday` | boolean | `true` = hari libur (meniadakan jadwal pelajaran) |
| `color` | varchar(7) | Hex color untuk tampilan kalender (#RRGGBB) |

### `academic_events`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | uuid | Primary key |
| `category_id` | uuid FK | Kategori event |
| `class_id` | uuid FK nullable | `NULL` = seluruh sekolah; diisi = 1 kelas |
| `created_by` | uuid FK | User yang membuat event |
| `title` | string | Judul event |
| `start_date` | date | Tanggal mulai |
| `end_date` | date | Tanggal selesai |
| `description` | text nullable | Keterangan tambahan |

---

## RBAC (Batasan Role)

| Role | Lihat Kalender | Buat Event | Kategori Holiday | Kelola Kategori |
|------|:-:|:-:|:-:|:-:|
| Admin / Kepsek | ✅ | ✅ Semua kelas | ✅ | ✅ |
| Guru | ✅ | ✅ Semua kelas* | ❌ | ❌ |
| Siswa / Ortu | ✅ | ❌ | ❌ | ❌ |

> *Setelah modul Jadwal Pelajaran selesai, Guru hanya bisa buat event untuk kelas yang diajarnya.

Validasi kategori holiday untuk Guru diterapkan di `StoreAcademicEventRequest::withValidator()`.

---

## File yang Dibuat

```
app/
  Models/
    EventCategory.php
    AcademicEvent.php
  Services/
    AcademicCalendarService.php       ← SERVICE UTAMA (reusable)
  Policies/
    AcademicEventPolicy.php
    EventCategoryPolicy.php
  Http/
    Controllers/Calendar/
      AcademicEventController.php
      EventCategoryController.php
    Requests/
      StoreAcademicEventRequest.php

database/
  migrations/
    2026_07_27_000001_create_event_categories_table.php
    2026_07_27_000002_create_academic_events_table.php
  seeders/
    AcademicCalendarSeeder.php
  factories/
    SchoolClassFactory.php

resources/views/calendar/
  index.blade.php               ← Kalender bulanan grid
  categories/index.blade.php    ← Manajemen kategori

tests/Unit/
  AcademicCalendarServiceTest.php   ← 5 skenario test
```

---

## Cara Pakai `AcademicCalendarService` di Modul Lain

Service ini dirancang **reusable** — tidak ada logic pengecekan libur di controller.

### Import di Modul Jadwal Pelajaran (nanti)

```php
use App\Services\AcademicCalendarService;

class JadwalController extends Controller
{
    public function __construct(
        private AcademicCalendarService $calendarService
    ) {}

    public function show(string $date, string $classId)
    {
        $holiday = $this->calendarService->isHoliday($date, $classId);

        if ($holiday['is_holiday']) {
            // Jadwal tidak aktif hari ini
            return view('jadwal.libur', [
                'alasan' => $holiday['event_title'],
            ]);
        }

        // Tampilkan jadwal normal...
    }
}
```

### Method yang Tersedia

```php
// Cek hari libur
$result = $service->isHoliday('2025-08-17', $classId = null);
// return: ['is_holiday' => bool, 'event_title' => string|null, 'event_id' => string|null]

// Ambil semua event dalam rentang (untuk render kalender)
$events = $service->getEventsForPeriod('2025-10-01', '2025-10-31', $classId = null);
// return: Collection of AcademicEvent (with category eager-loaded)
```

### Skenario yang Sudah Diuji (Unit Test)

```bash
php artisan test --filter=AcademicCalendarServiceTest
# ✓ detects school wide holiday
# ✓ detects class specific holiday (true kelas A, false kelas B)
# ✓ returns false for regular day
# ✓ ignores non holiday events
# ✓ detects holiday in multi day range
# Tests: 5 passed
```

---

## Cara Test Manual

1. Login sebagai **Admin** → buka `/calendar`
2. Cek event HUT RI (17 Agustus) sudah muncul di kalender
3. Klik **Kelola Kategori** → tambah kategori baru, pastikan bisa pilih warna & is_holiday
4. Login sebagai **Guru** → klik **Tambah Event**
5. Pastikan dropdown kategori **tidak menampilkan** kategori yang `is_holiday = true`
6. Login sebagai **Siswa** → pastikan **tidak ada** tombol Tambah Event
7. Navigasi ke bulan Agustus → cek event HUT RI muncul dengan warna merah

---

## Catatan untuk Pengembangan Berikutnya

- **Modul Jadwal Pelajaran**: Gunakan `AcademicCalendarService::isHoliday()` untuk cek sebelum render jadwal. Batasi Guru hanya ke kelas yang ada di jadwalnya.
- **Notifikasi Event**: Saat Admin buat event libur, bisa trigger notifikasi ke semua siswa (Modul F — Pengumuman).
- **iCal Export**: Untuk integrasi ke Google Calendar / kalender HP (fitur opsional masa depan).
