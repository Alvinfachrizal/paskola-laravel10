<?php

namespace App\Http\Controllers\Finance;

use App\Enums\PaymentStatus;
use App\Enums\StudentBillStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\StudentBill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceReportController extends Controller
{
    /**
     * Tampilkan Dashboard Rekap Keuangan untuk Admin.
     */
    public function index(Request $request)
    {
        // 1. Statistik Global (Bulan Berjalan)
        $currentMonth = Carbon::now()->format('Y-m');

        // Total ditagihkan bulan ini (termasuk tagihan awal & SPP)
        $totalBilledThisMonth = StudentBill::where(function($query) use ($currentMonth) {
                $query->where('period', $currentMonth)
                      ->orWhere(function($q) use ($currentMonth) {
                          $q->whereNull('period')
                            ->whereMonth('created_at', substr($currentMonth, 5, 2))
                            ->whereYear('created_at', substr($currentMonth, 0, 4));
                      });
            })->sum('amount');

        // Total uang masuk bulan ini (berdasarkan verified_at payment)
        $totalReceivedThisMonth = Payment::where('status', PaymentStatus::Verified->value)
            ->whereMonth('verified_at', substr($currentMonth, 5, 2))
            ->whereYear('verified_at', substr($currentMonth, 0, 4))
            ->sum('amount_paid');

        // Total yang masih menunggak (seluruh periode)
        $totalUnpaid = StudentBill::whereIn('status', [
            StudentBillStatus::BelumBayar->value, 
            StudentBillStatus::Terlambat->value
        ])->sum('amount');

        // Jumlah pembayaran menunggu verifikasi
        $pendingVerifications = Payment::where('status', PaymentStatus::Pending->value)->count();

        // 2. Chart Pendapatan 6 Bulan Terakhir
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $payments = Payment::where('status', PaymentStatus::Verified->value)
            ->where('verified_at', '>=', $sixMonthsAgo)
            ->get(['verified_at', 'amount_paid']);

        $revenueData = [];
        foreach ($payments as $payment) {
            $month = Carbon::parse($payment->verified_at)->format('Y-m');
            if (!isset($revenueData[$month])) {
                $revenueData[$month] = 0;
            }
            $revenueData[$month] += $payment->amount_paid;
        }

        // Siapkan array kosong untuk 6 bulan agar chart rata
        $chartLabels = [];
        $chartValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStr = Carbon::now()->subMonths($i)->format('Y-m');
            // Konversi misal '2026-08' -> 'Agustus'
            $chartLabels[] = Carbon::createFromFormat('Y-m', $monthStr)->translatedFormat('F');
            $chartValues[] = $revenueData[$monthStr] ?? 0;
        }

        return view('finance.reports.index', compact(
            'totalBilledThisMonth',
            'totalReceivedThisMonth',
            'totalUnpaid',
            'pendingVerifications',
            'chartLabels',
            'chartValues'
        ));
    }
}
