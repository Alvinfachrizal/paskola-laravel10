@extends('layouts.app-bootstrap')
@section('title', 'Rekap Kebutuhan Seragam PPDB')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Rekap Kebutuhan Seragam</h2>
        <p class="text-muted mb-0 small">Data untuk pemesanan massal ke konveksi</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.ppdb.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-sm rounded-3">
            <i class="bi bi-printer me-1"></i>Print Rekap
        </button>
    </div>
</div>
@endsection

@section('content')

{{-- Filter Gelombang --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.ppdb.uniform-recap') }}" class="d-flex gap-2 align-items-end">
            <div class="flex-grow-1" style="max-width:300px">
                <select name="wave_id" class="form-select form-select-sm rounded-3">
                    <option value="">Semua Gelombang</option>
                    @foreach($waves as $wave)
                        <option value="{{ $wave->id }}" {{ request('wave_id') == $wave->id ? 'selected' : '' }}>
                            {{ $wave->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm rounded-3">
                <i class="bi bi-search me-1"></i>Filter
            </button>
            <a href="{{ route('admin.ppdb.uniform-recap') }}" class="btn btn-outline-secondary btn-sm rounded-3">Reset</a>
        </form>
    </div>
</div>

{{-- Ringkasan Per Ukuran --}}
<div class="row g-3 mb-4">
    @foreach($perUkuran as $ukuran => $jumlah)
    <div class="col-6 col-md-3 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3">
            <div class="fw-bold fs-3 text-primary">{{ $ukuran }}</div>
            <div class="text-muted small">{{ $jumlah }} orang</div>
        </div>
    </div>
    @endforeach
    <div class="col-6 col-md-3 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3" style="background:#eff6ff">
            <div class="fw-bold fs-3 text-primary">{{ $totalOrders }}</div>
            <div class="text-muted small">Total</div>
        </div>
    </div>
</div>

{{-- Tabel Rekap Lengkap --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="bi bi-table me-2"></i>Detail Kebutuhan Per Kombinasi</h6>
        <span class="badge bg-secondary rounded-pill">{{ $recap->count() }} kombinasi</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:.875rem">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Ukuran</th>
                        <th>Gender</th>
                        <th>Kerudung</th>
                        <th>Jenis Bawahan</th>
                        <th class="text-end pe-4">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recap as $row)
                    <tr>
                        <td class="ps-4 fw-bold">
                            <span class="badge bg-primary rounded-pill fs-6 px-3">{{ $row['ukuran'] }}</span>
                        </td>
                        <td>
                            <span class="{{ $row['gender'] === 'perempuan' ? 'text-danger' : 'text-info' }} fw-semibold">
                                <i class="bi {{ $row['gender'] === 'perempuan' ? 'bi-gender-female' : 'bi-gender-male' }} me-1"></i>
                                {{ ucfirst($row['gender']) }}
                            </span>
                        </td>
                        <td>
                            @if($row['gender'] === 'perempuan')
                                <span class="badge {{ $row['pakai_kerudung'] ? 'bg-success' : 'bg-secondary' }} rounded-pill small">
                                    {{ $row['pakai_kerudung'] ? 'Ya' : 'Tidak' }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ ucfirst($row['jenis_bawahan']) }}</td>
                        <td class="text-end pe-4">
                            <span class="badge bg-dark rounded-pill px-3 fs-6">{{ $row['jumlah'] }} orang</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>Belum ada data seragam.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($recap->isNotEmpty())
                <tfoot class="table-light">
                    <tr>
                        <td colspan="4" class="ps-4 py-3 fw-bold">Total</td>
                        <td class="text-end pe-4 fw-bold">{{ $totalOrders }} orang</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@endsection
