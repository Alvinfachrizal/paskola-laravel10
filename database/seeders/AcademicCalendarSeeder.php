<?php

namespace Database\Seeders;

use App\Models\AcademicEvent;
use App\Models\EventCategory;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicCalendarSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::firstOrCreate(
            ['npsn' => '12345678'],
            [
                'name'    => 'Sekolah Nusantara',
                'address' => 'Jl. Pendidikan No. 1',
                'phone'   => '021-123456',
                'email'   => 'info@sekolahnusantara.sch.id',
                'level'   => 'SMA',
            ]
        );

        $admin = User::where('email', 'admin@paskola.com')->first()
            ?? User::where('email', 'superadmin@paskola.com')->first()
            ?? User::first();

        if (! $admin) {
            $this->command->warn('Tidak ada user di database. Jalankan RoleAndUserSeeder terlebih dahulu, lalu jalankan seeder ini kembali.');
            return;
        }


        // ─── Kategori Event ────────────────────────────────────────────────────
        $categories = [
            [
                'name'       => 'Hari Libur Nasional',
                'is_holiday' => true,
                'color'      => '#EF4444', // merah
            ],
            [
                'name'       => 'Libur Sekolah',
                'is_holiday' => true,
                'color'      => '#F97316', // oranye
            ],
            [
                'name'       => 'Ujian / Penilaian',
                'is_holiday' => false,
                'color'      => '#3B82F6', // biru
            ],
            [
                'name'       => 'Kegiatan Sekolah',
                'is_holiday' => false,
                'color'      => '#10B981', // hijau
            ],
            [
                'name'       => 'Pertemuan Orang Tua',
                'is_holiday' => false,
                'color'      => '#8B5CF6', // ungu
            ],
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[$cat['name']] = EventCategory::firstOrCreate(
                ['school_id' => $school->id, 'name' => $cat['name']],
                ['is_holiday' => $cat['is_holiday'], 'color' => $cat['color']]
            );
        }

        // ─── Event Sekolah-Wide (class_id = null) ─────────────────────────────
        $schoolWideEvents = [
            [
                'category' => 'Hari Libur Nasional',
                'title'    => 'Libur Idul Fitri 1446 H',
                'start'    => '2025-03-28',
                'end'      => '2025-04-07',
                'desc'     => 'Libur lebaran selama 11 hari',
            ],
            [
                'category' => 'Hari Libur Nasional',
                'title'    => 'Hari Kemerdekaan RI',
                'start'    => '2025-08-17',
                'end'      => '2025-08-17',
                'desc'     => 'HUT RI ke-80',
            ],
            [
                'category' => 'Ujian / Penilaian',
                'title'    => 'Ujian Tengah Semester Ganjil',
                'start'    => '2025-10-06',
                'end'      => '2025-10-10',
                'desc'     => 'PTS Ganjil TA 2025/2026',
            ],
            [
                'category' => 'Ujian / Penilaian',
                'title'    => 'Ujian Akhir Semester Ganjil',
                'start'    => '2025-12-01',
                'end'      => '2025-12-12',
                'desc'     => 'PAS Ganjil TA 2025/2026',
            ],
            [
                'category' => 'Libur Sekolah',
                'title'    => 'Libur Semester Ganjil',
                'start'    => '2025-12-22',
                'end'      => '2026-01-02',
                'desc'     => 'Libur akhir semester ganjil dan Natal/Tahun Baru',
            ],
            [
                'category' => 'Kegiatan Sekolah',
                'title'    => 'MPLS (Masa Pengenalan Lingkungan Sekolah)',
                'start'    => '2025-07-14',
                'end'      => '2025-07-18',
                'desc'     => 'Pengenalan sekolah untuk siswa baru',
            ],
            [
                'category' => 'Pertemuan Orang Tua',
                'title'    => 'Pembagian Rapor Semester Ganjil',
                'start'    => '2025-12-19',
                'end'      => '2025-12-19',
                'desc'     => 'Orang tua mengambil rapor semester ganjil',
            ],
        ];

        foreach ($schoolWideEvents as $event) {
            AcademicEvent::firstOrCreate(
                [
                    'title'      => $event['title'],
                    'start_date' => $event['start'],
                ],
                [
                    'category_id' => $createdCategories[$event['category']]->id,
                    'class_id'    => null,
                    'created_by'  => $admin->id,
                    'end_date'    => $event['end'],
                    'description' => $event['desc'],
                ]
            );
        }

        // ─── Event Khusus Kelas (class_id diisi) ──────────────────────────────
        $classA = SchoolClass::where('is_active', true)->first();
        if ($classA && $admin) {
            AcademicEvent::firstOrCreate(
                [
                    'title'      => 'Ujian Susulan Kelas ' . $classA->name,
                    'start_date' => '2025-10-13',
                ],
                [
                    'category_id' => $createdCategories['Ujian / Penilaian']->id,
                    'class_id'    => $classA->id,
                    'created_by'  => $admin->id,
                    'end_date'    => '2025-10-14',
                    'description' => 'Ujian susulan bagi siswa yang tidak hadir PTS',
                ]
            );
        }

        $this->command->info('AcademicCalendarSeeder: ' . count($schoolWideEvents) . ' event sekolah-wide berhasil dibuat.');
    }
}
