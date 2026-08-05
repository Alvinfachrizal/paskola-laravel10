<?php

namespace App\Http\Controllers\Finance;

use App\Enums\PaymentStatus;
use App\Enums\StudentBillStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Tampilkan daftar pembayaran yang menunggu verifikasi (Untuk Admin).
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending'); // Default: yang pending saja
        
        $query = Payment::with(['studentBill.billType', 'studentBill.student.user', 'verifier'])
            ->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $payments = $query->paginate(20);

        return view('finance.payments.index', compact('payments', 'status'));
    }

    /**
     * Siswa upload bukti pembayaran untuk suatu tagihan.
     */
    public function store(Request $request, StudentBill $bill)
    {
        // Validasi: tagihan belum lunas
        if ($bill->status === StudentBillStatus::Lunas) {
            return back()->withErrors(['Tagihan ini sudah lunas.']);
        }

        // Cek apakah ada pembayaran yang sedang menunggu verifikasi
        $pendingPayment = $bill->payments()->where('status', PaymentStatus::Pending->value)->first();
        if ($pendingPayment) {
            return back()->withErrors(['Anda sudah mengupload bukti bayar dan sedang menunggu verifikasi admin.']);
        }

        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:1',
            'proof_file'  => 'required|image|mimes:jpeg,png,jpg|max:2048', // Maks 2MB
        ]);

        $path = $request->file('proof_file')->store('payments', 'public');

        Payment::create([
            'student_bill_id' => $bill->id,
            'method'          => 'manual',
            'proof_file'      => $path,
            'amount_paid'     => $validated['amount_paid'],
            'status'          => PaymentStatus::Pending->value,
        ]);

        // Update status tagihan jadi menunggu verifikasi
        $bill->update(['status' => StudentBillStatus::MenungguVerifikasi->value]);

        return back()->with('success', 'Bukti pembayaran berhasil diupload. Silakan tunggu verifikasi dari Admin.');
    }

    /**
     * Admin memverifikasi pembayaran (Setuju / Tolak).
     */
    public function verify(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'action'           => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:500',
        ]);

        $bill = $payment->studentBill;
        $user = Auth::user();

        if ($validated['action'] === 'approve') {
            $payment->update([
                'status'      => PaymentStatus::Verified->value,
                'verified_by' => $user->id,
                'verified_at' => now(),
            ]);

            // Jika jumlah bayar sesuai (atau lebih), set lunas.
            // Jika kurang, bisa buat logika "cicilan", tapi untuk MVP kita set Lunas atau Belum Bayar
            $bill->update(['status' => StudentBillStatus::Lunas->value]);

            $msg = 'Pembayaran berhasil diverifikasi dan tagihan dinyatakan lunas.';
        } else {
            $payment->update([
                'status'           => PaymentStatus::Rejected->value,
                'verified_by'      => $user->id,
                'verified_at'      => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            // Kembalikan status tagihan ke belum_bayar
            $bill->update(['status' => StudentBillStatus::BelumBayar->value]);
            
            $msg = 'Pembayaran ditolak. Siswa harus mengupload ulang.';
        }

        return back()->with('success', $msg);
    }
}
