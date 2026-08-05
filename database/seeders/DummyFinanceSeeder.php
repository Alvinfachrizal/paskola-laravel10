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
use Illuminate\Support\Str;

class DummyFinanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = School::first();
        if (!$school) {
            $this->command->error('Tidak ada data School. Seeder dibatalkan.');
            return;
        }

        $this->command->info('Membersihkan data dummy lama...');
        $dummyUserIds = User::where('email', 'like', 'siswa%@sekolah.com')->pluck('id');
        StudentBill::whereIn('student_id', Student::whereIn('user_id', $dummyUserIds)->pluck('id'))->delete();
        Student::whereIn('user_id', $dummyUserIds)->delete();
        User::whereIn('id', $dummyUserIds)->delete();

        $this->command->info('Membuat 5 Siswa Dummy...');
        $students = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => "Siswa Dummy $i",
                'email' => "siswa$i@sekolah.com",
                'password' => bcrypt('password'),
                'role' => 'Siswa',
                'school_id' => $school->id,
            ]);
            $user->assignRole('Siswa');

            // Kita mute event agar tidak memicu StudentObserver secara ganda untuk keperluan seeder ini
            $student = Student::withoutEvents(function () use ($user, $school, $i) {
                return Student::create([
                    'user_id' => $user->id,
                    'school_id' => $school->id,
                    'nisn' => '000000000' . $i,
                    'name' => "Siswa Dummy $i",
                    'gender' => $i % 2 === 0 ? 'P' : 'L',
                    'birth_place' => 'Jakarta',
                    'birth_date' => Carbon::now()->subYears(15),
                    'parent_name' => "Orang Tua $i",
                    'parent_phone' => '08123456789' . $i,
                    'entry_year' => Carbon::now()->year,
                    'status' => 'aktif',
                ]);
            });
            $students[] = $student;
        }

        $billTypes = BillType::where('school_id', $school->id)->get();
        $spp = $billTypes->where('name', 'SPP')->first();
        $uangGedung = $billTypes->where('name', 'Uang Gedung')->first();

        $admin = User::where('role', 'Super Admin')->orWhere('role', 'Admin')->first();
        $adminId = $admin ? $admin->id : null;

        $this->command->info('Membuat Tagihan & Pembayaran...');

        // Kita buat riwayat 6 bulan ke belakang
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = Carbon::now()->subMonths($i);
        }

        foreach ($students as $index => $student) {
            // -- 1. Buat Tagihan Awal (Uang Gedung)
            if ($uangGedung) {
                $gedungBill = StudentBill::create([
                    'student_id' => $student->id,
                    'bill_type_id' => $uangGedung->id,
                    'period' => null,
                    'amount' => $uangGedung->default_amount,
                    'due_date' => Carbon::now()->startOfYear(),
                    'status' => StudentBillStatus::Lunas->value,
                ]);

                // Bayar lunas 5 bulan lalu
                Payment::create([
                    'student_bill_id' => $gedungBill->id,
                    'method' => 'manual',
                    'proof_file' => 'dummy/proof.jpg',
                    'amount_paid' => $gedungBill->amount,
                    'status' => PaymentStatus::Verified->value,
                    'verified_by' => $adminId,
                    'verified_at' => Carbon::now()->subMonths(5),
                    'created_at' => Carbon::now()->subMonths(5)->subDays(2),
                ]);
            }

            // -- 2. Buat Tagihan SPP Bulanan
            if ($spp) {
                foreach ($months as $key => $month) {
                    $dueDate = $month->copy()->endOfMonth();
                    
                    $bill = StudentBill::create([
                        'student_id' => $student->id,
                        'bill_type_id' => $spp->id,
                        'period' => $month->format('Y-m'),
                        'amount' => $spp->default_amount,
                        'due_date' => $dueDate,
                        'status' => StudentBillStatus::BelumBayar->value, // akan diupdate nanti
                        'created_at' => $month->copy()->startOfMonth(),
                    ]);

                    // Skenario:
                    // Siswa 1 & 2: Lunas semua kecuali bulan ini (pending)
                    // Siswa 3: Lunas sampai bulan lalu, bulan ini belum
                    // Siswa 4: Nunggak 2 bulan terakhir, bulan ini rejected
                    // Siswa 5: Nunggak semua

                    $paymentStatus = null;
                    $paymentDate = $dueDate->copy()->subDays(rand(1, 5));

                    if ($index < 2) { // Siswa 1 & 2
                        if ($key == 5) { // Bulan ini
                            $paymentStatus = 'pending';
                        } else {
                            $paymentStatus = 'verified';
                        }
                    } elseif ($index == 2) { // Siswa 3
                        if ($key < 5) {
                            $paymentStatus = 'verified';
                        }
                    } elseif ($index == 3) { // Siswa 4
                        if ($key < 4) {
                            $paymentStatus = 'verified';
                        } elseif ($key == 5) { // Bulan ini ditolak
                            $paymentStatus = 'rejected';
                        }
                    }

                    if ($paymentStatus === 'verified') {
                        $bill->update(['status' => StudentBillStatus::Lunas->value]);
                        Payment::create([
                            'student_bill_id' => $bill->id,
                            'method' => 'manual',
                            'proof_file' => 'dummy/proof.jpg',
                            'amount_paid' => $bill->amount,
                            'status' => PaymentStatus::Verified->value,
                            'verified_by' => $adminId,
                            'verified_at' => $paymentDate->copy()->addDay(),
                            'created_at' => $paymentDate,
                        ]);
                    } elseif ($paymentStatus === 'pending') {
                        $bill->update(['status' => StudentBillStatus::MenungguVerifikasi->value]);
                        Payment::create([
                            'student_bill_id' => $bill->id,
                            'method' => 'manual',
                            'proof_file' => 'dummy/proof.jpg',
                            'amount_paid' => $bill->amount,
                            'status' => PaymentStatus::Pending->value,
                            'created_at' => $paymentDate,
                        ]);
                    } elseif ($paymentStatus === 'rejected') {
                        Payment::create([
                            'student_bill_id' => $bill->id,
                            'method' => 'manual',
                            'proof_file' => 'dummy/proof.jpg',
                            'amount_paid' => $bill->amount,
                            'status' => PaymentStatus::Rejected->value,
                            'verified_by' => $adminId,
                            'verified_at' => $paymentDate->copy()->addDay(),
                            'rejection_reason' => 'Bukti transfer buram dan tidak terbaca nominalnya.',
                            'created_at' => $paymentDate,
                        ]);
                        // Status bill tetap belum bayar atau terlambat
                        if ($dueDate->isPast()) {
                            $bill->update(['status' => StudentBillStatus::Terlambat->value]);
                        }
                    } else {
                        // Tidak ada payment (Belum bayar / Terlambat)
                        if ($dueDate->isPast()) {
                            $bill->update(['status' => StudentBillStatus::Terlambat->value]);
                        }
                    }
                }
            }
        }

        $this->command->info('Data Dummy Keuangan berhasil dibuat!');
    }
}
