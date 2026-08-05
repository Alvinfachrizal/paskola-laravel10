<?php

namespace App\Console\Commands;

use App\Enums\StudentBillStatus;
use App\Models\BillType;
use App\Models\Student;
use App\Models\StudentBill;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:generate-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate tagihan berulang (seperti SPP) untuk bulan ini';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai generate tagihan bulanan...');
        
        $currentPeriod = Carbon::now()->format('Y-m'); // e.g. "2026-08"
        $dueDate       = Carbon::now()->endOfMonth();  // Jatuh tempo akhir bulan

        // Ambil semua tagihan yang berstatus recurring dan aktif
        $recurringBills = BillType::active()->recurring()->get();
        
        if ($recurringBills->isEmpty()) {
            $this->info('Tidak ada jenis tagihan berulang yang aktif. Selesai.');
            return;
        }

        // Ambil semua siswa aktif
        // Asumsi 'status' siswa = 'aktif'
        $students = Student::where('status', 'aktif')->get();

        if ($students->isEmpty()) {
            $this->info('Tidak ada siswa aktif. Selesai.');
            return;
        }

        $count = 0;

        foreach ($students as $student) {
            foreach ($recurringBills as $billType) {
                // Pastikan jenis tagihan berada di sekolah yang sama dengan siswa
                if ($billType->school_id !== $student->school_id) {
                    continue;
                }

                // Gunakan firstOrCreate untuk mencegah duplikasi periode yang sama
                $bill = StudentBill::firstOrCreate(
                    [
                        'student_id'   => $student->id,
                        'bill_type_id' => $billType->id,
                        'period'       => $currentPeriod,
                    ],
                    [
                        'amount'       => $billType->default_amount,
                        'due_date'     => $dueDate,
                        'status'       => StudentBillStatus::BelumBayar->value,
                        'notes'        => 'Tagihan otomatis dibuat oleh sistem.',
                    ]
                );

                if ($bill->wasRecentlyCreated) {
                    $count++;
                }
            }
        }

        $this->info("Berhasil membuat {$count} tagihan baru untuk periode {$currentPeriod}.");
    }
}
