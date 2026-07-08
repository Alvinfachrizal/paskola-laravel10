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
                Selamat {{ $greeting }}, {{ explode(' ', Auth::user()->name)[0] }} 👩‍👦
            </h2>
            <p class="text-muted mb-0" style="font-size:0.875rem;">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </p>
        </div>
        <div class="mt-2 mt-md-0 d-flex gap-2">
            <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-2 fw-medium">
                <i class="bi bi-chat-dots"></i> Hubungi Guru
            </button>
            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 bg-white d-flex align-items-center gap-2 fw-medium">
                <i class="bi bi-telephone"></i> Kontak Sekolah
            </button>
        </div>
    </div>
@endsection

@section('content')
<!-- Child Identification Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-4">
            <div class="d-flex align-items-center gap-4 w-100">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 60px; height: 60px; font-size: 1.5rem; font-weight:600;">
                    A
                </div>
                <div>
                    <h4 class="fw-bold text-dark mb-1">Andi Pratama</h4>
                    <p class="text-muted mb-0" style="font-size:0.85rem;">X IPA 1 • NISN 0087654321</p>
                </div>
            </div>
            <div class="text-start text-md-end w-100">
                <p class="text-muted mb-1" style="font-size:0.8rem;">Wali Kelas</p>
                <div class="fw-bold text-dark">Bpk. Hendra P.</div>
            </div>
        </div>
    </div>
</div>

<!-- Top Stats Row -->
<div class="row g-3 g-md-4 mb-4">
    <!-- Stat 1 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3 mb-md-4">
                <div class="stat-icon rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-clipboard-check"></i>
                </div>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">91.8%</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Kehadiran</p>
                <small class="text-muted" style="font-size:0.7rem;">68 / 74 hari hadir</small>
            </div>
        </div>
    </div>
    <!-- Stat 2 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3 mb-md-4">
                <div class="stat-icon rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-star"></i>
                </div>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">82.0</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Rata Nilai</p>
                <small class="text-muted" style="font-size:0.7rem;">5 mata pelajaran</small>
            </div>
        </div>
    </div>
    <!-- Stat 3 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3 mb-md-4">
                <div class="stat-icon rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3 text-danger">1</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Tagihan SPP</p>
                <small class="text-danger" style="font-size:0.7rem;">belum dibayar</small>
            </div>
        </div>
    </div>
    <!-- Stat 4 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3 mb-md-4">
                <div class="stat-icon rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-chat-text"></i>
                </div>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">2</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Pesan Guru</p>
                <small class="text-muted" style="font-size:0.7rem;">pesan belum dibaca</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri -->
    <div class="col-lg-8 d-flex flex-column gap-4">
        <!-- Absensi Terakhir -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    <h6 class="fw-bold mb-0 text-dark" style="font-size:0.95rem;">Absensi 5 Hari Terakhir</h6>
                </div>
                <div class="d-flex gap-3 text-center" style="font-size:0.75rem;">
                    <div><span class="text-success fw-bold">68</span><br><span class="text-muted">Hadir</span></div>
                    <div><span class="text-primary fw-bold">3</span><br><span class="text-muted">Izin</span></div>
                    <div><span class="text-warning fw-bold">2</span><br><span class="text-muted">Sakit</span></div>
                    <div><span class="text-danger fw-bold">1</span><br><span class="text-muted">Alfa</span></div>
                </div>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="d-flex flex-column gap-0">
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="fw-medium text-dark">Senin, 7 Jul</div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-medium">Hadir</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="fw-medium text-dark">Selasa, 8 Jul</div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-medium">Hadir</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="fw-medium text-dark">Rabu, 9 Jul</div>
                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1 fw-medium">Sakit</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="fw-medium text-dark">Kamis, 10 Jul</div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-medium">Hadir</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-3">
                        <div class="fw-medium text-dark">Jum'at, 11 Jul</div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-medium">Hadir</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nilai UTS Terbaru -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-star text-warning"></i>
                    <h6 class="fw-bold mb-0 text-dark" style="font-size:0.95rem;">Nilai UTS Terbaru</h6>
                </div>
                <a href="#" class="text-primary text-decoration-none" style="font-size:0.85rem;">Detail &rarr;</a>
            </div>
            <div class="card-body px-4 pb-4 pt-3">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-dark" style="font-size:0.85rem;">Matematika</span>
                            <span class="fw-bold text-success" style="font-size:0.9rem;">85</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-dark" style="font-size:0.85rem;">Bahasa Inggris</span>
                            <span class="fw-bold text-warning" style="font-size:0.9rem;">79</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 79%"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-dark" style="font-size:0.85rem;">Fisika</span>
                            <span class="fw-bold text-success" style="font-size:0.9rem;">88</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 88%"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-dark" style="font-size:0.85rem;">Sejarah</span>
                            <span class="fw-bold text-warning" style="font-size:0.9rem;">76</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 76%"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-dark" style="font-size:0.85rem;">Kimia</span>
                            <span class="fw-bold text-success" style="font-size:0.9rem;">82</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 82%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="col-lg-4 d-flex flex-column gap-4">
        <!-- Tagihan SPP -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                <i class="bi bi-wallet2 text-primary"></i>
                <h6 class="fw-bold mb-0 text-dark" style="font-size:0.95rem;">Tagihan SPP</h6>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
                        <div>
                            <div class="fw-semibold text-dark" style="font-size:0.85rem;">SPP Juli 2026</div>
                            <div class="text-muted" style="font-size:0.75rem;">Rp 250.000 • 5 Jul 2026</div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="bi bi-check-lg"></i> Lunas</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
                        <div>
                            <div class="fw-semibold text-dark" style="font-size:0.85rem;">SPP Juni 2026</div>
                            <div class="text-muted" style="font-size:0.75rem;">Rp 250.000 • 3 Jun 2026</div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="bi bi-check-lg"></i> Lunas</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold text-danger" style="font-size:0.85rem;">SPP Agust 2026</div>
                            <div class="text-muted" style="font-size:0.75rem;">Rp 250.000 • Jatuh tempo 5 Agt</div>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1"><i class="bi bi-exclamation-circle"></i> Belum</span>
                    </div>
                </div>
                <button class="btn btn-primary w-100 rounded-pill fw-medium shadow-sm transition-hover py-2">
                    Bayar SPP Sekarang
                </button>
            </div>
        </div>

        <!-- Pengumuman Sekolah -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                <i class="bi bi-bell text-warning"></i>
                <h6 class="fw-bold mb-0 text-dark" style="font-size:0.95rem;">Pengumuman Sekolah</h6>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="d-flex flex-column gap-3">
                    <div class="border-bottom pb-3">
                        <div class="d-flex gap-2">
                            <i class="bi bi-info-circle text-danger mt-1" style="font-size:0.9rem;"></i>
                            <div>
                                <h6 class="fw-semibold mb-1 text-dark" style="font-size:0.85rem;">UTS Semester Ganjil mulai 21 Juli 2026</h6>
                                <div class="text-muted" style="font-size:0.75rem;">1 jam lalu</div>
                            </div>
                        </div>
                    </div>
                    <div class="border-bottom pb-3">
                        <div class="d-flex gap-2">
                            <i class="bi bi-info-circle text-success mt-1" style="font-size:0.9rem;"></i>
                            <div>
                                <h6 class="fw-semibold mb-1 text-dark" style="font-size:0.85rem;">Laporan perkembangan siswa tersedia</h6>
                                <div class="text-muted" style="font-size:0.75rem;">2 hari lalu</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex gap-2">
                            <i class="bi bi-info-circle text-danger mt-1" style="font-size:0.9rem;"></i>
                            <div>
                                <h6 class="fw-semibold mb-1 text-dark" style="font-size:0.85rem;">Pertemuan wali murid - 18 Juli 2026</h6>
                                <div class="text-muted" style="font-size:0.75rem;">3 hari lalu</div>
                            </div>
                        </div>
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
</style>
@endsection
