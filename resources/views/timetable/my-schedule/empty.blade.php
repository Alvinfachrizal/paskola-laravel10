@extends('layouts.app-bootstrap')

@section('title', 'Jadwal Pelajaran')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6 text-center">
        <i class="bi bi-calendar-x display-1 text-secondary opacity-25 mb-4 d-block"></i>
        <h4 class="fw-bold text-dark mb-3">Informasi Jadwal Tidak Tersedia</h4>
        <p class="text-muted mb-4">{{ $message ?? 'Tidak ada data jadwal untuk ditampilkan saat ini.' }}</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-house me-2"></i>Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
