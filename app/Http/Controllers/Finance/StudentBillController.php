<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentBillController extends Controller
{
    /**
     * Tampilkan daftar tagihan.
     * Jika login sebagai Siswa/Ortu: tampilkan tagihan milik mereka sendiri.
     * Jika Admin: tampilkan semua (bisa dengan filter kedepannya).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = StudentBill::with(['billType', 'student.user', 'latestPayment'])
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc');

        if ($user->hasRole('Siswa') || $user->hasRole('Orang Tua')) {
            // Ambil student_id dari user login
            $student = Student::where('user_id', $user->id)->first();
            
            if (!$student) {
                return back()->withErrors(['Akses ditolak: Data siswa tidak ditemukan.']);
            }

            $query->where('student_id', $student->id);
            $bills = $query->paginate(15);

            return view('finance.bills.student-index', compact('bills', 'student'));
        }

        // Untuk Admin
        $bills = $query->paginate(20);
        return view('finance.bills.admin-index', compact('bills'));
    }

    /**
     * Tampilkan detail tagihan dan form bayar (untuk siswa).
     */
    public function show(StudentBill $bill)
    {
        $user = Auth::user();

        if ($user->hasRole('Siswa') || $user->hasRole('Orang Tua')) {
            $student = Student::where('user_id', $user->id)->first();
            if ($bill->student_id !== $student?->id) {
                abort(403, 'Akses ditolak.');
            }
        }

        $bill->load(['billType', 'student.user', 'payments.verifier']);

        return view('finance.bills.show', compact('bill'));
    }
}
