<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QuestionBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = School::first();
        if (!$school) {
            $this->command->error('Data sekolah belum ada.');
            return;
        }

        $subjects = Subject::where('school_id', $school->id)->get();
        if ($subjects->isEmpty()) {
            $this->command->error('Belum ada data mata pelajaran (Subject).');
            return;
        }

        // Ambil guru secara acak untuk dijadikan pemilik bank soal
        // Prioritaskan akun guru default agar lebih enak saat demo
        $guru = User::role('Guru')->first();
        if (!$guru) {
            $this->command->error('Tidak ada user dengan role Guru.');
            return;
        }

        $this->command->info("Menyiapkan 10 soal untuk setiap dari {$subjects->count()} Mata Pelajaran...");

        foreach ($subjects as $subject) {
            $this->command->line("Generating soal untuk mapel: {$subject->name}...");
            
            for ($i = 1; $i <= 10; $i++) {
                $question = Question::create([
                    'school_id'     => $school->id,
                    'teacher_id'    => $guru->id,
                    'subject_id'    => $subject->id,
                    'question_text' => $this->generateQuestionText($subject->name, $i),
                    'in_bank'       => true,
                ]);

                // Buat 4-5 opsi pilihan ganda
                $correctIndex = rand(0, 3); // A, B, C, atau D
                $options = ['A', 'B', 'C', 'D'];

                foreach ($options as $idx => $label) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => "Pilihan {$label} untuk soal {$i} ({$subject->name})",
                        'is_correct'  => ($idx === $correctIndex),
                        'position'    => $idx + 1,
                    ]);
                }
            }
        }

        $this->command->info('Berhasil membuat 10 soal untuk setiap mapel!');
    }

    /**
     * Helper untuk membuat teks soal yang sedikit realistis berdasarkan mapel
     */
    private function generateQuestionText(string $subjectName, int $number): string
    {
        $mapel = strtolower($subjectName);

        if (str_contains($mapel, 'matematika')) {
            return "Berapakah hasil dari " . rand(10, 50) . " x " . rand(2, 9) . " + " . rand(10, 100) . "? (Soal Uji #{$number})";
        }
        
        if (str_contains($mapel, 'fisika')) {
            return "Sebuah benda bermassa " . rand(2, 10) . " kg bergerak dengan percepatan " . rand(2, 5) . " m/s². Berapakah gaya yang bekerja pada benda tersebut? (Soal Uji #{$number})";
        }

        if (str_contains($mapel, 'biologi')) {
            return "Bagian sel yang berfungsi sebagai pusat pengaturan seluruh kegiatan sel adalah... (Soal Uji #{$number})";
        }

        if (str_contains($mapel, 'sejarah')) {
            return "Pada tahun berapakah peristiwa penting ini terjadi dalam sejarah kemerdekaan? (Soal Uji #{$number})";
        }

        if (str_contains($mapel, 'bahasa inggris') || str_contains($mapel, 'english')) {
            return "Choose the correct answer to complete the sentence: 'She ... to the market yesterday.' (Soal Uji #{$number})";
        }

        // Default soal umum
        return "Pertanyaan evaluasi nomor {$number} untuk mata pelajaran {$subjectName}. Pilihlah jawaban yang paling tepat di bawah ini.";
    }
}
