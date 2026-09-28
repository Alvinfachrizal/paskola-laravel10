@extends('layouts.app-bootstrap')
@section('title', 'Masukkan Kode Ujian')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="col-12 col-sm-9 col-md-6 col-lg-4">
        <div class="text-center mb-4">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                style="width:72px;height:72px;background:linear-gradient(135deg,#667eea,#764ba2);">
                <i class="bi bi-key-fill text-white fs-2"></i>
            </div>
            <h3 class="fw-bold">Masukkan Kode Ujian</h3>
            <p class="text-muted small">Ketik kode 8 karakter yang diberikan guru Anda</p>
        </div>

        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger rounded-3 py-2 small mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
                </div>
                @endif

                <form method="POST" action="{{ route('exam.session.enter.submit') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Kode Ujian</label>
                        <input type="text" name="code" id="codeInput"
                            class="form-control form-control-lg rounded-3 text-center fw-bold text-uppercase @error('code') is-invalid @enderror"
                            placeholder="XXXXXXXX"
                            maxlength="10"
                            autocomplete="off"
                            autofocus
                            style="letter-spacing: 0.4rem; font-size: 1.5rem;"
                            value="{{ old('code') }}"
                            oninput="this.value = this.value.toUpperCase()">
                        @error('code')
                        <div class="invalid-feedback text-center">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold py-3 fs-5">
                        <i class="bi bi-arrow-right-circle me-2"></i>Mulai Ujian
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('exam.session.my-exams') }}" class="text-muted small">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke daftar ujian
            </a>
        </div>

        <div class="card border-0 bg-light rounded-4 mt-3">
            <div class="card-body py-3 px-4">
                <p class="small text-muted mb-2 fw-semibold"><i class="bi bi-info-circle me-1"></i>Perhatian:</p>
                <ul class="small text-muted mb-0 ps-3">
                    <li>Kode ujian bersifat pribadi dan hanya berlaku untuk akun Anda</li>
                    <li>Pastikan Anda memiliki koneksi internet stabil sebelum memulai</li>
                    <li>Waktu dihitung dari saat Anda masuk, bukan dari waktu ujian dibuka</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
