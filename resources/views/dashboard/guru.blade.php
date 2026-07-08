@extends('layouts.app-bootstrap')

@section('header')
    <h2 class="h4 mb-0 text-dark">
        {{ __('Dashboard Guru') }}
    </h2>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success">
            Selamat datang, <strong>{{ $user->name }}</strong>! Semoga harimu menyenangkan.
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Stat 1 -->
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-info h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Jadwal Hari Ini</h5>
                <p class="card-text display-6">0</p>
                <small>Mata Pelajaran</small>
            </div>
        </div>
    </div>
    <!-- Stat 2 -->
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-warning h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Tugas Menunggu Penilaian</h5>
                <p class="card-text display-6">0</p>
                <small>Pengumpulan (Submissions)</small>
            </div>
        </div>
    </div>
    <!-- Stat 3 -->
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Materi Terpublikasi</h5>
                <p class="card-text display-6">0</p>
                <small>Dokumen & Video</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Kelas yang Anda Ajar</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Data kelas belum tersedia.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Pengumuman Sekolah</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Tidak ada pengumuman terbaru.</p>
            </div>
        </div>
    </div>
</div>
@endsection
