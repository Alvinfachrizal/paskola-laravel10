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
                Selamat {{ $greeting }}, {{ explode(' ', Auth::user()->name)[0] }} 👏
            </h2>
            <p class="text-muted mb-0" style="font-size:0.875rem;">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </p>
        </div>
        <div class="mt-2 mt-md-0">
            <div class="bg-white border rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="font-size:0.875rem;">
                <i class="bi bi-book text-primary"></i>
                <span class="fw-semibold">Matematika</span>
                <span class="badge bg-success-subtle text-success ms-1 rounded-pill">Guru Mapel</span>
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
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">4</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Kelas Diampu</p>
                <small class="text-muted" style="font-size:0.7rem;">kelas aktif</small>
            </div>
        </div>
    </div>
    <!-- Stat 2 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3 mb-md-4">
                <div class="stat-icon rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-book"></i>
                </div>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">121</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Total Siswa</p>
                <small class="text-muted" style="font-size:0.7rem;">siswa diajar</small>
            </div>
        </div>
    </div>
    <!-- Stat 3 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3 mb-md-4">
                <div class="stat-icon rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-clock"></i>
                </div>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">4</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Jam Hari Ini</p>
                <small class="text-muted" style="font-size:0.7rem;">sesi mengajar</small>
            </div>
        </div>
    </div>
    <!-- Stat 4 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3 mb-md-4">
                <div class="stat-icon rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-clipboard-check"></i>
                </div>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">20</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Tugas Dinilai</p>
                <small class="text-muted" style="font-size:0.7rem;">perlu diperiksa</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri -->
    <div class="col-lg-8">
        <!-- Jadwal Mengajar -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0" style="font-size:0.95rem;">Jadwal Mengajar Hari Ini</h6>
                <a href="#" class="text-primary text-decoration-none" style="font-size:0.85rem;">Lihat Semua &rarr;</a>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="d-flex flex-column gap-0">
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-center" style="min-width:60px;">
                                <div class="fw-bold text-primary">07.30</div>
                                <div class="text-muted" style="font-size:0.7rem;">s/d 09.00</div>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Matematika</div>
                                <div class="text-muted" style="font-size:0.8rem;">X IPA 1 • R. 01</div>
                            </div>
                        </div>
                        <span class="badge bg-light text-secondary rounded-pill px-3 py-1">Selesai</span>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-center" style="min-width:60px;">
                                <div class="fw-bold text-primary">09.15</div>
                                <div class="text-muted" style="font-size:0.7rem;">s/d 10.45</div>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Matematika</div>
                                <div class="text-muted" style="font-size:0.8rem;">X IPA 2 • R. 03</div>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-medium">Berlangsung</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-center" style="min-width:60px;">
                                <div class="fw-bold text-primary">11.00</div>
                                <div class="text-muted" style="font-size:0.7rem;">s/d 12.30</div>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Matematika</div>
                                <div class="text-muted" style="font-size:0.8rem;">XI IPS 1 • R. 07</div>
                            </div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-medium">Akan Datang</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-center" style="min-width:60px;">
                                <div class="fw-bold text-primary">13.30</div>
                                <div class="text-muted" style="font-size:0.7rem;">s/d 15.00</div>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Matematika</div>
                                <div class="text-muted" style="font-size:0.8rem;">XI IPS 2 • R. 08</div>
                            </div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-medium">Akan Datang</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kelas yang Diampu -->
        <h6 class="fw-bold mb-3 mt-4" style="font-size:0.95rem;">Kelas yang Diampu</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-hover cursor-pointer">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded px-2 py-1"><i class="bi bi-people"></i></div>
                        <i class="bi bi-chevron-right text-muted" style="font-size:0.8rem;"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">X IPA 1</h5>
                    <p class="text-muted mb-3" style="font-size:0.8rem;">32 siswa</p>
                    <div class="d-flex align-items-center gap-2" style="font-size:0.8rem;">
                        <i class="bi bi-graph-up text-success"></i>
                        <span class="fw-semibold">Rata-rata 82.4</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-hover cursor-pointer">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded px-2 py-1"><i class="bi bi-people"></i></div>
                        <i class="bi bi-chevron-right text-muted" style="font-size:0.8rem;"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">X IPA 2</h5>
                    <p class="text-muted mb-3" style="font-size:0.8rem;">30 siswa</p>
                    <div class="d-flex align-items-center gap-2" style="font-size:0.8rem;">
                        <i class="bi bi-graph-down text-warning"></i>
                        <span class="fw-semibold">Rata-rata 79.8</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-hover cursor-pointer">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded px-2 py-1"><i class="bi bi-people"></i></div>
                        <i class="bi bi-chevron-right text-muted" style="font-size:0.8rem;"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">XI IPS 1</h5>
                    <p class="text-muted mb-3" style="font-size:0.8rem;">28 siswa</p>
                    <div class="d-flex align-items-center gap-2" style="font-size:0.8rem;">
                        <i class="bi bi-graph-down text-warning"></i>
                        <span class="fw-semibold">Rata-rata 75.2</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-hover cursor-pointer">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded px-2 py-1"><i class="bi bi-people"></i></div>
                        <i class="bi bi-chevron-right text-muted" style="font-size:0.8rem;"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">XI IPS 2</h5>
                    <p class="text-muted mb-3" style="font-size:0.8rem;">31 siswa</p>
                    <div class="d-flex align-items-center gap-2" style="font-size:0.8rem;">
                        <i class="bi bi-graph-up text-success"></i>
                        <span class="fw-semibold">Rata-rata 77.6</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="col-lg-4 d-flex flex-column gap-4">
        <!-- Pengumuman -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                <i class="bi bi-bell text-primary"></i>
                <h6 class="fw-bold mb-0" style="font-size:0.95rem;">Pengumuman</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                        <div class="d-flex gap-2">
                            <i class="bi bi-info-circle text-warning mt-1" style="font-size:0.9rem;"></i>
                            <div>
                                <h6 class="fw-semibold mb-1 text-dark" style="font-size:0.85rem;">Rapat guru mingguan - Senin 14.00</h6>
                                <div class="text-muted" style="font-size:0.75rem;">1 jam lalu</div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                        <div class="d-flex gap-2">
                            <i class="bi bi-exclamation-circle text-danger mt-1" style="font-size:0.9rem;"></i>
                            <div>
                                <h6 class="fw-semibold mb-1 text-dark" style="font-size:0.85rem;">Deadline input nilai UTS: 12 Juli 2026</h6>
                                <div class="text-muted" style="font-size:0.75rem;">3 jam lalu</div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                        <div class="d-flex gap-2">
                            <i class="bi bi-check-circle text-success mt-1" style="font-size:0.9rem;"></i>
                            <div>
                                <h6 class="fw-semibold mb-1 text-dark" style="font-size:0.85rem;">Pelatihan digitalisasi kelas - 15 Juli</h6>
                                <div class="text-muted" style="font-size:0.75rem;">Kemarin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tugas Perlu Diperiksa -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                <i class="bi bi-journal-check text-danger"></i>
                <h6 class="fw-bold mb-0" style="font-size:0.95rem;">Tugas Perlu Diperiksa</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-semibold text-dark" style="font-size:0.85rem;">PR Bab 3 - Fungsi Kuadrat</div>
                            <div class="text-muted" style="font-size:0.75rem;">X IPA 1</div>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">8 siswa</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-semibold text-dark" style="font-size:0.85rem;">Kuis Integral</div>
                            <div class="text-muted" style="font-size:0.75rem;">XI IPS 1</div>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">12 siswa</span>
                    </div>
                </div>
            </div>
        </div>
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
    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endsection
