@extends('layouts.app-bootstrap')
@section('title', 'Profil Pengguna')

@section('header')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h3 mb-1 text-dark fw-bold" style="letter-spacing:-0.5px;">Profil Pengguna</h2>
            <p class="text-muted mb-0" style="font-size:0.875rem;">Kelola informasi akun dan keamanan Anda</p>
        </div>
    </div>
@endsection

@section('content')

@if (session('status') === 'profile-updated')
    <div class="alert alert-success alert-dismissible fade show border-0 bg-success-subtle rounded-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Profil berhasil diperbarui.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('status') === 'password-updated')
    <div class="alert alert-success alert-dismissible fade show border-0 bg-success-subtle rounded-4" role="alert">
        <i class="bi bi-shield-check me-2"></i> Password berhasil diperbarui.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Left Column: Profile Info & Avatar -->
    <div class="col-xl-4 col-lg-5">
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4 text-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: 600;">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
                <h5 class="fw-bold text-dark mb-1">{{ Auth::user()->name }}</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 mb-3">
                    {{ Auth::user()->role }}
                </span>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                    <i class="bi bi-envelope me-1"></i> {{ Auth::user()->email }}
                </p>
            </div>
            <div class="card-footer bg-transparent border-top p-3 text-center">
                <small class="text-muted">Bergabung sejak {{ Auth::user()->created_at->format('M Y') }}</small>
            </div>
        </div>
    </div>

    <!-- Right Column: Settings Forms -->
    <div class="col-xl-8 col-lg-7">
        
        <!-- Update Profile Form -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold text-dark mb-1">Informasi Dasar</h5>
                <p class="text-muted" style="font-size: 0.85rem;">Perbarui nama akun dan alamat email Anda.</p>
            </div>
            <div class="card-body p-4">
                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="mb-3">
                        <label for="name" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Nama Lengkap</label>
                        <input type="text" class="form-control bg-light border-0 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Alamat Email</label>
                        <input type="email" class="form-control bg-light border-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if (Auth::user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! Auth::user()->hasVerifiedEmail())
                            <div class="mt-2 text-warning" style="font-size: 0.85rem;">
                                Email Anda belum diverifikasi. 
                                <button form="send-verification" class="btn btn-link p-0 m-0 align-baseline text-decoration-none">Klik di sini untuk mengirim ulang email verifikasi.</button>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-medium shadow-sm transition-hover">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Update Password Form -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold text-dark mb-1">Ubah Password</h5>
                <p class="text-muted" style="font-size: 0.85rem;">Pastikan akun Anda menggunakan password yang panjang dan acak demi keamanan.</p>
            </div>
            <div class="card-body p-4">
                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="mb-3">
                        <label for="current_password" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Password Saat Ini</label>
                        <input type="password" class="form-control bg-light border-0 @error('current_password', 'updatePassword') is-invalid @enderror" id="current_password" name="current_password" autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Password Baru</label>
                        <input type="password" class="form-control bg-light border-0 @error('password', 'updatePassword') is-invalid @enderror" id="password" name="password" autocomplete="new-password">
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control bg-light border-0 @error('password_confirmation', 'updatePassword') is-invalid @enderror" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                        @error('password_confirmation', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-dark rounded-pill px-4 py-2 fw-medium shadow-sm transition-hover">Perbarui Password</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Account Form -->
        <div class="card border-0 rounded-4 shadow-sm border-danger border-opacity-25">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold text-danger mb-1">Hapus Akun</h5>
                <p class="text-muted" style="font-size: 0.85rem;">Setelah dihapus, semua data dan sumber daya akun ini akan dihapus secara permanen.</p>
            </div>
            <div class="card-body p-4">
                <button class="btn btn-outline-danger rounded-pill px-4 py-2 fw-medium transition-hover" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
                    Hapus Akun Permanen
                </button>
            </div>
        </div>
        
    </div>
</div>

<!-- Modal Hapus Akun -->
<div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                
                <div class="modal-header border-bottom-0 px-4 pt-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="confirmUserDeletionModalLabel">Apakah Anda yakin?</h5>
                    <button type="button" class="btn-close bg-light rounded-circle" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted" style="font-size: 0.9rem;">
                        Tindakan ini tidak dapat dibatalkan. Silakan masukkan password Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun ini selamanya.
                    </p>
                    
                    <div class="mt-3">
                        <label for="password_delete" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Password Anda</label>
                        <input type="password" class="form-control bg-light border-0 @error('password', 'userDeletion') is-invalid @enderror" id="password_delete" name="password" placeholder="Password">
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">Hapus Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .transition-hover {
        transition: all 0.3s ease;
    }
    .transition-hover:hover {
        transform: translateY(-2px);
    }
    .form-control:focus {
        box-shadow: none;
        border: 1px solid var(--primary-color) !important;
    }
</style>

@endsection

