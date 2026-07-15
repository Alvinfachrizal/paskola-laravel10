@extends('ppdb.layout')
@section('title', 'Portal PPDB Online')

@section('content')

{{-- Hero --}}
<section class="ppdb-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge-wave mb-3 d-inline-block">
                    <i class="bi bi-calendar3 me-1"></i>
                    Tahun Ajaran {{ date('Y') }}/{{ date('Y') + 1 }}
                </span>
                <h1 class="display-5 fw-bold mb-3">
                    Penerimaan Peserta<br>Didik Baru Online
                </h1>
                <p class="lead mb-4" style="opacity:.85">
                    Daftarkan diri Anda secara online. Mudah, cepat, dan transparan.
                    Pantau status pendaftaran kapan saja tanpa perlu datang ke sekolah.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('ppdb.register.form') }}" class="btn btn-warning btn-lg fw-bold px-4 rounded-pill shadow">
                        <i class="bi bi-pencil-square me-2"></i>Daftar Sekarang
                    </a>
                    <a href="{{ route('ppdb.cek-status.form') }}" class="btn btn-outline-light btn-lg fw-semibold px-4 rounded-pill">
                        <i class="bi bi-search me-2"></i>Cek Status Saya
                    </a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center pt-4 pt-lg-0">
                <i class="bi bi-mortarboard" style="font-size: 9rem; opacity:.2; color:#fff;"></i>
            </div>
        </div>
    </div>
</section>

{{-- Gelombang Aktif --}}
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold mb-1 text-center" style="font-size:1.6rem">Gelombang Pendaftaran</h2>
        <p class="text-muted text-center mb-4">Pilih gelombang yang sesuai dengan jadwal Anda</p>

        @if($activeWaves->isEmpty())
            <div class="alert alert-info text-center rounded-3 py-4">
                <i class="bi bi-info-circle fs-4 me-2"></i>
                <strong>Belum ada gelombang pendaftaran yang aktif.</strong><br>
                Silakan pantau pengumuman dari sekolah kami.
            </div>
        @else
            <div class="row g-4 justify-content-center">
                @foreach($activeWaves as $wave)
                <div class="col-md-5">
                    <div class="ppdb-card card h-100 p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h4 class="fw-bold mb-0">{{ $wave->name }}</h4>
                            <span class="badge bg-success rounded-pill px-3">Aktif</span>
                        </div>
                        <p class="text-muted small mb-3">{{ $wave->description }}</p>
                        <div class="d-flex flex-column gap-2 mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-check text-primary"></i>
                                <span class="small">Dibuka: <strong>{{ $wave->start_date->format('d M Y') }}</strong></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-x text-danger"></i>
                                <span class="small">Ditutup: <strong>{{ $wave->end_date->format('d M Y') }}</strong></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people text-warning"></i>
                                <span class="small">Sisa Kuota: <strong>{{ $wave->remainingQuota() }}</strong> dari {{ $wave->quota }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-cash-coin text-info"></i>
                                <span class="small">
                                    Biaya Pendaftaran:
                                    <strong>{{ $wave->hasFee() ? 'Rp ' . number_format($wave->registration_fee, 0, ',', '.') : 'Gratis' }}</strong>
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('ppdb.register.form') }}" class="btn btn-ppdb-primary w-100">
                            <i class="bi bi-arrow-right-circle me-2"></i>Daftar di Gelombang Ini
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- Alur Pendaftaran --}}
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="fw-bold text-center mb-1" style="font-size:1.6rem">Cara Mendaftar</h2>
        <p class="text-muted text-center mb-5">Ikuti 4 langkah mudah berikut</p>
        <div class="row g-4">
            @foreach([
                ['icon'=>'bi-pencil-square','title'=>'Isi Formulir','desc'=>'Isi data diri, data orang tua, pilih gelombang, dan data seragam secara lengkap.'],
                ['icon'=>'bi-file-earmark-arrow-up','title'=>'Upload Dokumen','desc'=>'Upload scan dokumen: KK, Akta Lahir, Pas Foto, Ijazah/SKL, dan SKHUN.'],
                ['icon'=>'bi-hourglass-split','title'=>'Tunggu Verifikasi','desc'=>'Panitia akan memverifikasi dokumen Anda dalam 2–3 hari kerja.'],
                ['icon'=>'bi-trophy','title'=>'Pengumuman','desc'=>'Cek status kelulusan seleksi dan lakukan daftar ulang jika dinyatakan lolos.'],
            ] as $i => $step)
            <div class="col-6 col-md-3">
                <div class="ppdb-card card p-4 text-center h-100">
                    <div class="d-flex align-items-center justify-content-center mb-3" style="gap:.75rem">
                        <div class="step-badge">{{ $i+1 }}</div>
                    </div>
                    <div class="mb-2">
                        <i class="bi {{ $step['icon'] }} fs-2 text-primary"></i>
                    </div>
                    <h6 class="fw-bold mb-1">{{ $step['title'] }}</h6>
                    <p class="text-muted small mb-0">{{ $step['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA Cek Status --}}
<section class="py-5">
    <div class="container">
        <div class="ppdb-card card p-5 text-center"
             style="background: linear-gradient(135deg, #1a56db 0%, #0e9f6e 100%); color: #fff;">
            <i class="bi bi-search fs-1 mb-3" style="opacity:.7"></i>
            <h3 class="fw-bold mb-2">Sudah pernah mendaftar?</h3>
            <p class="mb-4" style="opacity:.85">Masukkan kode pendaftaran dan tanggal lahir Anda untuk melihat status terkini.</p>
            <a href="{{ route('ppdb.cek-status.form') }}" class="btn btn-light btn-lg fw-bold px-5 rounded-pill mx-auto">
                <i class="bi bi-search me-2"></i>Cek Status Pendaftaran
            </a>
        </div>
    </div>
</section>

@endsection
