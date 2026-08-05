<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\StudentFinanceService;
use Illuminate\Http\Request;

class StudentFinanceController extends Controller
{
    protected $financeService;

    public function __construct(StudentFinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * Tampilkan riwayat keuangan gabungan (PPDB + Sekolah) untuk seorang siswa.
     */
    public function show(Student $student)
    {
        // Ambil rekap history gabungan
        $history = $this->financeService->getFullHistory($student->id);
        
        // Ambil summary
        $summary = $this->financeService->getSummary($student->id);

        return view('finance.student-history.show', compact('student', 'history', 'summary'));
    }
}
