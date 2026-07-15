@extends('layouts.app-bootstrap')
@section('title', 'Kelola Gelombang PPDB')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Gelombang Pendaftaran</h2>
        <p class="text-muted mb-0 small">Buat dan kelola jadwal gelombang PPDB</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.ppdb.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <button data-bs-toggle="modal" data-bs-target="#createWaveModal" class="btn btn-primary btn-sm rounded-3">
            <i class="bi bi-plus-lg me-1"></i>Tambah Gelombang
        </button>
    </div>
</div>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    @forelse($waves as $wave)
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="fw-bold mb-0">{{ $wave->name }}</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge {{ $wave->is_active ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                            {{ $wave->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        <button class="btn btn-outline-secondary btn-sm rounded-3"
                                data-bs-toggle="modal" data-bs-target="#editWaveModal{{ $wave->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </div>
                <p class="text-muted small mb-3">{{ $wave->description ?? '—' }}</p>
                <div class="row g-2 small">
                    <div class="col-6"><span class="text-muted d-block">Dibuka</span><strong>{{ $wave->start_date->format('d M Y') }}</strong></div>
                    <div class="col-6"><span class="text-muted d-block">Ditutup</span><strong>{{ $wave->end_date->format('d M Y') }}</strong></div>
                    <div class="col-6"><span class="text-muted d-block">Kuota</span><strong>{{ $wave->quota }}</strong></div>
                    <div class="col-6"><span class="text-muted d-block">Pendaftar</span><strong>{{ $wave->applicants_count }}</strong></div>
                    <div class="col-6"><span class="text-muted d-block">Sisa Kuota</span><strong>{{ $wave->remainingQuota() }}</strong></div>
                    <div class="col-6">
                        <span class="text-muted d-block">Biaya</span>
                        <strong>{{ $wave->hasFee() ? 'Rp ' . number_format($wave->registration_fee,0,',','.') : 'Gratis' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Edit --}}
        <div class="modal fade" id="editWaveModal{{ $wave->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Edit {{ $wave->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('admin.ppdb.waves.update', $wave) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-body">
                            @include('admin.ppdb._wave-form', ['wave' => $wave])
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary rounded-3">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
            <i class="bi bi-layers fs-1 mb-3"></i>
            <p>Belum ada gelombang pendaftaran. Klik "Tambah Gelombang" untuk memulai.</p>
        </div>
    </div>
    @endforelse
</div>

{{-- Modal Tambah Gelombang --}}
<div class="modal fade" id="createWaveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Tambah Gelombang Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.ppdb.waves.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @include('admin.ppdb._wave-form', ['wave' => null])
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3">Buat Gelombang</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
