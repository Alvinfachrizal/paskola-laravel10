@extends('layouts.app-bootstrap')
@section('title', 'Tahun Ajaran')

@section('header')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h3 mb-1 text-dark fw-bold" style="letter-spacing:-0.5px;">Tahun Ajaran</h2>
            <p class="text-muted mb-0" style="font-size:0.875rem;">Kelola data tahun ajaran dan periode akademik aktif</p>
        </div>
        <div class="d-grid d-sm-block">
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-2 shadow-sm transition-hover fw-medium d-flex align-items-center justify-content-center gap-1" style="font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#tambahTahunAjaranModal">
                <i class="bi bi-plus-lg"></i> Tambah Periode
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 bg-success-subtle" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Daftar Tahun Ajaran</h6>
            </div>
            
            <div class="card-body px-4 pb-4 pt-2">
                <div class="table-responsive border-0">
                    <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                        <thead style="background-color: #f8eade;">
                            <tr>
                                <th style="background-color: #fdfaf6; border-radius: 8px 0 0 8px;" class="ps-4 py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">TAHUN AJARAN</th>
                                <th style="background-color: #fdfaf6;" class="py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">SEMESTER</th>
                                <th style="background-color: #fdfaf6;" class="py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">PERIODE</th>
                                <th style="background-color: #fdfaf6;" class="py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">STATUS</th>
                                <th style="background-color: #fdfaf6; border-radius: 0 8px 8px 0;" class="text-end pe-4 py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse ($schoolYears as $sy)
                                <tr class="border-bottom">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 35px; height: 35px; font-weight:600;">
                                                <i class="bi bi-calendar3"></i>
                                            </div>
                                            <span class="fw-bold text-dark" style="font-size:0.95rem;">{{ $sy->academic_year }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $sy->semester == 'ganjil' ? 'bg-info bg-opacity-10 text-info' : 'bg-warning bg-opacity-10 text-warning' }} rounded-pill px-3 py-1 fw-medium text-capitalize">
                                            {{ $sy->semester }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-medium" style="font-size:0.875rem;">{{ $sy->start_date->format('d M Y') }} - {{ $sy->end_date->format('d M Y') }}</div>
                                    </td>
                                    <td>
                                        @if($sy->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-medium"><i class="bi bi-check-circle me-1"></i> Aktif</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-1 fw-medium">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.school-years.edit', $sy->id) }}" class="btn btn-sm bg-primary bg-opacity-10 text-primary border-0 rounded-pill px-3 fw-medium transition-hover d-flex align-items-center gap-1">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.school-years.destroy', $sy->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Yakin ingin menghapus tahun ajaran ini?');">
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
                                    <td colspan="5" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <div class="bg-light rounded-4 d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                                <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">Belum ada data tahun ajaran</h6>
                                            <p class="text-muted mb-0" style="font-size:0.875rem;">Silakan tambah periode tahun ajaran baru</p>
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

<!-- Modal Tambah Tahun Ajaran -->
<div class="modal fade" id="tambahTahunAjaranModal" tabindex="-1" aria-labelledby="tambahTahunAjaranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-bottom-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="tambahTahunAjaranModalLabel" style="font-size: 1.1rem;">Tambah Tahun Ajaran</h5>
                <button type="button" class="btn-close bg-light rounded-circle" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.school-years.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Tahun Ajaran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light border-0 @error('academic_year') is-invalid @enderror" name="academic_year" value="{{ old('academic_year') }}" required placeholder="Contoh: 2023/2024">
                            @error('academic_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Semester <span class="text-danger">*</span></label>
                            <select class="form-select bg-light border-0 @error('semester') is-invalid @enderror" name="semester" required>
                                <option value="">-- Pilih Semester --</option>
                                <option value="ganjil" {{ old('semester') == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="genap" {{ old('semester') == 'genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                            @error('semester') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control bg-light border-0 @error('start_date') is-invalid @enderror" name="start_date" value="{{ old('start_date') }}" required>
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control bg-light border-0 @error('end_date') is-invalid @enderror" name="end_date" value="{{ old('end_date') }}" required>
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }} style="width: 2.5rem; height: 1.25rem;">
                            <label class="form-check-label text-dark fw-medium mt-1" for="is_active">Jadikan Tahun Ajaran Aktif</label>
                        </div>
                        <small class="text-muted d-block mt-1">Mengaktifkan periode ini akan menonaktifkan periode lainnya secara otomatis.</small>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-medium text-secondary border border-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-medium shadow-sm transition-hover d-flex align-items-center gap-2"><i class="bi bi-save"></i> Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('tambahTahunAjaranModal'), {
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
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1) !important;
    }
</style>
@endsection

