<?php

namespace App\Services;

use App\Models\PpdbPayment;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Support\Collection;

/**
 * StudentFinanceService
 *
 * Service ini menjadi "jendela tunggal" untuk melihat riwayat keuangan lengkap
 * seorang siswa — menggabungkan data dari 2 sumber:
 *   1. ppdb_payments : Biaya PPDB (pendaftaran & daftar ulang) — dari modul PPDB
 *   2. student_bills : Tagihan sekolah (SPP, uang gedung, dll) — dari modul Keuangan
 *
 * Ini adalah contoh "Separation of Concerns":
 *   - Modul PPDB tetap mencatat datanya sendiri
 *   - Modul Keuangan mencatat tagihan rutin
 *   - Service ini yang bertanggung jawab menggabungkan keduanya
 */
class StudentFinanceService
{
    /**
     * Ambil riwayat keuangan LENGKAP seorang siswa (PPDB + Keuangan Sekolah),
     * digabung dalam 1 collection, diurutkan berdasarkan tanggal terbaru.
     *
     * @param  string  $studentId  UUID siswa
     * @return Collection  Collection berisi array dengan format unified
     */
    public function getFullHistory(string $studentId): Collection
    {
        // ── SUMBER 1: Tagihan dari modul Keuangan Sekolah ──────────────────
        $bills = StudentBill::with(['billType', 'latestPayment'])
            ->where('student_id', $studentId)
            ->get()
            ->map(function (StudentBill $bill) {
                return [
                    'source'       => 'finance',           // Sumber data: modul Keuangan
                    'id'           => $bill->id,
                    'title'        => $bill->billType->name . ($bill->period ? ' — ' . $bill->periodLabel() : ''),
                    'description'  => $bill->billType->description,
                    'amount'       => (float) $bill->amount,
                    'status'       => $bill->status->value,       // belum_bayar | lunas | dll
                    'status_label' => $bill->status->label(),
                    'badge_class'  => $bill->status->badgeClass(),
                    'date'         => $bill->due_date ?? $bill->created_at,
                    'period'       => $bill->period,
                    'type'         => $bill->billType->is_recurring ? 'recurring' : 'one-time',
                    'bill_id'      => $bill->id,           // Dipakai untuk link "Bayar"
                    'payment_id'   => $bill->latestPayment?->id,
                ];
            });

        // ── SUMBER 2: Riwayat dari modul PPDB ─────────────────────────────
        $ppdbPayments = PpdbPayment::with('applicant')
            ->where('student_id', $studentId)
            ->get()
            ->map(function (PpdbPayment $payment) {
                // Map status PPDB ke format yang konsisten dengan modul keuangan
                $statusMap = [
                    'pending' => ['label' => 'Menunggu', 'badge' => 'bg-warning text-dark'],
                    'paid'    => ['label' => 'Lunas',    'badge' => 'bg-success'],
                    'failed'  => ['label' => 'Gagal',    'badge' => 'bg-danger'],
                    'expired' => ['label' => 'Kadaluarsa', 'badge' => 'bg-secondary'],
                ];
                $statusInfo = $statusMap[$payment->status->value] ?? ['label' => $payment->status->value, 'badge' => 'bg-secondary'];

                $typeLabel = match($payment->payment_type) {
                    'registration_fee'    => 'Biaya Pendaftaran PPDB',
                    're_registration_fee' => 'Biaya Daftar Ulang PPDB',
                    default               => 'Pembayaran PPDB',
                };

                return [
                    'source'       => 'ppdb',              // Sumber data: modul PPDB
                    'id'           => 'ppdb-' . $payment->id,
                    'title'        => $typeLabel,
                    'description'  => 'Gelombang PPDB: ' . ($payment->applicant->wave->name ?? '-'),
                    'amount'       => (float) $payment->amount,
                    'status'       => $payment->status->value,
                    'status_label' => $statusInfo['label'],
                    'badge_class'  => $statusInfo['badge'],
                    'date'         => $payment->paid_at ?? $payment->created_at,
                    'period'       => null,
                    'type'         => 'ppdb',
                    'bill_id'      => null,                 // Tidak ada bill_id untuk PPDB
                    'payment_id'   => $payment->id,
                ];
            });

        // ── GABUNGKAN & URUTKAN ────────────────────────────────────────────
        return $bills->concat($ppdbPayments)
            ->sortByDesc('date')
            ->values();
    }

    /**
     * Hitung ringkasan finansial seorang siswa untuk ditampilkan di kartu statistik.
     *
     * @param  string  $studentId  UUID siswa
     * @return array  ['total_billed', 'total_paid', 'total_unpaid', 'pending_verification']
     */
    public function getSummary(string $studentId): array
    {
        $bills = StudentBill::where('student_id', $studentId)->get();

        return [
            'total_billed'         => $bills->sum('amount'),
            'total_paid'           => $bills->where('status', 'lunas')->sum('amount'),
            'total_unpaid'         => $bills->whereIn('status', ['belum_bayar', 'terlambat'])->sum('amount'),
            'pending_verification' => $bills->where('status', 'menunggu_verifikasi')->count(),
        ];
    }
}
