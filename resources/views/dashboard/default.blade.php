@extends('layouts.app-bootstrap')

@section('header')
    <h2 class="h4 mb-0 text-dark">
        {{ __('Dashboard') }}
    </h2>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Selamat datang, {{ $user->name }}!</h5>
                <p class="card-text text-muted">Akun Anda saat ini belum memiliki akses ke modul manapun. Silakan hubungi Administrator Sekolah untuk mendapatkan akses yang sesuai.</p>
            </div>
        </div>
    </div>
</div>
@endsection
