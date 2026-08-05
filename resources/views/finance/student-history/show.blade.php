@extends('layouts.app-bootstrap')

@section('title', 'Riwayat Keuangan Siswa')

@section('header')
<div class="d-flex align-items-center gap-3">
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="h4 mb-1 fw-bold">Riwayat Keuangan</h2>
        <p class="text-muted mb-0">{{ $student->name }} (NISN: {{ $student->nisn }})</p>
    </div>
</div>
@endsection

@section('content')

{{-- Info Ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2"><i class="bi bi-receipt fs-4"></i></div>
                <div>
                    <div class="small opacity-75">Total Tagihan</div>
                    <div class="fw-bold fs-5">Rp {{ number_format($summary['total_billed'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 bg-success text-white h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2"><i class="bi bi-check-circle fs-4"></i></div>
                <div>
                    <div class="small opacity-75">Sudah Dibayar</div>
                    <div class="fw-bold fs-5">Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 bg-danger text-white h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2"><i class="bi bi-x-circle fs-4"></i></div>
                <div>
                    <div class="small opacity-75">Sisa Tunggakan</div>
                    <div class="fw-bold fs-5">Rp {{ number_format($summary['total_unpaid'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 bg-warning text-dark h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2"><i class="bi bi-hourglass-split fs-4"></i></div>
                <div>
                    <div class="small opacity-75">Menunggu Verifikasi</div>
                    <div class="fw-bold fs-5">{{ $summary['pending_verification'] }} Item</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
        <h5 class="fw-bold mb-0">Timeline Pembayaran & Tagihan (PPDB + Sekolah)</h5>
    </div>
    <div class="card-body">
        @if($history->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-clock-history display-4 d-block mb-3 opacity-25"></i>
                Belum ada riwayat keuangan untuk siswa ini.
            </div>
        @else
            <div class="timeline mt-3 px-2">
                @foreach($history as $item)
                    <div class="d-flex mb-4 position-relative">
                        {{-- Garis konektor (kecuali item terakhir) --}}
                        @if(!$loop->last)
                            <div class="position-absolute bg-light" style="width: 2px; height: 100%; left: 15px; top: 32px; z-index: 1;"></div>
                        @endif

                        <div class="me-3 mt-1 position-relative" style="z-index: 2;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white {{ $item['badge_class'] }}" style="width: 32px; height: 32px;">
                                @if($item['source'] === 'ppdb')
                                    <i class="bi bi-clipboard-check"></i>
                                @else
                                    <i class="bi bi-receipt"></i>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex-grow-1 bg-light rounded-4 p-3 border">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                                <div class="fw-bold fs-6">
                                    {{ $item['title'] }}
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::parse($item['date'])->translatedFormat('d F Y') }}
                                </div>
                            </div>
                            
                            <div class="mb-2 d-flex flex-wrap gap-2 align-items-center">
                                <span class="badge {{ $item['badge_class'] }} rounded-pill">
                                    {{ $item['status_label'] }}
                                </span>
                                @if($item['source'] === 'ppdb')
                                    <span class="badge bg-secondary rounded-pill"><i class="bi bi-box-arrow-in-right me-1"></i>Modul PPDB</span>
                                @endif
                            </div>

                            @if($item['description'])
                                <div class="text-muted small mb-2">{{ $item['description'] }}</div>
                            @endif

                            <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary fs-5">Rp {{ number_format($item['amount'], 0, ',', '.') }}</span>
                                
                                @if($item['source'] === 'finance' && $item['bill_id'])
                                    <a href="{{ route('finance.bills.show', $item['bill_id']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Lihat Detail
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection
