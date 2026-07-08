@extends('layouts.app-bootstrap')

@section('header')
    <h2 class="h4 mb-0 text-dark">
        {{ __('Dashboard Siswa') }}
    </h2>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            Halo, <strong>{{ $user->name }}</strong>! Tetap semangat belajarnya ya!
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Stat 1 -->
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Tugas Baru</h5>
                <p class="card-text display-6">0</p>
                <small>Belum dikerjakan</small>
            </div>
        </div>
    </div>
    <!-- Stat 2 -->
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Kehadiran (Bulan Ini)</h5>
                <p class="card-text display-6">100%</p>
                <small>0 Sakit, 0 Izin, 0 Alfa</small>
            </div>
        </div>
    </div>
    <!-- Stat 3 -->
    <div class="col-md-4 mb-3">
        <div class="card text-dark bg-light h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Rata-Rata Nilai</h5>
                <p class="card-text display-6">0</p>
                <small>Semester Ini</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Jadwal Pelajaran Hari Ini</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Tidak ada jadwal hari ini atau hari libur.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Papan Pengumuman</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Kosong.</p>
            </div>
        </div>
    </div>
</div>
@endsection
