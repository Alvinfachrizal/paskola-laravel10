@extends('layouts.app-bootstrap')

@section('header')
    <h2 class="h4 mb-0 text-dark">
        {{ __('Dashboard Orang Tua / Wali') }}
    </h2>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-secondary">
            Selamat datang, <strong>{{ $user->name }}</strong>. Berikut adalah ringkasan informasi akademik anak Anda.
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Stat 1 -->
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-danger h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Tagihan Belum Lunas</h5>
                <p class="card-text display-6">Rp 0</p>
                <small>SPP / Uang Gedung</small>
            </div>
        </div>
    </div>
    <!-- Stat 2 -->
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Kehadiran Siswa</h5>
                <p class="card-text display-6">100%</p>
                <small>Bulan ini</small>
            </div>
        </div>
    </div>
    <!-- Stat 3 -->
    <div class="col-md-4 mb-3">
        <div class="card text-dark bg-warning h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Tugas Tertunda</h5>
                <p class="card-text display-6">0</p>
                <small>Minta anak Anda untuk menyelesaikannya</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Perkembangan Akademik Terakhir</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Belum ada data nilai baru yang masuk minggu ini.</p>
            </div>
        </div>
    </div>
</div>
@endsection
