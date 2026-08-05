@extends('layouts.app-bootstrap')

@section('title', 'Semua Tagihan Siswa')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-1 fw-bold">
            <i class="bi bi-wallet2 text-primary me-2"></i>Semua Tagihan Siswa
        </h2>
        <p class="text-muted mb-0">Daftar semua tagihan sekolah untuk seluruh siswa</p>
    </div>
</div>
@endsection

@section('content')

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3 py-3">Siswa</th>
                        <th class="py-3">Tagihan</th>
                        <th class="py-3">Jatuh Tempo</th>
                        <th class="py-3">Nominal</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $bill->student->name }}</div>
                                <div class="text-muted small">NISN: {{ $bill->student->nisn }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $bill->billType->name }}</div>
                                @if($bill->periodLabel())
                                    <div class="text-muted small">Periode: {{ $bill->periodLabel() }}</div>
                                @endif
                            </td>
                            <td>
                                @if($bill->due_date)
                                    <span class="{{ $bill->due_date->isPast() && !$bill->isPaid() ? 'text-danger fw-semibold' : '' }}">
                                        {{ $bill->due_date->translatedFormat('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">Rp {{ number_format($bill->amount, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $bill->status->badgeClass() }} rounded-pill px-3 py-2">
                                    {{ $bill->status->label() }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('finance.history.show', $bill->student_id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bi bi-clock-history me-1"></i>Riwayat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Belum ada tagihan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $bills->links() }}
        </div>
    </div>
</div>
@endsection
