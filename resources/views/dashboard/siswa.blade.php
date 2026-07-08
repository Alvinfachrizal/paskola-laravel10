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
                Selamat {{ $greeting }}, {{ explode(' ', Auth::user()->name)[0] }} 📚
            </h2>
            <p class="text-muted mb-0" style="font-size:0.875rem;">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </p>
        </div>
        <div class="mt-2 mt-md-0">
            <div class="bg-white border rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="font-size:0.875rem;">
                <i class="bi bi-mortarboard text-primary"></i>
                <span class="fw-semibold">X IPA 1</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1 rounded-pill">2025/2026 Ganjil</span>
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
                <div class="stat-icon rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-clipboard-check"></i>
                </div>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">91.8%</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Kehadiran</p>
                <small class="text-muted" style="font-size:0.7rem;">68 hadir / 74 hari</small>
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
                <div class="stat-icon rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-calendar-event"></i>
                </div>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">4</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Jam Hari Ini</p>
                <small class="text-muted" style="font-size:0.7rem;">sesi belajar</small>
            </div>
        </div>
    </div>
    <!-- Stat 4 -->
    <div class="col-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 p-md-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-3 mb-md-4">
                <div class="stat-icon rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size:1.1rem;">
                    <i class="bi bi-book"></i>
                </div>
            </div>
            <div>
                <h3 class="fw-bold mb-1 fs-4 fs-md-3">3</h3>
                <p class="text-muted mb-0 fw-medium" style="font-size:0.85rem;">Tugas Aktif</p>
                <small class="text-muted" style="font-size:0.7rem;">perlu dikumpulkan</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri -->
    <div class="col-lg-8">
        <!-- Jadwal Belajar -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock text-primary"></i>
                    <h6 class="fw-bold mb-0" style="font-size:0.95rem;">Jadwal Belajar Hari Ini</h6>
                </div>
                <a href="#" class="text-primary text-decoration-none" style="font-size:0.85rem;">Lihat Semua &rarr;</a>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div class="d-flex flex-column gap-0">
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-center" style="min-width:60px;">
                                <div class="fw-bold text-primary">07.30</div>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Matematika</div>
                                <div class="text-muted" style="font-size:0.8rem;">Bpk. Agus S. • R. 01</div>
                            </div>
                        </div>
                        <span class="badge bg-light text-secondary rounded-pill px-3 py-1">Selesai</span>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-center" style="min-width:60px;">
                                <div class="fw-bold text-primary">09.15</div>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Bahasa Inggris</div>
                                <div class="text-muted" style="font-size:0.8rem;">Ibu Dewi R. • R. 03</div>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-medium">Berlangsung</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-center" style="min-width:60px;">
                                <div class="fw-bold text-primary">11.00</div>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Fisika</div>
                                <div class="text-muted" style="font-size:0.8rem;">Bpk. Hendra W. • Lab. IPA</div>
                            </div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-medium">Akan Datang</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-center" style="min-width:60px;">
                                <div class="fw-bold text-primary">13.30</div>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Sejarah</div>
                                <div class="text-muted" style="font-size:0.8rem;">Ibu Siti N. • R. 07</div>
                            </div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-medium">Akan Datang</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nilai UTS Terakhir -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-star text-warning"></i>
                    <h6 class="fw-bold mb-0" style="font-size:0.95rem;">Nilai UTS Terakhir</h6>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">Semester Ganjil</span>
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
        <!-- Rekap Absensi -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0" style="font-size:0.95rem;">Rekap Absensi</h6>
                <div class="text-muted" style="font-size:0.75rem;">Semester Ganjil 2025/2026</div>
            </div>
            <div class="card-body px-4 pb-4 pt-3">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 text-center">
                            <h3 class="fw-bold mb-0">68</h3>
                            <div class="fw-medium" style="font-size:0.8rem;">Hadir</div>
                            <small class="opacity-75" style="font-size:0.7rem;">92%</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 text-center">
                            <h3 class="fw-bold mb-0">3</h3>
                            <div class="fw-medium" style="font-size:0.8rem;">Izin</div>
                            <small class="opacity-75" style="font-size:0.7rem;">4%</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 text-center">
                            <h3 class="fw-bold mb-0">2</h3>
                            <div class="fw-medium" style="font-size:0.8rem;">Sakit</div>
                            <small class="opacity-75" style="font-size:0.7rem;">3%</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3 text-center">
                            <h3 class="fw-bold mb-0">1</h3>
                            <div class="fw-medium" style="font-size:0.8rem;">Alfa</div>
                            <small class="opacity-75" style="font-size:0.7rem;">1%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengumuman -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                <i class="bi bi-bell text-warning"></i>
                <h6 class="fw-bold mb-0" style="font-size:0.95rem;">Pengumuman</h6>
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
                            <i class="bi bi-info-circle text-danger mt-1" style="font-size:0.9rem;"></i>
                            <div>
                                <h6 class="fw-semibold mb-1 text-dark" style="font-size:0.85rem;">Pengumpulan tugas Bahasa Inggris - besok</h6>
                                <div class="text-muted" style="font-size:0.75rem;">3 jam lalu</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex gap-2">
                            <i class="bi bi-info-circle text-muted mt-1" style="font-size:0.9rem;"></i>
                            <div>
                                <h6 class="fw-semibold mb-1 text-dark" style="font-size:0.85rem;">Ekskul Robotika dibuka pendaftaran baru</h6>
                                <div class="text-muted" style="font-size:0.75rem;">Kemarin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
