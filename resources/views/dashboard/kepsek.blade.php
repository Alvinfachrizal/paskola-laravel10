@extends('layouts.app-bootstrap')
@section('title', 'Dashboard Kepsek')

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
                Selamat {{ $greeting }}, {{ explode(' ', Auth::user()->name)[0] }} 🎓
            </h2>
            <p class="text-muted mb-0" style="font-size:0.875rem;">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </p>
        </div>
        <div class="mt-2 mt-md-0">
            <div class="bg-white border rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="font-size:0.875rem;">
                <i class="bi bi-shield-lock text-primary"></i>
                <span class="fw-semibold">SMA N 1 Contoh</span>
                <span class="badge bg-success-subtle text-success ms-1 rounded-pill">Akreditasi A</span>
            </div>
        </div>
    </div>
@endsection

@section('content')
<!-- Top Stats Row 1 -->
<div class="row g-3 g-md-4 mb-3">
    <!-- Stat 1 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-mortarboard"></i>
                </div>
                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size:0.7rem;"><i class="bi bi-arrow-up-short"></i>+24</span>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">1.248</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Total Siswa</p>
            </div>
        </div>
    </div>
    <!-- Stat 2 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-people"></i>
                </div>
                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size:0.7rem;"><i class="bi bi-arrow-up-short"></i>+3</span>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">86</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Total Guru</p>
            </div>
        </div>
    </div>
    <!-- Stat 3 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1" style="font-size:0.7rem;"><i class="bi bi-arrow-down-short"></i>-1.3%</span>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">94.2%</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Rata Kehadiran</p>
            </div>
        </div>
    </div>
    <!-- Stat 4 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-star"></i>
                </div>
                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size:0.7rem;">Angkatan</span>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">98.7%</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Kelulusan</p>
            </div>
        </div>
    </div>
</div>

<!-- Top Stats Row 2 -->
<div class="row g-3 g-md-4 mb-4 mb-md-5">
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3">
            <div class="stat-icon rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; font-size:1.2rem;">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark">78.4</h4>
                <p class="text-muted mb-0" style="font-size:0.75rem;">Rata Nilai Sekolah</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3">
            <div class="stat-icon rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; font-size:1.2rem;">
                <i class="bi bi-star-fill"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark">142</h4>
                <p class="text-muted mb-0" style="font-size:0.75rem;">Siswa Berprestasi</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3">
            <div class="stat-icon rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; font-size:1.2rem;">
                <i class="bi bi-door-open"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark">36</h4>
                <p class="text-muted mb-0" style="font-size:0.75rem;">Kelas Aktif</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3">
            <div class="stat-icon rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; font-size:1.2rem;">
                <i class="bi bi-book-half"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark">24</h4>
                <p class="text-muted mb-0" style="font-size:0.75rem;">Mapel Tersedia</p>
            </div>
        </div>
    </div>
</div>


<!-- Bottom Layout: Activity and System Status -->
<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8 d-flex flex-column gap-4">
        
        <!-- Perlu Perhatian -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark" style="font-size:0.95rem;">Perlu Perhatian</h6>
                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">3 Item</span>
            </div>
            <div class="card-body px-3 px-md-4 pb-4 pt-2">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded-3 bg-white">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-circle text-danger"></i>
                            <span class="fw-medium text-dark" style="font-size:0.85rem;">12 siswa dengan absensi > 3x alpha bulan ini</span>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1" style="font-size:0.7rem;">Kritis</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded-3 bg-white">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle text-warning"></i>
                            <span class="fw-medium text-dark" style="font-size:0.85rem;">8 guru belum input nilai UTS</span>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2 py-1" style="font-size:0.7rem;">Perhatian</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded-3 bg-white">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle text-success"></i>
                            <span class="fw-medium text-dark" style="font-size:0.85rem;">Akreditasi A diperpanjang hingga 2028</span>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1" style="font-size:0.7rem;">Info</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded-3 bg-white">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-clock-history text-warning"></i>
                            <span class="fw-medium text-dark" style="font-size:0.85rem;">Deadline laporan BOS: 15 Jul 2026</span>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2 py-1" style="font-size:0.7rem;">Perhatian</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aktivitas Terkini -->
        <div class="card border-0 shadow-sm rounded-4 flex-grow-1">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark" style="font-size:0.95rem;">Aktivitas Terkini</h6>
                <a href="#" class="text-primary fw-medium text-decoration-none" style="font-size:0.8rem;">Lihat Semua &rarr;</a>
            </div>
            <div class="card-body px-3 px-md-4 pb-4 pt-3">
                <ul class="list-unstyled mb-0 position-relative timeline-list">
                    <li class="mb-4 d-flex align-items-start position-relative">
                        <div class="bg-success rounded-circle mt-1 flex-shrink-0" style="width:10px;height:10px;"></div>
                        <div class="ms-3 pb-3 border-bottom w-100">
                            <p class="mb-1 text-dark fw-medium" style="font-size:0.85rem;">Nilai UTS Matematika kelas X IPA 1 diinput</p>
                            <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i>5 mnt lalu</span>
                        </div>
                    </li>
                    <li class="mb-4 d-flex align-items-start position-relative">
                        <div class="bg-danger rounded-circle mt-1 flex-shrink-0" style="width:10px;height:10px;"></div>
                        <div class="ms-3 pb-3 border-bottom w-100">
                            <p class="mb-1 text-dark fw-medium" style="font-size:0.85rem;">12 siswa alfa hari ini tercatat sistem</p>
                            <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i>30 mnt lalu</span>
                        </div>
                    </li>
                    <li class="mb-4 d-flex align-items-start position-relative">
                        <div class="bg-primary rounded-circle mt-1 flex-shrink-0" style="width:10px;height:10px;"></div>
                        <div class="ms-3 pb-3 border-bottom w-100">
                            <p class="mb-1 text-dark fw-medium" style="font-size:0.85rem;">Guru Baru: Siti Rahayu (Bahasa Inggris)</p>
                            <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i>2 jam lalu</span>
                        </div>
                    </li>
                    <li class="mb-4 d-flex align-items-start position-relative">
                        <div class="bg-success rounded-circle mt-1 flex-shrink-0" style="width:10px;height:10px;"></div>
                        <div class="ms-3 pb-3 border-bottom w-100">
                            <p class="mb-1 text-dark fw-medium" style="font-size:0.85rem;">SPP Juli: 89 siswa baru melunasi</p>
                            <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i>3 jam lalu</span>
                        </div>
                    </li>
                    <li class="d-flex align-items-start position-relative">
                        <div class="bg-primary rounded-circle mt-1 flex-shrink-0" style="width:10px;height:10px;"></div>
                        <div class="ms-3 w-100">
                            <p class="mb-1 text-dark fw-medium" style="font-size:0.85rem;">Pengumuman libur dipublikasikan</p>
                            <span class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i>5 jam lalu</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4 d-flex flex-column gap-4">
        <!-- Kehadiran Hari Ini -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-3 px-md-4">
                <h6 class="fw-bold mb-0 text-dark" style="font-size:0.95rem;">Kehadiran Hari Ini</h6>
            </div>
            <div class="card-body px-3 px-md-4 pb-4 pt-3">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.8rem;">
                        <span class="text-muted">Hadir</span>
                        <span class="fw-semibold text-dark">1176 <span class="text-muted fw-normal">/ 1248</span></span>
                    </div>
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 94%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.8rem;">
                        <span class="text-muted">Izin</span>
                        <span class="fw-semibold text-dark">38 <span class="text-muted fw-normal">/ 1248</span></span>
                    </div>
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 3%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.8rem;">
                        <span class="text-muted">Sakit</span>
                        <span class="fw-semibold text-dark">24 <span class="text-muted fw-normal">/ 1248</span></span>
                    </div>
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: 2%; background-color:#fd7e14;"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.8rem;">
                        <span class="text-muted">Alfa</span>
                        <span class="fw-semibold text-dark">10 <span class="text-muted fw-normal">/ 1248</span></span>
                    </div>
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 1%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keuangan Sekolah -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-3 px-md-4 d-flex align-items-center gap-2">
                <i class="bi bi-wallet2 text-primary"></i>
                <h6 class="fw-bold mb-0 text-dark" style="font-size:0.95rem;">Keuangan Sekolah</h6>
            </div>
            <div class="card-body px-3 px-md-4 pb-4 pt-3">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle text-success" style="font-size:0.85rem;"></i>
                        <span class="text-muted fw-medium" style="font-size:0.85rem;">SPP Terkumpul Juli</span>
                    </div>
                    <span class="fw-bold text-primary" style="font-size:0.85rem;">Rp 285jt</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-circle text-warning" style="font-size:0.85rem;"></i>
                        <span class="text-muted fw-medium" style="font-size:0.85rem;">Tagihan Belum Lunas</span>
                    </div>
                    <span class="fw-bold text-dark" style="font-size:0.85rem;">127 siswa</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle text-success" style="font-size:0.85rem;"></i>
                        <span class="text-muted fw-medium" style="font-size:0.85rem;">Dana BOS Cair</span>
                    </div>
                    <span class="fw-bold text-success" style="font-size:0.85rem;">Rp 450jt</span>
                </div>
            </div>
        </div>
        
        <!-- Rata Nilai per Kelas -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-3 px-md-4">
                <h6 class="fw-bold mb-0 text-dark" style="font-size:0.95rem;">Rata Nilai per Kelas</h6>
            </div>
            <div class="card-body px-3 px-md-4 pb-4 pt-3">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-dark" style="font-size:0.85rem;">X IPA 1</span>
                            <span class="fw-bold text-success" style="font-size:0.85rem;">82</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 82%"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-dark" style="font-size:0.85rem;">X IPA 2</span>
                            <span class="fw-bold text-warning" style="font-size:0.85rem;">79</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 79%"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-dark" style="font-size:0.85rem;">XI IPA 1</span>
                            <span class="fw-bold text-success" style="font-size:0.85rem;">85</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-dark" style="font-size:0.85rem;">XI IPS 1</span>
                            <span class="fw-bold text-warning" style="font-size:0.85rem;">75</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 75%"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-dark" style="font-size:0.85rem;">XII IPA</span>
                            <span class="fw-bold text-success" style="font-size:0.85rem;">88</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 88%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

