@extends('layouts.app-bootstrap')

@section('header')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h3 mb-1 text-dark fw-bold" style="letter-spacing:-0.5px;">Manajemen Pengguna</h2>
            <p class="text-muted mb-0" style="font-size:0.875rem;">Kelola akun pengguna sistem beserta role dan akses</p>
        </div>
        <div class="d-grid d-sm-block">
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-2 shadow-sm transition-hover fw-medium d-flex align-items-center justify-content-center gap-1" style="font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#tambahPenggunaModal">
                <i class="bi bi-plus-lg"></i> Tambah Pengguna
            </button>
        </div>
    </div>
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
        
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Stats Cards Row -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2 flex-grow-1">
                <div class="card bg-white border border-light shadow-sm rounded-4 h-100 transition-hover">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">{{ $stats['Admin'] }}</h4>
                            <span class="text-muted" style="font-size:0.75rem;">Admin</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2 flex-grow-1">
                <div class="card bg-white border border-light shadow-sm rounded-4 h-100 transition-hover">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-person-badge fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">{{ $stats['Kepala'] }}</h4>
                            <span class="text-muted" style="font-size:0.75rem;">Kepala</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2 flex-grow-1">
                <div class="card bg-white border border-light shadow-sm rounded-4 h-100 transition-hover">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-person-video3 fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">{{ $stats['Guru'] }}</h4>
                            <span class="text-muted" style="font-size:0.75rem;">Guru</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2 flex-grow-1">
                <div class="card bg-white border border-light shadow-sm rounded-4 h-100 transition-hover">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-mortarboard fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">{{ $stats['Siswa'] }}</h4>
                            <span class="text-muted" style="font-size:0.75rem;">Siswa</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2 flex-grow-1">
                <div class="card bg-white border border-light shadow-sm rounded-4 h-100 transition-hover">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="bg-secondary bg-opacity-10 text-secondary rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">{{ $stats['OrangTua'] }}</h4>
                            <span class="text-muted" style="font-size:0.75rem;">Orang Tua</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom pt-3 pb-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">Daftar Pengguna ({{ $users->total() }})</h6>
                <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;" onclick="window.location.reload();" title="Refresh Data">
                    <i class="bi bi-arrow-repeat text-muted"></i>
                </button>
            </div>
            
            <div class="card-body px-4 pb-4 pt-4">
                <!-- Filter Row -->
                <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control bg-white border-start-0 py-2 rounded-end-pill" placeholder="Cari nama atau email..." value="{{ request('search') }}" style="font-size:0.875rem;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="role" class="form-select py-2 rounded-pill bg-white" style="font-size:0.875rem;" onchange="this.form.submit()">
                            <option value="">Semua Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <!-- Table Content -->
                <div class="table-responsive rounded-3 border">
                    <table class="table table-hover table-borderless align-middle mb-0" style="min-width:900px;">
                        <thead style="background-color: #f8f6f2; border-bottom: 1px solid #e2e8f0;">
                            <tr>
                                <th class="ps-4 py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px; width:60px;">NO</th>
                                <th class="py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">PENGGUNA</th>
                                <th class="py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">EMAIL</th>
                                <th class="py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">ROLE</th>
                                <th class="py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">STATUS</th>
                                <th class="text-end pe-4 py-3 text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $index => $user)
                                <tr class="border-bottom">
                                    <td class="ps-4 py-3 text-muted" style="font-size:0.875rem;">{{ $users->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @php
                                                // Generate color based on role
                                                $roleName = $user->roles->first()?->name ?? 'User';
                                                $colorMap = [
                                                    'Super Admin' => 'bg-primary',
                                                    'Admin' => 'bg-info',
                                                    'Kepala Sekolah' => 'bg-primary',
                                                    'Guru' => 'bg-success',
                                                    'Siswa' => 'bg-warning',
                                                    'Ortu' => 'bg-secondary'
                                                ];
                                                $bgColor = $colorMap[$roleName] ?? 'bg-dark';
                                                $initial = strtoupper(substr($user->name, 0, 1));
                                            @endphp
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold {{ $bgColor }}" style="width: 36px; height: 36px; font-size:0.9rem;">
                                                {{ $initial }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size:0.9rem;">{{ $user->name }}</div>
                                                <div class="text-muted" style="font-size:0.75rem;">{{ $user->nik ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted" style="font-size:0.875rem;">{{ $user->email }}</span>
                                    </td>
                                    <td>
                                        @php
                                            // Icon mapping for roles
                                            $iconMap = [
                                                'Super Admin' => 'bi-shield-check',
                                                'Admin' => 'bi-shield-lock',
                                                'Kepala Sekolah' => 'bi-person-badge',
                                                'Guru' => 'bi-person-video3',
                                                'Siswa' => 'bi-mortarboard',
                                                'Ortu' => 'bi-people'
                                            ];
                                            $bgOpacityColor = str_replace('bg-', 'text-', $bgColor) . ' ' . $bgColor . ' bg-opacity-10';
                                        @endphp
                                        <span class="badge {{ $bgOpacityColor }} rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size:0.75rem;">
                                            <i class="bi {{ $iconMap[$roleName] ?? 'bi-person' }}"></i> {{ $roleName }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="text-success fw-medium d-flex align-items-center gap-1" style="font-size:0.8rem;">
                                                <i class="bi bi-check-circle-fill"></i> Aktif
                                            </span>
                                        @else
                                            <span class="text-danger fw-medium d-flex align-items-center gap-1" style="font-size:0.8rem;">
                                                <i class="bi bi-x-circle-fill"></i> Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            @if($user->id !== auth()->id())
                                                <!-- Impersonate Button -->
                                                <form action="{{ route('admin.users.impersonate', $user->id) }}" method="POST" title="Login Sebagai {{ $user->name }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm bg-dark bg-opacity-10 text-dark border-0 rounded-pill px-3 fw-medium transition-hover d-flex align-items-center gap-1">
                                                        <i class="bi bi-box-arrow-in-right"></i> Login As
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <button type="button" class="btn btn-sm bg-primary bg-opacity-10 text-primary border-0 rounded-pill px-3 fw-medium transition-hover d-flex align-items-center gap-1" title="Edit Pengguna">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm bg-danger bg-opacity-10 text-danger border-0 rounded-pill px-3 fw-medium transition-hover d-flex align-items-center gap-1" title="Hapus Pengguna">
                                                        <i class="bi bi-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <div class="bg-light rounded-4 d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                                <i class="bi bi-people text-muted" style="font-size: 2rem;"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">Belum ada pengguna</h6>
                                            <p class="text-muted mb-0" style="font-size:0.875rem;">Coba ubah filter pencarian Anda</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($users->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted" style="font-size: 0.85rem;">
                        Menampilkan {{ $users->firstItem() }} sampai {{ $users->lastItem() }} dari {{ $users->total() }} pengguna
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
                @endif
                
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal fade" id="tambahPenggunaModal" tabindex="-1" aria-labelledby="tambahPenggunaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-bottom-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="tambahPenggunaModalLabel" style="font-size: 1.1rem;">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close bg-light rounded-circle" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.users.store') }}" method="POST" id="formTambahPengguna">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Nama pengguna" style="font-size: 0.95rem;">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-lg bg-light border-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="email@sekolah.id" style="font-size: 0.95rem;">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control form-control-lg bg-light border-0 @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Min. 8 karakter" style="font-size: 0.95rem;">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Role <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg bg-light border-0 @error('role') is-invalid @enderror" id="role" name="role" required style="font-size: 0.95rem;">
                                <option value="">-- Pilih --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="phone" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">No. HP</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0 @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" style="font-size: 0.95rem;">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nik" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">NIK</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0 @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik') }}" placeholder="16 digit NIK" style="font-size: 0.95rem;">
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-medium text-secondary border border-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-medium shadow-sm transition-hover">Buat Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('tambahPenggunaModal'), {
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
