@extends('layouts.app-bootstrap')

@section('title', 'Tagihan & Pembayaran')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-1 fw-bold">
            <i class="bi bi-wallet2 text-primary me-2"></i>Tagihan & Pembayaran
        </h2>
        <p class="text-muted mb-0">Informasi tagihan sekolah untuk {{ $student->name }} (NISN: {{ $student->nisn }})</p>
    </div>
</div>
@endsection

@section('content')

{{-- Info Ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-danger text-white">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2"><i class="bi bi-x-circle fs-4"></i></div>
                <div>
                    <div class="small opacity-75">Belum Dibayar</div>
                    <div class="fw-bold fs-5">Rp {{ number_format($bills->whereIn('status', ['belum_bayar', 'terlambat'])->sum('amount'), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-warning text-dark">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2"><i class="bi bi-hourglass-split fs-4"></i></div>
                <div>
                    <div class="small opacity-75">Menunggu Verifikasi</div>
                    <div class="fw-bold fs-5">Rp {{ number_format($bills->where('status', 'menunggu_verifikasi')->sum('amount'), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-success text-white">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2"><i class="bi bi-check-circle fs-4"></i></div>
                <div>
                    <div class="small opacity-75">Sudah Lunas</div>
                    <div class="fw-bold fs-5">Rp {{ number_format($bills->where('status', 'lunas')->sum('amount'), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
        <h5 class="fw-bold mb-0">Daftar Tagihan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3 py-3">Nama Tagihan</th>
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
                                <div class="fw-semibold text-dark">{{ $bill->billType->name }}</div>
                                @if($bill->periodLabel())
                                    <div class="text-muted small">Periode: {{ $bill->periodLabel() }}</div>
                                @endif
                            </td>
                            <td>
                                @if($bill->due_date)
                                    <span class="{{ $bill->due_date->isPast() && !$bill->isPaid() ? 'text-danger fw-semibold' : '' }}">
                                        {{ $bill->due_date->translatedFormat('d F Y') }}
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
                                    <i class="bi {{ $bill->status->icon() }} me-1"></i>
                                    {{ $bill->status->label() }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('finance.bills.show', $bill) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                    Detail / Bayar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-wallet2 display-4 d-block mb-3 opacity-25"></i>
                                Belum ada tagihan untuk saat ini.
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
