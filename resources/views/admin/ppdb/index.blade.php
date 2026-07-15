@extends('layouts.app-bootstrap')
@section('title', 'PPDB — Daftar Pendaftar')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center w-100 gap-2">
    <div>
        <h2 class="h3 mb-1 fw-bold" style="letter-spacing:-.5px">Manajemen PPDB</h2>
        <p class="text-muted mb-0 small">Penerimaan Peserta Didik Baru — Tahun Ajaran {{ date('Y') }}/{{ date('Y')+1 }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('ppdb.index') }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-3">
            <i class="bi bi-box-arrow-up-right me-1"></i>Portal Publik
        </a>
        <a href="{{ route('admin.ppdb.waves') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-layers me-1"></i>Kelola Gelombang
        </a>
        <a href="{{ route('admin.ppdb.uniform-recap') }}" class="btn btn-primary btn-sm rounded-3">
            <i class="bi bi-bag me-1"></i>Rekap Seragam
        </a>
    </div>
</div>
@endsection

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Statistik Ringkasan --}}
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'Total Pendaftar', 'value'=>$stats['total'],       'color'=>'primary',   'icon'=>'bi-people'],
        ['label'=>'Menunggu Verifikasi','value'=>$stats['pending'],   'color'=>'secondary', 'icon'=>'bi-hourglass-split'],
        ['label'=>'Terverifikasi',   'value'=>$stats['verified'],     'color'=>'info',      'icon'=>'bi-patch-check'],
        ['label'=>'Lolos Seleksi',   'value'=>$stats['selected'],     'color'=>'success',   'icon'=>'bi-trophy'],
        ['label'=>'Tidak Lolos',     'value'=>$stats['rejected'],     'color'=>'danger',    'icon'=>'bi-x-circle'],
        ['label'=>'Daftar Ulang',    'value'=>$stats['reregistered'], 'color'=>'primary',   'icon'=>'bi-person-check'],
    ] as $stat)
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100">
            <i class="bi {{ $stat['icon'] }} text-{{ $stat['color'] }} fs-4 mb-1"></i>
            <h4 class="fw-bold mb-0">{{ $stat['value'] }}</h4>
            <p class="text-muted mb-0" style="font-size:.75rem">{{ $stat['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.ppdb.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm rounded-3"
                       placeholder="Cari nama, kode, atau no HP..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="wave_id" class="form-select form-select-sm rounded-3">
                    <option value="">Semua Gelombang</option>
                    @foreach($waves as $wave)
                        <option value="{{ $wave->id }}" {{ request('wave_id') == $wave->id ? 'selected' : '' }}>
                            {{ $wave->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm rounded-3">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-3 flex-grow-1">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.ppdb.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel Pendaftar --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Daftar Pendaftar</h6>
        <span class="badge bg-secondary rounded-pill">{{ $applicants->total() }} pendaftar</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:.875rem">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Kode / Nama</th>
                        <th>Gelombang</th>
                        <th>Gender</th>
                        <th>Dokumen</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applicants as $applicant)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-semibold">{{ $applicant->full_name }}</div>
                            <div class="text-muted small" style="letter-spacing:.5px">{{ $applicant->registration_code }}</div>
                        </td>
                        <td>
                            <span class="text-muted small">{{ $applicant->wave->name ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $applicant->gender === 'perempuan' ? 'bg-pink-subtle text-danger' : 'bg-info-subtle text-info' }} rounded-pill"
                                  style="{{ $applicant->gender === 'perempuan' ? 'background:#fce7f3!important;color:#be185d!important' : '' }}">
                                <i class="bi {{ $applicant->gender === 'perempuan' ? 'bi-gender-female' : 'bi-gender-male' }} me-1"></i>
                                {{ ucfirst($applicant->gender) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $totalDocs   = $applicant->documents->count();
                                $validDocs   = $applicant->documents->where('status','valid')->count();
                                $invalidDocs = $applicant->documents->where('status','invalid')->count();
                            @endphp
                            @if($totalDocs === 0)
                                <span class="text-muted small">Belum ada</span>
                            @else
                                <span class="text-success small fw-semibold">{{ $validDocs }}✓</span>
                                @if($invalidDocs > 0)
                                    <span class="text-danger small fw-semibold ms-1">{{ $invalidDocs }}✗</span>
                                @endif
                                <span class="text-muted small ms-1">/ {{ $totalDocs }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $applicant->status->badgeClass() }} rounded-pill small">
                                {{ $applicant->status->label() }}
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('admin.ppdb.applicants.show', $applicant) }}"
                               class="btn btn-outline-primary btn-sm rounded-3">
                                <i class="bi bi-eye me-1"></i>Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Tidak ada data pendaftar yang sesuai filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($applicants->hasPages())
        <div class="px-4 py-3 border-top">
            {{ $applicants->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

@endsection
