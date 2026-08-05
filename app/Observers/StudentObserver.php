<?php

namespace App\Observers;

use App\Enums\StudentBillStatus;
use App\Models\BillType;
use App\Models\Student;
use App\Models\StudentBill;
use Carbon\Carbon;

class StudentObserver
{
    /**
     * Handle the Student "created" event.
     * Akan otomatis dipanggil setiap kali ada data Student baru yang dibuat,
     * termasuk saat siswa baru didaftarulang dari modul PPDB.
     */
    public function created(Student $student): void
    {
        // Ambil semua jenis tagihan yang ditandai sebagai "initial_bill" (tagihan awal masuk)
        // dan berstatus aktif di sekolah yang sama.
        $initialBillTypes = BillType::active()
            ->initial()
            ->where('school_id', $student->school_id)
            ->get();

        foreach ($initialBillTypes as $billType) {
            // Jatuh tempo: 30 hari dari tanggal siswa dibuat
            $dueDate = Carbon::now()->addDays(30);

            // Buat tagihan awal untuk siswa tersebut
            StudentBill::create([
                'student_id'   => $student->id,
                'bill_type_id' => $billType->id,
                'period'       => null, // Tagihan awal biasanya tidak punya periode bulan
                'amount'       => $billType->default_amount,
                'due_date'     => $dueDate,
                'status'       => StudentBillStatus::BelumBayar->value,
                'notes'        => 'Dibuat otomatis saat siswa baru terdaftar.',
            ]);
        }
    }

    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "deleted" event.
     */
    public function deleted(Student $student): void
    {
        //
    }
}
