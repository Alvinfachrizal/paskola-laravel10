@extends('ppdb.layout')
@section('title', 'Cek Status Pendaftaran')

@section('content')
<div class="container py-5" style="max-width:500px;">

    <div class="text-center mb-4">
        <div style="width:72px;height:72px;background:linear-gradient(135deg,#1a56db,#0e9f6e);
                    border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
            <i class="bi bi-search text-white" style="font-size:2rem;"></i>
        </div>
        <h1 class="fw-bold" style="font-size:1.8rem">Cek Status Pendaftaran</h1>
        <p class="text-muted">Masukkan kode pendaftaran dan tanggal lahir yang Anda daftarkan.</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger rounded-3 mb-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ $errors->first() }}
    </div>
    @endif

    <div class="ppdb-card card p-4">
        <form action="{{ route('ppdb.cek-status.submit') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="registration_code">
                    Kode Pendaftaran <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control @error('registration_code') is-invalid @enderror"
                       id="registration_code" name="registration_code"
                       value="{{ old('registration_code') }}"
                       placeholder="Contoh: PPDB-2025-00001"
                       style="font-size:1.1rem;font-weight:600;letter-spacing:1px;text-transform:uppercase"
                       required>
                @error('registration_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label" for="birth_date">
                    Tanggal Lahir Calon Siswa <span class="text-danger">*</span>
                </label>
                <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                       id="birth_date" name="birth_date"
                       value="{{ old('birth_date') }}" required>
                @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">
                    <i class="bi bi-shield-lock me-1"></i>
                    Digunakan sebagai verifikasi identitas — bukan untuk OTP atau kode apapun.
                </div>
            </div>

            <button type="submit" class="btn btn-ppdb-primary w-100">
                <i class="bi bi-search me-2"></i>Lihat Status Pendaftaran
            </button>
        </form>
    </div>

    <p class="text-center text-muted small mt-4">
        Belum punya kode?
        <a href="{{ route('ppdb.register.form') }}" class="text-primary fw-semibold">Daftar sekarang →</a>
    </p>
</div>
@endsection
