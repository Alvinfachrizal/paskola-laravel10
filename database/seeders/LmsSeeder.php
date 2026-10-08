<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LmsMaterial;
use App\Models\LmsAssignment;
use App\Models\LmsSubmission;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;

class LmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('=== LmsSeeder ===');
        
        $school = School::first();
        if (!$school) {
            $this->command->error('Sekolah belum ada. Jalankan RoleAndUserSeeder dahulu.');
            return;
        }

        // Ambil kelas
        $classIPA = SchoolClass::where('name', 'X-IPA-1')->first();
        if (!$classIPA) {
            $this->command->error('Kelas X-IPA-1 belum ada. Jalankan MasterDataSeeder dahulu.');
            return;
        }

        // Ambil mapel MTK dan FIS
        $mapelMtk = Subject::where('name', 'Matematika')->first();
        $mapelFis = Subject::where('name', 'Fisika')->first();

        // Ambil guru MTK dan FIS
        $guruMtk = User::where('email', 'guru.mtk@paskola.com')->first();
        $guruFis = User::where('email', 'guru.ipa@paskola.com')->first();

        // Ambil siswa X-IPA-1
        $siswaXipa11 = User::where('email', 'siswa.xipa11@paskola.com')->first();
        
        // Ambil beberapa user siswa lagi jika ada
        $students = User::role('Siswa')->whereHas('student', function($q) use ($classIPA) {
            $q->whereHas('classes', function($sq) use ($classIPA) {
                $sq->where('school_classes.id', $classIPA->id);
            });
        })->take(5)->get();

        if (!$guruMtk || !$mapelMtk || !$siswaXipa11) {
            $this->command->error('Data master tidak lengkap. LmsSeeder dilewati.');
            return;
        }

        $this->command->info('Membuat Materi LMS...');

        // 1. Materi Matematika
        $materiMtk1 = LmsMaterial::firstOrCreate(
            [
                'school_id' => $school->id,
                'class_id' => $classIPA->id,
                'subject_id' => $mapelMtk->id,
                'title' => 'Pengenalan Aljabar Linear',
            ],
            [
                'teacher_id' => $guruMtk->id,
                'description' => 'Materi ini membahas konsep dasar sistem persamaan linear dan matriks.',
                'type' => 'document',
            ]
        );

        $materiMtk2 = LmsMaterial::firstOrCreate(
            [
                'school_id' => $school->id,
                'class_id' => $classIPA->id,
                'subject_id' => $mapelMtk->id,
                'title' => 'Video Pembelajaran: Matriks dan Determinan',
            ],
            [
                'teacher_id' => $guruMtk->id,
                'description' => 'Tonton video ini untuk memahami cara menghitung determinan matriks 3x3.',
                'type' => 'video',
                'file_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' // contoh
            ]
        );

        // 2. Materi Fisika
        $materiFis = LmsMaterial::firstOrCreate(
            [
                'school_id' => $school->id,
                'class_id' => $classIPA->id,
                'subject_id' => $mapelFis->id,
                'title' => 'Hukum Newton I, II, dan III',
            ],
            [
                'teacher_id' => $guruFis->id,
                'description' => 'Modul lengkap materi Dinamika Partikel dan Hukum Newton.',
                'type' => 'document',
            ]
        );

        $this->command->info('Membuat Tugas (Assignments)...');

        // 1. Tugas Matematika (Sudah Lewat / Dinilai)
        $tugasMtk = LmsAssignment::firstOrCreate(
            [
                'school_id' => $school->id,
                'class_id' => $classIPA->id,
                'subject_id' => $mapelMtk->id,
                'title' => 'Tugas 1: Sistem Persamaan Linear',
            ],
            [
                'teacher_id' => $guruMtk->id,
                'description' => 'Kerjakan soal nomor 1-5 di LKS halaman 12. Kumpulkan dalam bentuk PDF.',
                'due_date' => Carbon::now()->subDays(2),
                'max_score' => 100,
            ]
        );

        // 2. Tugas Fisika (Aktif / Belum Berakhir)
        $tugasFis = LmsAssignment::firstOrCreate(
            [
                'school_id' => $school->id,
                'class_id' => $classIPA->id,
                'subject_id' => $mapelFis->id,
                'title' => 'Praktikum Gaya Gesek',
            ],
            [
                'teacher_id' => $guruFis->id,
                'description' => 'Buat laporan praktikum sesuai dengan format yang telah dibagikan. Upload berupa file Word (.docx).',
                'due_date' => Carbon::now()->addDays(5),
                'max_score' => 100,
            ]
        );

        $this->command->info('Membuat Pengumpulan Tugas (Submissions)...');

        // Pengumpulan Tugas Matematika (Oleh $siswaXipa11) - Sudah Dinilai
        LmsSubmission::firstOrCreate(
            [
                'assignment_id' => $tugasMtk->id,
                'student_id' => $siswaXipa11->id,
            ],
            [
                'text_content' => 'Berikut adalah jawaban saya untuk tugas 1 Pak.',
                'submitted_at' => Carbon::now()->subDays(3),
                'score' => 85,
                'feedback' => 'Bagus, cara penyelesaian sudah tepat. Perhatikan langkah nomor 3 ada sedikit keliru tanda.',
                'status' => 'graded'
            ]
        );

        // Pengumpulan Tugas Matematika (Oleh siswa lain jika ada) - Belum Dinilai
        if ($students->count() > 1) {
            LmsSubmission::firstOrCreate(
                [
                    'assignment_id' => $tugasMtk->id,
                    'student_id' => $students[1]->id,
                ],
                [
                    'text_content' => 'Maaf telat mengumpulkan.',
                    'submitted_at' => Carbon::now()->subDays(1),
                    'status' => 'submitted'
                ]
            );
        }

        // Pengumpulan Tugas Fisika (Oleh $siswaXipa11) - Baru Dikumpulkan
        LmsSubmission::firstOrCreate(
            [
                'assignment_id' => $tugasFis->id,
                'student_id' => $siswaXipa11->id,
            ],
            [
                'text_content' => 'Laporan praktikum fisika sudah saya lampirkan.',
                'submitted_at' => Carbon::now()->subHours(2),
                'status' => 'submitted'
            ]
        );

        $this->command->info('Membuat Bulk Data Materi & Tugas untuk menambah volume...');
        $faker = \Faker\Factory::create('id_ID');
        $allClasses = SchoolClass::all();
        $allTeachers = User::role('Guru')->get();

        if ($allClasses->count() > 0 && $allTeachers->count() > 0) {
            foreach ($allClasses as $c) {
                // Buat 5 materi ekstra per kelas
                for ($i=0; $i<5; $i++) {
                    $randomTeacher = $allTeachers->random();
                    // Ambil satu mapel yang terkait dengan nama guru jika ada, atau random
                    $randomSubject = Subject::inRandomOrder()->first();
                    
                    LmsMaterial::firstOrCreate(
                        [
                            'school_id' => $school->id,
                            'class_id' => $c->id,
                            'title' => 'Materi Tambahan ' . $faker->words(3, true),
                        ],
                        [
                            'subject_id' => $randomSubject->id ?? $mapelMtk->id,
                            'teacher_id' => $randomTeacher->id,
                            'description' => $faker->paragraph(),
                            'type' => $faker->randomElement(['document', 'video', 'link']),
                        ]
                    );

                    LmsAssignment::firstOrCreate(
                        [
                            'school_id' => $school->id,
                            'class_id' => $c->id,
                            'title' => 'Tugas Tambahan ' . $faker->words(3, true),
                        ],
                        [
                            'subject_id' => $randomSubject->id ?? $mapelMtk->id,
                            'teacher_id' => $randomTeacher->id,
                            'description' => $faker->paragraph(),
                            'due_date' => Carbon::now()->addDays(rand(-5, 10)),
                            'max_score' => 100
                        ]
                    );
                }
            }
        }

        $this->command->info('Data LMS (Materi, Tugas, Submission) berhasil dibuat & diperbanyak!');
    }
}
