<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Enums\StudentBillStatus;
use App\Models\BillType;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (!$school) {
            $this->command->warn('Seeder dilewati: belum ada data School.');
            return;
        }

        // ── 1. Buat Jenis Tagihan (BillTypes) ────────────────────────────────
        $billTypes = [
            [
                'name'            => 'SPP',
                'description'     => 'Sumbangan Pembinaan Pendidikan — tagihan bulanan wajib',
                'is_recurring'    => true,
                'is_initial_bill' => false,
                'default_amount'  => 350000,
            ],
            [
                'name'            => 'Uang Gedung',
                'description'     => 'Biaya pembangunan gedung sekolah — sekali bayar saat masuk',
                'is_recurring'    => false,
                'is_initial_bill' => true,
                'default_amount'  => 5000000,
            ],
            [
                'name'            => 'Seragam',
                'description'     => 'Paket seragam lengkap (putih-abu, pramuka, olahraga)',
                'is_recurring'    => false,
                'is_initial_bill' => true,
                'default_amount'  => 750000,
            ],
            [
                'name'            => 'Uang Komite',
                'description'     => 'Iuran komite sekolah — tagihan bulanan',
                'is_recurring'    => true,
                'is_initial_bill' => false,
                'default_amount'  => 100000,
            ],
            [
                'name'            => 'Buku Paket',
                'description'     => 'Paket buku pelajaran semester awal',
                'is_recurring'    => false,
                'is_initial_bill' => true,
                'default_amount'  => 600000,
            ],
        ];

        $createdBillTypes = [];
        foreach ($billTypes as $bt) {
            $createdBillTypes[] = BillType::firstOrCreate(
                ['school_id' => $school->id, 'name' => $bt['name']],
                array_merge($bt, ['school_id' => $school->id, 'is_active' => true])
            );
        }

        // Ambil referensi jenis tagihan
        $spp        = collect($createdBillTypes)->firstWhere('name', 'SPP');
        $gedung     = collect($createdBillTypes)->firstWhere('name', 'Uang Gedung');
        $seragam    = collect($createdBillTypes)->firstWhere('name', 'Seragam');
        $komite     = collect($createdBillTypes)->firstWhere('name', 'Uang Komite');
        $buku       = collect($createdBillTypes)->firstWhere('name', 'Buku Paket');

        $this->command->info('✅ 5 jenis tagihan berhasil dibuat.');

        // ── 2. Ambil Siswa Demo (gunakan yang sudah ada dari seeder lain) ─────
        $students = Student::where('school_id', $school->id)->take(3)->get();

        if ($students->isEmpty()) {
            $this->command->warn('Tidak ada data siswa untuk dibuat tagihan demo. Jalankan seeder siswa terlebih dahulu.');
            return;
        }

        $admin = User::where('role', 'Admin')->first()
                  ?? User::where('role', 'Super Admin')->first();

        foreach ($students as $index => $student) {
            // ── Tagihan One-time (Initial) ─────────────────────────────────
            $initials = [
                ['bt' => $gedung,  'status' => StudentBillStatus::Lunas,          'paid' => true],
                ['bt' => $seragam, 'status' => StudentBillStatus::Lunas,          'paid' => true],
                ['bt' => $buku,    'status' => StudentBillStatus::BelumBayar,     'paid' => false],
            ];

            foreach ($initials as $item) {
                $bill = StudentBill::firstOrCreate(
                    ['student_id' => $student->id, 'bill_type_id' => $item['bt']->id, 'period' => null],
                    [
                        'amount'   => $item['bt']->default_amount,
                        'due_date' => Carbon::now()->startOfMonth()->addDays(30),
                        'status'   => $item['status']->value,
                    ]
                );

                if ($item['paid'] && $bill->wasRecentlyCreated) {
                    Payment::create([
                        'student_bill_id' => $bill->id,
                        'method'          => 'manual',
                        'proof_file'      => 'demo/bukti-transfer-dummy.jpg',
                        'amount_paid'     => $bill->amount,
                        'status'          => PaymentStatus::Verified->value,
                        'verified_by'     => $admin?->id,
                        'verified_at'     => now()->subDays(rand(5, 30)),
                    ]);
                }
            }

            // ── Tagihan SPP 3 bulan terakhir ──────────────────────────────
            $sppScenarios = [
                ['period' => Carbon::now()->subMonths(2)->format('Y-m'), 'status' => StudentBillStatus::Lunas,                'paid' => true],
                ['period' => Carbon::now()->subMonth()->format('Y-m'),   'status' => StudentBillStatus::Lunas,                'paid' => true],
                ['period' => Carbon::now()->format('Y-m'),               'status' => ($index === 0 ? StudentBillStatus::MenungguVerifikasi : StudentBillStatus::BelumBayar), 'paid' => ($index === 0)],
            ];

            foreach ($sppScenarios as $scenario) {
                $bill = StudentBill::firstOrCreate(
                    ['student_id' => $student->id, 'bill_type_id' => $spp->id, 'period' => $scenario['period']],
                    [
                        'amount'   => $spp->default_amount,
                        'due_date' => Carbon::parse($scenario['period'] . '-10'),
                        'status'   => $scenario['status']->value,
                    ]
                );

                if ($scenario['paid'] && $bill->wasRecentlyCreated) {
                    Payment::create([
                        'student_bill_id' => $bill->id,
                        'method'          => 'manual',
                        'proof_file'      => 'demo/bukti-spp-dummy.jpg',
                        'amount_paid'     => $bill->amount,
                        'status'          => $scenario['status'] === StudentBillStatus::Lunas
                            ? PaymentStatus::Verified->value
                            : PaymentStatus::Pending->value,
                        'verified_by'     => $scenario['status'] === StudentBillStatus::Lunas ? $admin?->id : null,
                        'verified_at'     => $scenario['status'] === StudentBillStatus::Lunas ? now()->subDays(rand(1, 10)) : null,
                    ]);
                }
            }

            // ── Tagihan Uang Komite bulan ini ─────────────────────────────
            StudentBill::firstOrCreate(
                ['student_id' => $student->id, 'bill_type_id' => $komite->id, 'period' => Carbon::now()->format('Y-m')],
                [
                    'amount'   => $komite->default_amount,
                    'due_date' => Carbon::now()->endOfMonth(),
                    'status'   => StudentBillStatus::BelumBayar->value,
                ]
            );
        }

        $this->command->info("✅ Tagihan demo berhasil dibuat untuk {$students->count()} siswa.");
    }
}
