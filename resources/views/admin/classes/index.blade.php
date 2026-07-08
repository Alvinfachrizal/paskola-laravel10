@extends('layouts.app-bootstrap')

@section('header')
    <h2 class="h3 mb-1 text-dark fw-bold" style="letter-spacing:-0.5px;">Kelas & Mata Pelajaran</h2>
    <p class="text-muted mb-0" style="font-size:0.875rem;">Kelola data kelas, mata pelajaran, dan jurusan sekolah</p>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-4">
            <!-- Tabs Header -->
            <div class="card-header bg-white border-bottom pt-3 pb-0 px-4 d-flex justify-content-between align-items-center">
                <ul class="nav nav-tabs border-bottom-0" style="gap: 1.5rem;">
                    <li class="nav-item">
                        <a class="nav-link active px-0 pb-3 border-0 border-bottom border-primary border-2 text-primary fw-bold bg-transparent" href="{{ route('admin.classes.index') }}" style="color: #0d6efd !important;">
                            <i class="bi bi-building me-2"></i>Kelas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-0 pb-3 border-0 text-muted fw-medium bg-transparent transition-hover-text" href="{{ route('admin.subjects.index') }}">
                            <i class="bi bi-book me-2"></i>Mata Pelajaran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-0 pb-3 border-0 text-muted fw-medium bg-transparent transition-hover-text" href="{{ route('admin.majors.index') }}">
                            <i class="bi bi-layers me-2"></i>Jurusan
                        </a>
                    </li>
                </ul>
                <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center mb-2" style="width:32px;height:32px;" title="Refresh Data">
                    <i class="bi bi-arrow-repeat text-muted"></i>
                </button>
            </div>
            
            <div class="card-body px-4 pb-4 pt-4">
                <!-- Filter Row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control bg-white border-start-0 py-2" placeholder="Cari nama kelas..." style="font-size:0.875rem;">
                        </div>
                        <div class="mt-2">
                            <select class="form-select bg-white rounded-pill py-2 text-muted" style="font-size:0.875rem; max-width: 300px;">
                                <option>Semua Tahun Ajaran</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mt-3 mt-md-0 d-grid d-md-flex justify-content-md-end align-items-center">
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-2 shadow-sm transition-hover fw-medium d-flex align-items-center justify-content-center gap-1" style="font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#tambahKelasModal">
                            <i class="bi bi-plus-lg"></i> Tambah Kelas
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="table-responsive rounded-3 border">
                    <table class="table table-hover table-borderless align-middle mb-0" style="min-width:800px;">
                        <thead style="background-color: #f8f6f2; border-bottom: 1px solid #e2e8f0;">
                            <tr>
                                <th class="ps-4 py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">NAMA KELAS</th>
                                <th class="py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">TINGKAT</th>
                                <th class="py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">JURUSAN</th>
                                <th class="py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">TAHUN AJARAN</th>
                                <th class="py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">KAPASITAS</th>
                                <th class="py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">RUANG</th>
                                <th class="text-end pe-4 py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($classes as $class)
                                <tr class="border-bottom">
                                    <td class="ps-4 py-3">
                                        <span class="fw-bold text-dark" style="font-size:0.9rem;">{{ $class->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-semibold" style="font-size:0.75rem;">Kelas {{ $class->grade }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted" style="font-size:0.875rem;">{{ $class->major ? $class->major->name : '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted" style="font-size:0.875rem;">{{ $class->schoolYear ? $class->schoolYear->academic_year : '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-semibold" style="font-size:0.875rem;">{{ $class->max_students ?: '-' }}</span> <span class="text-muted" style="font-size:0.875rem;">siswa</span>
                                    </td>
                                    <td>
                                        <span class="text-muted" style="font-size:0.875rem;">{{ $class->room_number ?: '-' }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.classes.edit', $class->id) }}" class="btn btn-sm bg-primary bg-opacity-10 text-primary border-0 rounded-pill px-3 fw-medium transition-hover d-flex align-items-center gap-1">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.classes.destroy', $class->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kelas ini?');" class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm bg-danger bg-opacity-10 text-danger border-0 rounded-pill px-3 fw-medium transition-hover d-flex align-items-center gap-1">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <div class="bg-light rounded-4 d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                                <i class="bi bi-building text-muted" style="font-size: 2rem;"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">Belum ada data kelas</h6>
                                            <p class="text-muted mb-0" style="font-size:0.875rem;">Tambahkan kelas terlebih dahulu</p>
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

<!-- Modal Tambah Kelas -->
<div class="modal fade" id="tambahKelasModal" tabindex="-1" aria-labelledby="tambahKelasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-bottom-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="tambahKelasModalLabel" style="font-size: 1.1rem;">Tambah Kelas</h5>
                <button type="button" class="btn-close bg-light rounded-circle" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.classes.store') }}" method="POST" id="formTambahKelas">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: X IPA 1" style="font-size: 0.95rem;">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="grade" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Tingkat Kelas <span class="text-danger">*</span></label>
                            <input type="number" min="1" class="form-control form-control-lg bg-light border-0 @error('grade') is-invalid @enderror" id="grade" name="grade" value="{{ old('grade') }}" required placeholder="Contoh: 10" style="font-size: 0.95rem;">
                            @error('grade') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="school_year_id" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Tahun Ajaran <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg bg-light border-0 @error('school_year_id') is-invalid @enderror" id="school_year_id" name="school_year_id" required style="font-size: 0.95rem;">
                                <option value="">-- Pilih --</option>
                                @foreach ($schoolYears as $sy)
                                    <option value="{{ $sy->id }}" {{ old('school_year_id') == $sy->id ? 'selected' : '' }}>
                                        {{ $sy->academic_year }} ({{ ucfirst($sy->semester) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('school_year_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="major_id" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Jurusan</label>
                            <select class="form-select form-select-lg bg-light border-0 @error('major_id') is-invalid @enderror" id="major_id" name="major_id" style="font-size: 0.95rem;">
                                <option value="">-- Pilih --</option>
                                @foreach ($majors as $major)
                                    <option value="{{ $major->id }}" {{ old('major_id') == $major->id ? 'selected' : '' }}>
                                        {{ $major->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('major_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="max_students" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Kapasitas</label>
                            <input type="number" min="1" class="form-control form-control-lg bg-light border-0 @error('max_students') is-invalid @enderror" id="max_students" name="max_students" value="{{ old('max_students') }}" placeholder="Contoh: 36" style="font-size: 0.95rem;">
                            @error('max_students') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="room_number" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">No. Ruang</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0 @error('room_number') is-invalid @enderror" id="room_number" name="room_number" value="{{ old('room_number') }}" placeholder="Contoh: R-101" style="font-size: 0.95rem;">
                            @error('room_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-medium text-secondary border border-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-medium shadow-sm transition-hover">Tambah Kelas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('tambahKelasModal'), {
            keyboard: false
        });
        myModal.show();
    });
</script>
@endif

<style>
    .transition-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .transition-hover:hover {
        transform: translateY(-2px);
    }
    .transition-hover-text:hover {
        color: #0f172a !important;
    }
    
    /* Customizing inputs */
    .input-group-text, .form-control, .form-select {
        border-color: #e2e8f0;
    }
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    }
    .input-group {
        max-width: 100%;
    }
</style>
@endsection
