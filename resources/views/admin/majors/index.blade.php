@extends('layouts.app-bootstrap')

@section('header')
    <h2 class="h3 mb-1 text-dark fw-bold" style="letter-spacing:-0.5px;">Kelas & Mata Pelajaran</h2>
    <p class="text-muted mb-0" style="font-size:0.875rem;">Kelola kelas, mata pelajaran, dan jurusan sekolah</p>
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
                        <a class="nav-link px-0 pb-3 border-0 text-muted fw-medium bg-transparent transition-hover-text" href="{{ route('admin.classes.index') }}">
                            <i class="bi bi-building me-2"></i>Kelas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-0 pb-3 border-0 text-muted fw-medium bg-transparent transition-hover-text" href="{{ route('admin.subjects.index') }}">
                            <i class="bi bi-book me-2"></i>Mata Pelajaran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active px-0 pb-3 border-0 border-bottom border-primary border-2 text-primary fw-bold bg-transparent" href="{{ route('admin.majors.index') }}" style="color: #0d6efd !important;">
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
                    <div class="col-12 d-grid d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-2 shadow-sm transition-hover fw-medium d-flex align-items-center justify-content-center gap-1" style="font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#tambahJurusanModal">
                            <i class="bi bi-plus-lg"></i> Tambah Jurusan
                        </button>
                    </div>
                </div>

                <!-- Grid Content (No Table) -->
                <div class="row g-4">
                    @forelse ($majors as $major)
                        <div class="col-md-6 col-xl-4">
                            <div class="card bg-light border border-light shadow-sm rounded-4 h-100 transition-hover">
                                <div class="card-body p-4 d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-2 fs-5">{{ $major->name }}</h6>
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-semibold" style="font-size:0.75rem;">{{ $major->type ?: 'Umum' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-3 mt-sm-0">
                                        <a href="{{ route('admin.majors.edit', $major->id) }}" class="btn btn-sm bg-primary bg-opacity-10 text-primary border-0 rounded-pill px-3 fw-medium transition-hover d-flex align-items-center gap-1">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.majors.destroy', $major->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jurusan ini?');" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm bg-danger bg-opacity-10 text-danger border-0 rounded-pill px-3 fw-medium transition-hover d-flex align-items-center gap-1">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5 border rounded-4 border-light">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <div class="bg-light rounded-4 d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                        <i class="bi bi-layers text-muted" style="font-size: 2rem;"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Belum ada data jurusan</h6>
                                    <p class="text-muted mb-0" style="font-size:0.875rem;">Tambahkan jurusan terlebih dahulu</p>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Jurusan -->
<div class="modal fade" id="tambahJurusanModal" tabindex="-1" aria-labelledby="tambahJurusanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-bottom-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="tambahJurusanModalLabel" style="font-size: 1.1rem;">Tambah Jurusan</h5>
                <button type="button" class="btn-close bg-light rounded-circle" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.majors.store') }}" method="POST" id="formTambahJurusan">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Nama Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Rekayasa Perangkat Lunak" style="font-size: 0.95rem;">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="type" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Tipe (Opsional)</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0 @error('type') is-invalid @enderror" id="type" name="type" value="{{ old('type') }}" placeholder="Contoh: Kejuruan / IPA / IPS" style="font-size: 0.95rem;">
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Deskripsi (Opsional)</label>
                        <textarea class="form-control form-control-lg bg-light border-0 @error('description') is-invalid @enderror" id="description" name="description" rows="2" placeholder="Deskripsi singkat tentang jurusan ini" style="font-size: 0.95rem; resize:none;">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-medium text-secondary border border-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-medium shadow-sm transition-hover">Tambah Jurusan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('tambahJurusanModal'), {
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
</style>
@endsection
