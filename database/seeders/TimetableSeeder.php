<?php

namespace Database\Seeders;

use App\Models\AcademicDay;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (!$school) {
            $this->command->error('School tidak ditemukan. Jalankan seeder master data terlebih dahulu.');
            return;
        }

        // 1. Setup Hari Aktif (Senin - Jumat Aktif, Sabtu - Minggu Libur)
        $days = [
            1 => true,  // Senin
            2 => true,  // Selasa
            3 => true,  // Rabu
            4 => true,  // Kamis
            5 => true,  // Jumat
            6 => false, // Sabtu
            7 => false, // Minggu
        ];

        foreach ($days as $day => $isActive) {
            AcademicDay::updateOrCreate(
                ['school_id' => $school->id, 'day_of_week' => $day],
                ['is_active' => $isActive]
            );
        }
        $this->command->info('Academic Days created.');

        // 2. Setup Ruangan
        $roomsData = [
            ['name' => 'Ruang 10A', 'type' => 'Tetap'],
            ['name' => 'Ruang 10B', 'type' => 'Tetap'],
            ['name' => 'Ruang 11A', 'type' => 'Tetap'],
            ['name' => 'Ruang 12A', 'type' => 'Tetap'],
            ['name' => 'Lab Komputer', 'type' => 'Umum'],
            ['name' => 'Lab IPA', 'type' => 'Umum'],
            ['name' => 'Aula', 'type' => 'Umum'],
        ];

        $rooms = [];
        foreach ($roomsData as $r) {
            $rooms[] = Room::updateOrCreate(
                ['school_id' => $school->id, 'name' => $r['name']],
                ['type' => $r['type']]
            );
        }
        $this->command->info('Rooms created.');

        // 3. Setup Time Slots (Jam Pelajaran)
        // Senin-Kamis: Pagi (4 jam), Siang (2 jam)
        // Jumat: Pagi (3 jam)
        $timeSlots = [];
        $pagiStart = Carbon::createFromTime(7, 0, 0);
        $siangStart = Carbon::createFromTime(13, 0, 0);
        $durationMin = 45;

        for ($day = 1; $day <= 5; $day++) {
            // Shift Pagi
            $periodsPagi = ($day == 5) ? 3 : 4; 
            for ($p = 1; $p <= $periodsPagi; $p++) {
                $start = $pagiStart->copy()->addMinutes(($p - 1) * $durationMin);
                $end = $start->copy()->addMinutes($durationMin);
                $timeSlots[] = TimeSlot::updateOrCreate([
                    'school_id' => $school->id,
                    'day_of_week' => $day,
                    'shift' => 'Pagi',
                    'period_number' => $p,
                ], [
                    'start_time' => $start->format('H:i:s'),
                    'end_time' => $end->format('H:i:s'),
                ]);
            }

            // Shift Siang (Kecuali Jumat)
            if ($day < 5) {
                for ($p = 1; $p <= 2; $p++) {
                    $start = $siangStart->copy()->addMinutes(($p - 1) * $durationMin);
                    $end = $start->copy()->addMinutes($durationMin);
                    $timeSlots[] = TimeSlot::updateOrCreate([
                        'school_id' => $school->id,
                        'day_of_week' => $day,
                        'shift' => 'Siang',
                        'period_number' => $p,
                    ], [
                        'start_time' => $start->format('H:i:s'),
                        'end_time' => $end->format('H:i:s'),
                    ]);
                }
            }
        }
        $this->command->info('Time Slots created.');

        // 4. Setup Prerequisites (Semester, Class, Subject, Teacher) if empty
        $schoolYear = \App\Models\SchoolYear::firstOrCreate(
            ['school_id' => $school->id, 'academic_year' => '2025/2026', 'semester' => 'Ganjil'],
            ['start_date' => '2025-07-01', 'end_date' => '2025-12-31', 'is_active' => true]
        );

        $semester = Semester::firstOrCreate(
            ['school_id' => $school->id, 'academic_year' => 2025, 'term' => 1],
            ['name' => 'Ganjil', 'start_date' => '2025-07-01', 'end_date' => '2025-12-31', 'is_active' => true]
        );

        $classes = SchoolClass::where('school_id', $school->id)->take(2)->get();
        if ($classes->count() < 2) {
            $classes = collect([
                SchoolClass::firstOrCreate(['school_id' => $school->id, 'name' => 'X-A'], ['school_year_id' => $schoolYear->id, 'grade' => 10, 'is_active' => true]),
                SchoolClass::firstOrCreate(['school_id' => $school->id, 'name' => 'X-B'], ['school_year_id' => $schoolYear->id, 'grade' => 10, 'is_active' => true]),
            ]);
        }

        $subjects = Subject::where('school_id', $school->id)->take(3)->get();
        if ($subjects->count() < 3) {
            $subjects = collect([
                Subject::firstOrCreate(['school_id' => $school->id, 'name' => 'Matematika'], ['code' => 'MTK']),
                Subject::firstOrCreate(['school_id' => $school->id, 'name' => 'Bahasa Indonesia'], ['code' => 'BIN']),
                Subject::firstOrCreate(['school_id' => $school->id, 'name' => 'Biologi'], ['code' => 'BIO']),
            ]);
        }

        $teachers = DB::table('teachers')->where('school_id', $school->id)->take(3)->get();
        if ($teachers->count() < 3) {
            // Need to make sure users exist to link as teachers, or we just insert raw rows since teacher is a distinct table
            // Wait, is 'teachers' table separate from 'users' or related? Let's check columns. 
            // Better to assume we have users from RoleAndUserSeeder, let's grab some users
            $guruUsers = \App\Models\User::role('Guru')->take(3)->get();
            if ($guruUsers->count() < 3) {
                $this->command->error('Tidak ada user dengan role Guru. Harap jalankan seeder user.');
                return;
            }
            
            // Insert them into teachers table if needed, or if teachers is just a role, wait...
            // the table is 'teachers'. Let's see if we can insert raw.
            // Usually teachers table has user_id, nip, etc. Let's try raw insert.
            foreach ($guruUsers as $u) {
                \App\Models\Teacher::updateOrCreate(
                    ['user_id' => $u->id],
                    ['school_id' => $school->id, 'nip' => 'NIP-'.$u->id, 'name' => $u->name]
                );
            }
            $teachers = \App\Models\Teacher::where('school_id', $school->id)->take(3)->get();
        }

        // Clean up existing schedules to avoid unique constraint errors during seeding
        Schedule::where('school_id', $school->id)->where('semester_id', $semester->id)->delete();

        // Create some sample non-conflicting schedules
        // Class 1: Monday Pagi 1 & 2 -> Teacher 1, Subject 1, Room Tetap 1
        Schedule::create([
            'school_id' => $school->id,
            'semester_id' => $semester->id,
            'time_slot_id' => TimeSlot::where('day_of_week', 1)->where('shift', 'Pagi')->where('period_number', 1)->first()->id,
            'room_id' => $rooms[0]->id,
            'class_id' => $classes[0]->id,
            'subject_id' => $subjects[0]->id,
            'teacher_id' => $teachers[0]->id,
        ]);
        Schedule::create([
            'school_id' => $school->id,
            'semester_id' => $semester->id,
            'time_slot_id' => TimeSlot::where('day_of_week', 1)->where('shift', 'Pagi')->where('period_number', 2)->first()->id,
            'room_id' => $rooms[0]->id,
            'class_id' => $classes[0]->id,
            'subject_id' => $subjects[0]->id,
            'teacher_id' => $teachers[0]->id,
        ]);

        // Class 2: Monday Pagi 1 & 2 -> Teacher 2, Subject 2, Room Tetap 2
        Schedule::create([
            'school_id' => $school->id,
            'semester_id' => $semester->id,
            'time_slot_id' => TimeSlot::where('day_of_week', 1)->where('shift', 'Pagi')->where('period_number', 1)->first()->id,
            'room_id' => $rooms[1]->id,
            'class_id' => $classes[1]->id,
            'subject_id' => $subjects[1]->id,
            'teacher_id' => $teachers[1]->id,
        ]);

        // Class 1: Tuesday Pagi 1 -> Teacher 3, Subject 3, Lab Komputer
        Schedule::create([
            'school_id' => $school->id,
            'semester_id' => $semester->id,
            'time_slot_id' => TimeSlot::where('day_of_week', 2)->where('shift', 'Pagi')->where('period_number', 1)->first()->id,
            'room_id' => $rooms[4]->id, // Lab Komputer
            'class_id' => $classes[0]->id,
            'subject_id' => $subjects[2]->id,
            'teacher_id' => $teachers[2]->id,
        ]);

        // Class 2: Tuesday Siang 1 -> Teacher 3, Subject 3, Lab Komputer (Shift Siang, no conflict on room/teacher)
        Schedule::create([
            'school_id' => $school->id,
            'semester_id' => $semester->id,
            'time_slot_id' => TimeSlot::where('day_of_week', 2)->where('shift', 'Siang')->where('period_number', 1)->first()->id,
            'room_id' => $rooms[4]->id, // Lab Komputer
            'class_id' => $classes[1]->id,
            'subject_id' => $subjects[2]->id,
            'teacher_id' => $teachers[2]->id,
        ]);

        $this->command->info('Schedules created successfully.');
    }
}
