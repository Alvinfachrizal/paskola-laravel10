@extends('layouts.app-bootstrap')

@section('header')
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center w-100 gap-2 gap-md-3">
        <div>
            @php
                $hour = date('H');
                if ($hour < 12) $greeting = 'Pagi';
                elseif ($hour < 15) $greeting = 'Siang';
                elseif ($hour < 18) $greeting = 'Sore';
                else $greeting = 'Malam';
            @endphp
            <h2 class="h3 mb-1 text-dark fw-bold" style="letter-spacing:-0.5px;">
                Selamat {{ $greeting }}, {{ explode(' ', Auth::user()->name)[0] }} 👋
            </h2>
            <p class="text-muted mb-0" style="font-size:0.875rem;">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </p>
        </div>
        <div class="mt-2 mt-md-0">
            <div class="bg-white border rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="font-size:0.875rem;">
                <i class="bi bi-shield-lock text-primary"></i>
                <span class="fw-semibold">SMA N 1 Contoh</span>
                <span class="badge bg-success-subtle text-success ms-1 rounded-pill">Aktif</span>
            </div>
        </div>
    </div>
@endsection

@section('content')
<!-- Top Stats Row -->
<div class="row g-3 g-md-4 mb-4">
    <!-- Stat 1 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3 mb-md-4">
                <div class="stat-icon rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-mortarboard"></i>
                </div>
                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size:0.7rem;"><i class="bi bi-arrow-up-short"></i>+24</span>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">1.248</h3>
                <p class="text-muted mb-0" style="font-size:0.75rem;">Total Siswa</p>
            </div>
        </div>
    </div>
    <!-- Stat 2 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3 mb-md-4">
                <div class="stat-icon rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-people"></i>
                </div>
                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size:0.7rem;"><i class="bi bi-arrow-up-short"></i>+3</span>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">86</h3>
                <p class="text-muted mb-0" style="font-size:0.75rem;">Total Guru</p>
            </div>
        </div>
    </div>
    <!-- Stat 3 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3 mb-md-4">
                <div class="stat-icon rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1" style="font-size:0.7rem;"><i class="bi bi-arrow-down-short"></i>-1.3%</span>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">94.2%</h3>
                <p class="text-muted mb-0" style="font-size:0.75rem;">Kehadiran (H)</p>
            </div>
        </div>
    </div>
    <!-- Stat 4 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3 mb-md-4">
                <div class="stat-icon rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-wallet2"></i>
                </div>
                <span class="fw-semibold text-warning" style="font-size:0.7rem;">Rp 45Jt</span>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">127</h3>
                <p class="text-muted mb-0" style="font-size:0.75rem;">Tagihan Belum</p>
            </div>
        </div>
    </div>
</div>

<!-- Aksi Cepat Row -->
<h6 class="fw-bold mb-3 mt-4 mt-md-2" style="font-size:0.95rem;">Aksi Cepat</h6>
<div class="row g-3 g-md-4 mb-4 mb-md-5">
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.students.index', ['action' => 'create']) }}" class="card border-0 shadow-sm rounded-4 text-center py-3 py-md-4 text-decoration-none transition-hover d-block">
            <i class="bi bi-mortarboard text-primary mb-2 mb-md-3 d-block" style="font-size:1.5rem;"></i>
            <span class="fw-semibold text-dark" style="font-size:0.85rem;">Tambah Siswa</span>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="#" class="card border-0 shadow-sm rounded-4 text-center py-3 py-md-4 text-decoration-none transition-hover d-block">
            <i class="bi bi-clipboard-check text-info mb-2 mb-md-3 d-block" style="font-size:1.5rem;"></i>
            <span class="fw-semibold text-dark" style="font-size:0.85rem;">Input Absensi</span>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="#" class="card border-0 shadow-sm rounded-4 text-center py-3 py-md-4 text-decoration-none transition-hover d-block">
            <i class="bi bi-journal-text text-primary mb-2 mb-md-3 d-block" style="font-size:1.5rem;"></i>
            <span class="fw-semibold text-dark" style="font-size:0.85rem;">Input Nilai</span>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="#" class="card border-0 shadow-sm rounded-4 text-center py-3 py-md-4 text-decoration-none transition-hover d-block">
            <i class="bi bi-wallet2 text-info mb-2 mb-md-3 d-block" style="font-size:1.5rem;"></i>
            <span class="fw-semibold text-dark" style="font-size:0.85rem;">Tagihan SPP</span>
        </a>
    </div>
</div>

<!-- Bottom Layout: Activity and System Status -->
<div class="row g-4">
    <!-- Left Column: Aktivitas Terkini -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0" style="font-size:0.95rem;">Aktivitas Terkini</h6>
                <a href="#" class="text-primary text-decoration-none" style="font-size:0.85rem;">Lihat Semua &rarr;</a>
            </div>
            <div class="card-body px-3 px-md-4 pb-4 pt-3">
                <ul class="list-unstyled mb-0 position-relative timeline-list">
                    <!-- Timeline Items -->
                    <li class="mb-4 d-flex align-items-start position-relative">
                        <div class="bg-primary rounded-circle mt-1 flex-shrink-0" style="width:10px;height:10px;"></div>
                        <div class="ms-3 pb-3 border-bottom w-100">
                            <p class="mb-1 text-dark fw-medium" style="font-size:0.85rem;">Andi Pratama ditambahkan ke Kelas X IPA 1</p>
                            <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i>5 mnt lalu</span>
                        </div>
                    </li>
                    <li class="mb-4 d-flex align-items-start position-relative">
                        <div class="bg-success rounded-circle mt-1 flex-shrink-0" style="width:10px;height:10px;"></div>
                        <div class="ms-3 pb-3 border-bottom w-100">
                            <p class="mb-1 text-dark fw-medium" style="font-size:0.85rem;">Nilai UTS Matematika kelas XI IPS 2 telah diinput</p>
                            <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i>32 mnt lalu</span>
                        </div>
                    </li>
                    <li class="mb-4 d-flex align-items-start position-relative">
                        <div class="bg-danger rounded-circle mt-1 flex-shrink-0" style="width:10px;height:10px;"></div>
                        <div class="ms-3 pb-3 border-bottom w-100">
                            <p class="mb-1 text-dark fw-medium" style="font-size:0.85rem;">12 siswa alfa hari ini</p>
                            <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i>1 jam lalu</span>
                        </div>
                    </li>
                    <li class="mb-4 d-flex align-items-start position-relative">
                        <div class="bg-success rounded-circle mt-1 flex-shrink-0" style="width:10px;height:10px;"></div>
                        <div class="ms-3 pb-3 border-bottom w-100">
                            <p class="mb-1 text-dark fw-medium" style="font-size:0.85rem;">Sri Wahyuni membayar SPP bulan Juli</p>
                            <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i>2 jam lalu</span>
                        </div>
                    </li>
                    <li class="d-flex align-items-start position-relative">
                        <div class="bg-primary rounded-circle mt-1 flex-shrink-0" style="width:10px;height:10px;"></div>
                        <div class="ms-3 w-100">
                            <p class="mb-1 text-dark fw-medium" style="font-size:0.85rem;">Pengumuman libur Idul Adha dipublikasikan</p>
                            <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i>3 jam lalu</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Right Column: Status & Progress -->
    <div class="col-lg-4 d-flex flex-column gap-4">
        <!-- Kehadiran Hari Ini -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-3 px-md-4">
                <h6 class="fw-bold mb-0" style="font-size:0.95rem;">Kehadiran Hari Ini</h6>
            </div>
            <div class="card-body px-3 px-md-4 pb-4">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.8rem;">
                        <span class="text-muted">Hadir</span>
                        <span class="fw-semibold text-dark">1176</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 94%" aria-valuenow="94" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.8rem;">
                        <span class="text-muted">Izin</span>
                        <span class="fw-semibold text-dark">38</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 3%" aria-valuenow="3" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.8rem;">
                        <span class="text-muted">Sakit</span>
                        <span class="fw-semibold text-dark">24</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: 2%; background-color:#fd7e14;" aria-valuenow="2" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.8rem;">
                        <span class="text-muted">Alfa</span>
                        <span class="fw-semibold text-dark">10</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 1%" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Sistem -->
        <div class="card border-0 shadow-sm rounded-4 flex-grow-1">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-3 px-md-4">
                <h6 class="fw-bold mb-0" style="font-size:0.95rem;">Status Sistem</h6>
            </div>
            <div class="card-body px-3 px-md-4 pb-4">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted" style="font-size:0.85rem;"><i class="bi bi-check-circle text-success me-2"></i>API Server</span>
                        <span class="badge bg-success-subtle text-success rounded-pill fw-medium">Online</span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted" style="font-size:0.85rem;"><i class="bi bi-check-circle text-success me-2"></i>Database</span>
                        <span class="badge bg-success-subtle text-success rounded-pill fw-medium">Online</span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted" style="font-size:0.85rem;"><i class="bi bi-x-circle text-danger me-2"></i>Email Service</span>
                        <span class="badge bg-danger-subtle text-danger rounded-pill fw-medium">Offline</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Big Button -->
        <button class="btn btn-primary w-100 py-3 rounded-3 fw-medium shadow-sm transition-hover">
            <i class="bi bi-plus-lg me-2"></i>Tambah Data Baru
        </button>
    </div>
</div>

<style>
    .transition-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .transition-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    }
    
    /* Simulate timeline vertical line */
    .timeline-list::before {
        content: '';
        position: absolute;
        top: 5px;
        bottom: 10px;
        left: 4.5px;
        width: 1px;
        background: #e2e8f0;
        z-index: 0;
    }
    .timeline-list li > div {
        z-index: 1;
    }
</style>
@endsection
