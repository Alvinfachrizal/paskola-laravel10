@extends('layouts.app-bootstrap')

@section('header')
    <h2 class="h4 mb-0 text-dark">
        {{ __('Dashboard Admin') }}
    </h2>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-primary">
            Selamat datang kembali, <strong>{{ $user->name }}</strong>! Anda masuk sebagai {{ $user->role }}.
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Stat 1 -->
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Total Siswa</h5>
                <p class="card-text display-6">0</p>
            </div>
        </div>
    </div>
    <!-- Stat 2 -->
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Total Guru</h5>
                <p class="card-text display-6">0</p>
            </div>
        </div>
    </div>
    <!-- Stat 3 -->
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Total Kelas</h5>
                <p class="card-text display-6">0</p>
            </div>
        </div>
    </div>
    <!-- Stat 4 -->
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Mata Pelajaran</h5>
                <p class="card-text display-6">0</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0">Aktivitas Terakhir</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Belum ada aktivitas yang tercatat hari ini.</p>
            </div>
        </div>
    </div>
</div>
@endsection
