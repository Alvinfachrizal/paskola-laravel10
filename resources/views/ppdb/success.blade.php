@extends('ppdb.layout')
@section('title', 'Pendaftaran Berhasil')

@section('content')
<div class="container py-5" style="max-width: 600px;">
    <div class="ppdb-card card p-5 text-center">
        <div class="mb-4">
            <div style="width:80px;height:80px;background:linear-gradient(135deg,#0e9f6e,#1a56db);
                        border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                <i class="bi bi-check2-circle text-white" style="font-size:2.5rem;"></i>
            </div>
        </div>

        <h2 class="fw-bold mb-1">Pendaftaran Berhasil! 🎉</h2>
        <p class="text-muted mb-4">Halo, <strong>{{ $name }}</strong>! Data Anda telah kami terima dan sedang dalam proses verifikasi.</p>

        {{-- Kode Pendaftaran --}}
        <div class="p-4 mb-4 rounded-3" style="background:#f0f9ff;border:2px dashed #1a56db">
            <p class="text-muted small mb-1 fw-semibold text-uppercase" style="letter-spacing:1px">Kode Pendaftaran Anda</p>
            <p class="fw-bold mb-0" style="font-size:2rem;letter-spacing:3px;color:#1a56db">{{ $code }}</p>
        </div>

        <div class="alert alert-warning rounded-3 text-start mb-4">
            <p class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Simpan Kode Ini!</p>
            <p class="small mb-0">
                Kode <strong>{{ $code }}</strong> dan <strong>tanggal lahir Anda</strong> digunakan untuk login ulang
                dan memantau status pendaftaran. Jangan berikan kepada orang lain.
            </p>
        </div>

        <div class="d-flex flex-column gap-2">
            <a href="{{ route('ppdb.cek-status.form') }}" class="btn btn-ppdb-primary">
                <i class="bi bi-search me-2"></i>Cek Status Pendaftaran
            </a>
            <a href="{{ route('ppdb.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-house me-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
