@extends('layouts.app-bootstrap')
@section('title', 'Data Siswa')

@section('header')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h3 mb-1 text-dark fw-bold" style="letter-spacing:-0.5px;">Data Siswa</h2>
            <p class="text-muted mb-0" style="font-size:0.875rem;">Kelola data siswa dan informasi akademik</p>
        </div>
        <div class="d-grid d-sm-block">
            @hasanyrole('Super Admin|Admin')
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-2 shadow-sm transition-hover fw-medium d-flex align-items-center justify-content-center gap-1" style="font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#tambahSiswaModal">
                <i class="bi bi-plus-lg"></i> Tambah Siswa
            </button>
            @endhasanyrole
        </div>
    </div>
@endsection

@section('content')
<!-- Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #eff6ff; color: #3b82f6;">
                <i class="bi bi-mortarboard"></i>
            </div>
            <div>
                <h3 class="mb-0 fw-bold">{{ count($students) }}</h3>
                <small class="text-muted">Total Siswa</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #dcfce7; color: #22c55e;">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <h3 class="mb-0 fw-bold">{{ collect($students)->where('status', 'active')->count() }}</h3>
                <small class="text-muted">Aktif</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fee2e2; color: #ef4444;">
                <i class="bi bi-x-circle"></i>
            </div>
            <div>
                <h3 class="mb-0 fw-bold">{{ collect($students)->whereIn('status', ['inactive', 'dropped_out'])->count() }}</h3>
                <small class="text-muted">Keluar / Pindah</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3f4f6; color: #6b7280;">
                <i class="bi bi-person-check"></i>
            </div>
            <div>
                <h3 class="mb-0 fw-bold">{{ collect($students)->where('status', 'graduated')->count() }}</h3>
                <small class="text-muted">Lulus</small>
            </div>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 bg-success-subtle" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Main Table Card -->
<div class="card border-0">
    <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Daftar Siswa ({{ count($students) }})</h6>
        <button class="btn btn-sm btn-light text-muted border-0"><i class="bi bi-arrow-clockwise"></i></button>
    </div>
    <div class="card-body px-4 pb-4">
        
        <!-- Filters -->
        <div class="d-flex flex-wrap gap-3 mb-4">
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 8px 0 0 8px;"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Cari nama, NISN, NIS..." style="border-radius: 0 8px 8px 0; background: white;">
                </div>
            </div>
            <select class="form-select" style="width: 200px; border-radius: 8px;">
                <option>Semua Status</option>
                <option>Aktif</option>
                <option>Lulus</option>
            </select>
        </div>

        <div class="table-responsive border-0">
            <table class="table table-hover align-middle mb-0" style="min-width: 900px;">
                <thead style="background-color: #f8eade;"> <!-- Matching the slight beige color in screenshot -->
                    <tr>
                        <th style="background-color: #fdfaf6; border-radius: 8px 0 0 8px;" class="text-muted">NO</th>
                        <th style="background-color: #fdfaf6;" class="text-muted">NAMA SISWA</th>
                        <th style="background-color: #fdfaf6;" class="text-muted">NISN / NIS</th>
                        <th style="background-color: #fdfaf6;" class="text-muted">L/P</th>
                        <th style="background-color: #fdfaf6;" class="text-muted">STATUS</th>
                        <th style="background-color: #fdfaf6;" class="text-muted">ORANG TUA</th>
                        <th style="background-color: #fdfaf6;" class="text-muted">HP ORTU</th>
                        @hasanyrole('Super Admin|Admin')
                        <th style="background-color: #fdfaf6; border-radius: 0 8px 8px 0;" class="text-end text-muted">AKSI</th>
                        @endhasanyrole
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse ($students as $index => $student)
                        <tr>
                            <td class="text-primary">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 35px; height: 35px; font-weight:600;">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $student->name }}</div>
                                        <div class="text-muted" style="font-size:0.75rem;">{{ $student->user->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-dark">{{ $student->nisn ?: '-' }}</div>
                                <div class="text-muted small">{{ $student->nis ?: '-' }}</div>
                            </td>
                            <td>{{ $student->gender }}</td>
                            <td>
                                @if($student->status === 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-medium">Aktif</span>
                                @elseif($student->status === 'inactive')
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1 fw-medium">Nonaktif</span>
                                @elseif($student->status === 'graduated')
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-1 fw-medium">Lulus</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 fw-medium">Dikeluarkan</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $student->parent_name ?: '-' }}</td>
                            <td class="text-muted">{{ $student->parent_phone ?: '-' }}</td>
                            @hasanyrole('Super Admin|Admin')
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-sm bg-primary bg-opacity-10 text-primary border-0 rounded-pill px-3 fw-medium transition-hover d-flex align-items-center gap-1">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Yakin hapus data siswa ini? Semua data terkait juga akan terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm bg-danger bg-opacity-10 text-danger border-0 rounded-pill px-3 fw-medium transition-hover d-flex align-items-center gap-1">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endhasanyrole
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                                Belum ada data siswa
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Siswa -->
<div class="modal fade" id="tambahSiswaModal" tabindex="-1" aria-labelledby="tambahSiswaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-bottom-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="tambahSiswaModalLabel" style="font-size: 1.1rem;">Tambah Siswa Baru</h5>
                <button type="button" class="btn-close bg-light rounded-circle" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.students.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-4">
                        <!-- Kolom Kiri: Informasi Pribadi & Akademik -->
                        <div class="col-lg-6">
                            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-person-badge me-2"></i>Informasi Pribadi & Akademik</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bg-light border-0 @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required placeholder="Masukkan nama lengkap siswa">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">NISN</label>
                                    <input type="text" class="form-control bg-light border-0 @error('nisn') is-invalid @enderror" name="nisn" value="{{ old('nisn') }}" placeholder="Opsional">
                                    @error('nisn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">NIS</label>
                                    <input type="text" class="form-control bg-light border-0 @error('nis') is-invalid @enderror" name="nis" value="{{ old('nis') }}" placeholder="Opsional">
                                    @error('nis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select class="form-select bg-light border-0 @error('gender') is-invalid @enderror" name="gender" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Tahun Masuk</label>
                                    <input type="number" class="form-control bg-light border-0 @error('entry_year') is-invalid @enderror" name="entry_year" value="{{ old('entry_year', date('Y')) }}" placeholder="Contoh: {{ date('Y') }}">
                                    @error('entry_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Akun & Kontak -->
                        <div class="col-lg-6">
                            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-shield-lock me-2"></i>Informasi Akun & Kontak</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Email Siswa <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control bg-light border-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required placeholder="Email untuk login akun">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control bg-light border-0 @error('password') is-invalid @enderror" name="password" required placeholder="Minimal 8 karakter">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">No. HP Siswa</label>
                                    <input type="text" class="form-control bg-light border-0 @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" placeholder="Opsional">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Status Akademik <span class="text-danger">*</span></label>
                                    <select class="form-select bg-light border-0 @error('status') is-invalid @enderror" name="status" required>
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                        <option value="graduated" {{ old('status') == 'graduated' ? 'selected' : '' }}>Lulus</option>
                                        <option value="dropped_out" {{ old('status') == 'dropped_out' ? 'selected' : '' }}>Dikeluarkan / Pindah</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <hr class="my-4 text-muted border-1 border-dashed">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Nama Orang Tua / Wali</label>
                                    <input type="text" class="form-control bg-light border-0 @error('parent_name') is-invalid @enderror" name="parent_name" value="{{ old('parent_name') }}" placeholder="Opsional">
                                    @error('parent_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">No. HP Orang Tua</label>
                                    <input type="text" class="form-control bg-light border-0 @error('parent_phone') is-invalid @enderror" name="parent_phone" value="{{ old('parent_phone') }}" placeholder="Opsional">
                                    @error('parent_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-medium text-secondary border border-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-medium shadow-sm transition-hover d-flex align-items-center gap-2"><i class="bi bi-save"></i> Simpan Siswa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($errors->any() || request('action') == 'create')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('tambahSiswaModal'), {
            keyboard: false
        });
        myModal.show();
        
        // Bersihkan parameter ?action=create dari URL setelah modal terbuka agar saat di-refresh tidak terbuka lagi
        if(window.history.replaceState) {
            var url = new URL(window.location.href);
            url.searchParams.delete('action');
            window.history.replaceState({path:url.href}, '', url.href);
        }
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

