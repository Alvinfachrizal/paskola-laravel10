@extends('layouts.app-bootstrap')

@section('header')
    <h2 class="h3 mb-1 text-dark fw-bold" style="letter-spacing:-0.5px;">Data Guru</h2>
    <p class="text-muted mb-0" style="font-size:0.875rem;">Kelola data guru dan tenaga pengajar</p>
@endsection

@section('header_action')
    <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary d-flex align-items-center gap-2 rounded-pill px-4 shadow-sm transition-hover">
        <i class="bi bi-plus-lg"></i> Tambah Guru
    </a>
@endsection

@section('content')
@php
    $totalGuru = $teachers->count();
    $guruAktif = $teachers->where('status', 'aktif')->count();
    $guruPNS = $teachers->where('employment_type', 'PNS')->count();
    $guruHonorer = $teachers->where('employment_type', 'Honorer')->count();
@endphp

<!-- Stats Row -->
<div class="row g-3 g-md-4 mb-4">
    <!-- Stat 1 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; font-size:1.25rem;">
                <i class="bi bi-person"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0 fs-4">{{ $totalGuru }}</h3>
                <p class="text-muted mb-0" style="font-size:0.75rem;">Total Guru</p>
            </div>
        </div>
    </div>
    <!-- Stat 2 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3">
            <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; font-size:1.25rem;">
                <i class="bi bi-award"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0 fs-4">{{ $guruAktif }}</h3>
                <p class="text-muted mb-0" style="font-size:0.75rem;">Aktif</p>
            </div>
        </div>
    </div>
    <!-- Stat 3 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3">
            <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; font-size:1.25rem;">
                <i class="bi bi-briefcase"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0 fs-4">{{ $guruPNS ?: '-' }}</h3>
                <p class="text-muted mb-0" style="font-size:0.75rem;">PNS</p>
            </div>
        </div>
    </div>
    <!-- Stat 4 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3">
            <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; font-size:1.25rem;">
                <i class="bi bi-person-badge"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0 fs-4">{{ $guruHonorer ?: '-' }}</h3>
                <p class="text-muted mb-0" style="font-size:0.75rem;">Honorer</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark" style="font-size:0.95rem;">Daftar Guru</h6>
                <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;" title="Refresh Data">
                    <i class="bi bi-arrow-repeat text-muted"></i>
                </button>
            </div>
            
            <div class="card-body px-4 pb-4 pt-2">
                <!-- Filter Row -->
                <div class="row g-2 mb-4">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control bg-light border-start-0 rounded-end-pill py-2" placeholder="Cari nama, NIP..." style="font-size:0.875rem;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select bg-light rounded-pill py-2 text-muted border-light" style="font-size:0.875rem;">
                            <option>Semua Status</option>
                            <option>Aktif</option>
                            <option>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="table-responsive rounded-3 border">
                    <table class="table table-hover table-borderless align-middle mb-0" style="min-width:800px;">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th class="ps-4 text-muted fw-semibold" style="font-size:0.75rem; letter-spacing:0.5px;">GURU</th>
                                <th class="text-muted fw-semibold" style="font-size:0.75rem; letter-spacing:0.5px;">NIP</th>
                                <th class="text-muted fw-semibold" style="font-size:0.75rem; letter-spacing:0.5px;">L/P</th>
                                <th class="text-muted fw-semibold" style="font-size:0.75rem; letter-spacing:0.5px;">MATA PELAJARAN</th>
                                <th class="text-muted fw-semibold" style="font-size:0.75rem; letter-spacing:0.5px;">STATUS</th>
                                <th class="text-end pe-4 text-muted fw-semibold" style="font-size:0.75rem; letter-spacing:0.5px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($teachers as $teacher)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size:0.9rem;">
                                                {{ strtoupper(substr($teacher->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size:0.875rem;">{{ $teacher->name }}</div>
                                                <div class="text-muted" style="font-size:0.75rem;">{{ $teacher->user->email ?? 'Belum ada email' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-dark" style="font-size:0.875rem;">{{ $teacher->nip ?: '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-dark" style="font-size:0.875rem;">{{ $teacher->gender === 'L' ? 'Laki-laki' : ($teacher->gender === 'P' ? 'Perempuan' : '-') }}</span>
                                    </td>
                                    <td>
                                        <span class="text-dark" style="font-size:0.875rem;">{{ $teacher->subject_specialty ?: '-' }}</span>
                                    </td>
                                    <td>
                                        @if($teacher->status === 'aktif' || $teacher->status === 'active')
                                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 fw-medium border border-success-subtle">Aktif</span>
                                        @else
                                            <span class="badge bg-light text-muted rounded-pill px-3 py-2 fw-medium border border-light">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center ms-auto" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:32px;height:32px;">
                                                <i class="bi bi-three-dots-vertical text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                                <li><a class="dropdown-item" href="{{ route('admin.teachers.edit', $teacher->id) }}"><i class="bi bi-pencil me-2 text-primary"></i>Edit Data</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('admin.teachers.destroy', $teacher->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus guru ini beserta akun penggunanya?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Hapus Data</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <div class="bg-light rounded-4 d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                                <i class="bi bi-person-slash text-muted" style="font-size: 2rem;"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">Belum ada data guru</h6>
                                            <p class="text-muted mb-0" style="font-size:0.875rem;">Klik 'Tambah Guru' untuk menambahkan</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .transition-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .transition-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    }
    
    /* Customizing input group for search */
    .input-group-text, .form-control, .form-select {
        border-color: #f1f5f9;
    }
    .form-control:focus, .form-select:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        border-color: #0d6efd;
    }
    .input-group:focus-within .input-group-text,
    .input-group:focus-within .form-control {
        background-color: #fff !important;
        border-color: #0d6efd;
    }
</style>
@endsection
