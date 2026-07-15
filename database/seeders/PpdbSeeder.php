<?php

namespace Database\Seeders;

use App\Enums\PpdbApplicantStatus;
use App\Enums\PpdbDocumentStatus;
use App\Enums\PpdbPaymentStatus;
use App\Enums\PpdbReregistrationStatus;
use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Models\PpdbPayment;
use App\Models\PpdbReregistration;
use App\Models\PpdbSelectionScore;
use App\Models\PpdbUniformOrder;
use App\Models\PpdbWave;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PpdbSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Buat Gelombang Pendaftaran ─────────────────────────────────────
        $wave1 = PpdbWave::create([
            'name'             => 'Gelombang 1',
            'quota'            => 60,
            'start_date'       => '2025-01-06',
            'end_date'         => '2025-02-28',
            'registration_fee' => 0,       // Gratis
            'is_active'        => false,   // Sudah ditutup
            'description'      => 'Pendaftaran PPDB awal tahun ajaran 2025/2026.',
        ]);

        $wave2 = PpdbWave::create([
            'name'             => 'Gelombang 2',
            'quota'            => 40,
            'start_date'       => now()->subDays(5)->format('Y-m-d'),
            'end_date'         => now()->addDays(25)->format('Y-m-d'),
            'registration_fee' => 150000,  // Berbayar Rp 150.000
            'is_active'        => true,    // Sedang berjalan
            'description'      => 'Pendaftaran PPDB gelombang ke-2 tahun ajaran 2025/2026.',
        ]);

        // ── 2. Buat Pendaftar Dummy dengan variasi status ─────────────────────
        $this->createApplicant($wave1->id, 'Ahmad Fauzi Ramadhan',   'laki-laki',   'M',   PpdbApplicantStatus::ReRegistered, true);
        $this->createApplicant($wave1->id, 'Budi Santoso',           'laki-laki',   'L',   PpdbApplicantStatus::ReRegistered, true);
        $this->createApplicant($wave1->id, 'Siti Nurhaliza',         'perempuan',   'S',   PpdbApplicantStatus::ReRegistered, true);
        $this->createApplicant($wave1->id, 'Dewi Rahayu',            'perempuan',   'M',   PpdbApplicantStatus::Rejected,     false);
        $this->createApplicant($wave1->id, 'Eko Prasetyo',           'laki-laki',   'XL',  PpdbApplicantStatus::Selected,     false);
        $this->createApplicant($wave2->id, 'Fajar Nugroho',          'laki-laki',   'L',   PpdbApplicantStatus::Verified,     false);
        $this->createApplicant($wave2->id, 'Galih Permana',          'laki-laki',   'M',   PpdbApplicantStatus::NeedRevision, false);
        $this->createApplicant($wave2->id, 'Hana Pertiwi',           'perempuan',   'S',   PpdbApplicantStatus::Pending,      false);
        $this->createApplicant($wave2->id, 'Indah Sari',             'perempuan',   'M',   PpdbApplicantStatus::Pending,      false);
        $this->createApplicant($wave2->id, 'Joko Widiatmoko',        'laki-laki',   'XXL', PpdbApplicantStatus::PaymentPending, false);

        $this->command->info('✅ PpdbSeeder selesai: 2 gelombang + 10 pendaftar dummy berhasil dibuat.');
    }

    /**
     * Helper untuk membuat 1 pendaftar beserta semua data terkait.
     */
    private function createApplicant(
        int $waveId,
        string $name,
        string $gender,
        string $ukuran,
        PpdbApplicantStatus $status,
        bool $withReregistration
    ): PpdbApplicant {
        // Buat pendaftar
        $applicant = PpdbApplicant::create([
            'wave_id'           => $waveId,
            'registration_code' => PpdbApplicant::generateRegistrationCode(),
            'full_name'         => $name,
            'nisn'              => fake()->unique()->numerify('##########'),
            'birth_date'        => fake()->dateTimeBetween('-18 years', '-12 years')->format('Y-m-d'),
            'birth_place'       => fake()->city(),
            'gender'            => $gender,
            'address'           => fake()->address(),
            'parent_name'       => fake()->name('male'),
            'parent_phone'      => '08' . fake()->numerify('#########'),
            'email'             => fake()->unique()->safeEmail(),
            'status'            => $status->value,
            'admin_notes'       => $status === PpdbApplicantStatus::NeedRevision
                ? 'Foto KTP orang tua buram, upload ulang yang lebih jelas.'
                : ($status === PpdbApplicantStatus::Rejected
                    ? 'Nilai seleksi di bawah batas minimum.'
                    : null),
        ]);

        // Buat dokumen untuk setiap pendaftar
        $docStatuses = match ($status) {
            PpdbApplicantStatus::Pending        => ['pending', 'pending', 'pending'],
            PpdbApplicantStatus::NeedRevision   => ['valid', 'invalid', 'pending'],
            default                             => ['valid', 'valid', 'valid'],
        };

        $docTypes = array_keys(PpdbDocument::$docTypes);
        foreach (array_slice($docTypes, 0, 3) as $i => $docType) {
            PpdbDocument::create([
                'applicant_id'  => $applicant->id,
                'doc_type'      => $docType,
                'file_path'     => "ppdb/documents/dummy_{$docType}.jpg",
                'original_name' => "{$docType}_dummy.jpg",
                'status'        => $docStatuses[$i] ?? 'valid',
                'verified_by'   => in_array($docStatuses[$i], ['valid', 'invalid'])
                    ? User::role('Admin')->first()?->id
                    : null,
                'verified_at'   => in_array($docStatuses[$i], ['valid', 'invalid'])
                    ? now()->subDays(2)
                    : null,
            ]);
        }

        // Buat data seragam
        $uniformData = [
            'applicant_id'  => $applicant->id,
            'gender'        => $gender,
            'ukuran'        => $ukuran,
            'pakai_kerudung' => false,
            'jenis_bawahan'  => 'celana',
        ];

        if ($gender === 'perempuan') {
            $uniformData['pakai_kerudung'] = fake()->boolean(70); // 70% perempuan berkerudung
            $uniformData['jenis_bawahan']  = $uniformData['pakai_kerudung'] ? 'rok' : fake()->randomElement(['rok', 'celana']);
        }

        PpdbUniformOrder::create($uniformData);

        // Buat nilai seleksi jika sudah melewati tahap verifikasi
        if (in_array($status->value, ['selected', 'rejected', 're_registered'])) {
            foreach (['matematika', 'bahasa_indonesia', 'ipa'] as $scoreType) {
                PpdbSelectionScore::create([
                    'applicant_id' => $applicant->id,
                    'score_type'   => $scoreType,
                    'score_value'  => fake()->numberBetween(
                        $status === PpdbApplicantStatus::Rejected ? 40 : 65,
                        100
                    ),
                    'inputted_by'  => User::role('Admin')->first()?->id,
                ]);
            }
        }

        // Buat data daftar ulang untuk yang sudah lolos dan menyelesaikan proses
        if ($withReregistration && $status === PpdbApplicantStatus::ReRegistered) {
            $reregistration = PpdbReregistration::create([
                'applicant_id' => $applicant->id,
                'status'       => PpdbReregistrationStatus::Completed->value,
                'completed_at' => now()->subDays(fake()->numberBetween(1, 10)),
                'notes'        => 'Daftar ulang selesai. Data siswa telah dibuat.',
            ]);
        }

        return $applicant;
    }
}
