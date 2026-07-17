<?php

namespace Database\Seeders;

use App\Enums\GradeComponentType;
use App\Enums\ReportCardStatus;
use App\Models\GradeWeight;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Models\School;
use Illuminate\Database\Seeder;

/**
 * Seeder Rapor: membuat data contoh agar fitur langsung bisa dicoba.
 *
 * Yang dibuat:
 * 1. 2 Semester (Ganjil + Genap 2025/2026)
 * 2. Bobot nilai untuk 2 mata pelajaran
 * 3. Nilai mentah untuk semua siswa dari seeder sebelumnya
 * 4. Rapor otomatis dihitung dari nilai × bobot
 */
class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (!$school) return;

        // ── 1. Buat Semester ─────────────────────────────────────────────
        $semGanjil = Semester::firstOrCreate(
            ['school_id' => $school->id, 'academic_year' => 2025, 'term' => 1],
            [
                'name'       => 'Semester Ganjil 2025/2026',
                'start_date' => '2025-07-14',
                'end_date'   => '2025-12-20',
                'is_active'  => true,
            ]
        );

        $semGenap = Semester::firstOrCreate(
            ['school_id' => $school->id, 'academic_year' => 2025, 'term' => 2],
            [
                'name'       => 'Semester Genap 2025/2026',
                'start_date' => '2026-01-05',
                'end_date'   => '2026-06-28',
                'is_active'  => false,
            ]
        );

        // ── 2. Ambil mapel yang sudah ada ────────────────────────────────
        $subjects = Subject::take(3)->get();
        if ($subjects->isEmpty()) {
            $this->command->warn('Tidak ada subjects. Jalankan seeder subjects terlebih dahulu.');
            return;
        }

        // ── 3. Atur bobot nilai untuk setiap mapel ───────────────────────
        // Bobot 1: Tugas=20, Kuis=10, Ujian Online=20, UTS=25, UAS=25
        $defaultWeights = [
            ['component_type' => 'tugas',        'weight_percent' => 20],
            ['component_type' => 'kuis',          'weight_percent' => 10],
            ['component_type' => 'ujian_online',  'weight_percent' => 20],
            ['component_type' => 'uts',           'weight_percent' => 25],
            ['component_type' => 'uas',           'weight_percent' => 25],
        ];

        foreach ($subjects as $subject) {
            foreach ($defaultWeights as $w) {
                GradeWeight::firstOrCreate(
                    [
                        'subject_id'     => $subject->id,
                        'semester_id'    => $semGanjil->id,
                        'component_type' => $w['component_type'],
                    ],
                    ['weight_percent' => $w['weight_percent']]
                );
            }
        }

        // ── 4. Ambil siswa & buat nilai contoh ───────────────────────────
        $students = Student::take(5)->get();
        if ($students->isEmpty()) {
            $this->command->warn('Tidak ada students. Jalankan seeder students terlebih dahulu.');
            return;
        }

        $gradeMap = [
            'tugas'       => [85, 78, 92, 70, 88],
            'kuis'        => [80, 75, 90, 65, 85],
            'ujian_online'=> [82, 72, 88, 68, 84],
            'uts'         => [78, 70, 85, 60, 80],
            'uas'         => [80, 75, 88, 65, 82],
        ];

        $firstSubject = $subjects->first();

        foreach ($students as $index => $student) {
            foreach ($gradeMap as $component => $scores) {
                $score = $scores[$index] ?? 75;
                StudentGrade::firstOrCreate(
                    [
                        'student_id'     => $student->id,
                        'subject_id'     => $firstSubject->id,
                        'semester_id'    => $semGanjil->id,
                        'component_type' => $component,
                    ],
                    [
                        'score'      => $score,
                        'source'     => in_array($component, ['uts', 'uas']) ? 'manual' : 'lms',
                    ]
                );
            }

            // Hitung & buat rapor otomatis
            ReportCard::recalculate($student->id, $firstSubject->id, $semGanjil->id);
        }

        // Publish satu rapor sebagai contoh (agar siswa bisa langsung lihat)
        ReportCard::where('student_id', $students->first()->id)
            ->where('subject_id', $firstSubject->id)
            ->update(['status' => ReportCardStatus::Published->value]);

        $this->command->info('GradeSeeder: 2 semester, bobot nilai, ' . $students->count() . ' nilai siswa, dan rapor berhasil dibuat.');
    }
}
