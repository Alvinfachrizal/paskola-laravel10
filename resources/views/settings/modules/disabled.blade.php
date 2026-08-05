@extends('layouts.app-bootstrap')

@section('title', 'Modul Tidak Aktif')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
    <div class="text-center px-4">
        <div class="mb-4">
            <i class="bi bi-plug text-secondary" style="font-size: 5rem; opacity: 0.3;"></i>
        </div>
        <h2 class="fw-bold mb-2">Modul Tidak Aktif</h2>
        <p class="text-muted mb-4" style="max-width: 400px; margin: 0 auto;">
            Fitur yang Anda coba akses saat ini sedang dinonaktifkan oleh administrator sekolah. 
            Silakan hubungi Admin untuk informasi lebih lanjut.
        </p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-house-fill me-2"></i>Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
