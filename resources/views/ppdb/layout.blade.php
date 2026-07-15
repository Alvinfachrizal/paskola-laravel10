<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal PPDB Online - Penerimaan Peserta Didik Baru {{ date('Y') }}/{{ date('Y')+1 }}">
    <title>@yield('title', 'Portal PPDB Online') — Paskola</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    {{-- Google Fonts: Inter --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --ppdb-primary:   #1a56db;
            --ppdb-secondary: #0e9f6e;
            --ppdb-accent:    #f59e0b;
            --ppdb-dark:      #0f172a;
            --ppdb-surface:   #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--ppdb-surface);
            color: #1e293b;
        }

        /* ── Navbar ── */
        .ppdb-navbar {
            background: linear-gradient(135deg, var(--ppdb-dark) 0%, #1e3a5f 100%);
            box-shadow: 0 2px 16px rgba(0,0,0,.2);
        }
        .ppdb-navbar .navbar-brand {
            font-weight: 800;
            font-size: 1.25rem;
            color: #fff !important;
            letter-spacing: -.3px;
        }
        .ppdb-navbar .navbar-brand span { color: var(--ppdb-accent); }
        .ppdb-navbar .nav-link {
            color: rgba(255,255,255,.8) !important;
            font-weight: 500;
            transition: color .2s;
        }
        .ppdb-navbar .nav-link:hover { color: #fff !important; }
        .ppdb-navbar .btn-outline-light {
            border-radius: 50px;
            font-weight: 600;
            padding: .35rem 1.1rem;
        }

        /* ── Hero ── */
        .ppdb-hero {
            background: linear-gradient(135deg, var(--ppdb-dark) 0%, #1d4ed8 100%);
            padding: 5rem 0 4rem;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .ppdb-hero::before {
            content: '';
            position: absolute;
            top: -60px; right: -80px;
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(245,158,11,.25), transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .ppdb-hero h1 { font-weight: 800; letter-spacing: -.5px; }
        .ppdb-hero .badge-wave {
            background: rgba(255,255,255,.15);
            color: #fff;
            border: 1px solid rgba(255,255,255,.3);
            border-radius: 50px;
            padding: .35rem 1rem;
            font-size: .85rem;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }

        /* ── Cards ── */
        .ppdb-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,.07);
            transition: transform .2s, box-shadow .2s;
        }
        .ppdb-card:hover { transform: translateY(-3px); box-shadow: 0 8px 32px rgba(0,0,0,.12); }

        /* ── Steps indicator ── */
        .step-badge {
            width: 36px; height: 36px;
            background: var(--ppdb-primary);
            color: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .85rem;
            flex-shrink: 0;
        }

        /* ── Form ── */
        .form-section-title {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--ppdb-primary);
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: .5rem;
            margin-bottom: 1.25rem;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border-color: #cbd5e1;
            padding: .6rem .9rem;
            font-size: .95rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--ppdb-primary);
            box-shadow: 0 0 0 3px rgba(26,86,219,.15);
        }
        label.form-label { font-weight: 500; font-size: .9rem; color: #374151; }

        /* ── Status badges ── */
        .status-timeline .timeline-item {
            display: flex; gap: 1rem; align-items: flex-start;
            padding-bottom: 1.25rem;
            border-left: 2px solid #e2e8f0;
            padding-left: 1.5rem;
            position: relative;
        }
        .status-timeline .timeline-item::before {
            content: '';
            width: 12px; height: 12px;
            border-radius: 50%;
            background: #94a3b8;
            position: absolute;
            left: -7px; top: 4px;
        }
        .status-timeline .timeline-item.active::before { background: var(--ppdb-primary); }
        .status-timeline .timeline-item.done::before   { background: var(--ppdb-secondary); }

        /* ── Buttons ── */
        .btn-ppdb-primary {
            background: var(--ppdb-primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: .65rem 1.5rem;
            transition: background .2s, transform .15s;
        }
        .btn-ppdb-primary:hover { background: #1648c0; color: #fff; transform: translateY(-1px); }

        /* ── Footer ── */
        .ppdb-footer {
            background: var(--ppdb-dark);
            color: rgba(255,255,255,.6);
            font-size: .85rem;
        }

        /* ── Upload zone ── */
        .upload-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 1.25rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .upload-zone:hover, .upload-zone.dragover {
            border-color: var(--ppdb-primary);
            background: #eff6ff;
        }
        .upload-zone input[type="file"] { opacity: 0; position: absolute; width: 0; height: 0; }

        @media (max-width: 576px) {
            .ppdb-hero { padding: 3rem 0 2.5rem; }
            .ppdb-hero h1 { font-size: 1.8rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Navbar --}}
<nav class="navbar navbar-expand-lg ppdb-navbar py-2">
    <div class="container">
        <a class="navbar-brand" href="{{ route('ppdb.index') }}">
            <i class="bi bi-mortarboard-fill me-2"></i>Paskola <span>PPDB</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#ppdbNav">
            <span class="navbar-toggler-icon" style="filter:invert(1)"></span>
        </button>
        <div class="collapse navbar-collapse" id="ppdbNav">
            <ul class="navbar-nav ms-auto me-3 gap-1">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('ppdb.index') }}"><i class="bi bi-house me-1"></i>Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('ppdb.register.form') }}"><i class="bi bi-pencil-square me-1"></i>Daftar Sekarang</a>
                </li>
            </ul>
            <a href="{{ route('ppdb.cek-status.form') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-search me-1"></i>Cek Status
            </a>
        </div>
    </div>
</nav>

{{-- Flash messages --}}
@if(session('error'))
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif
@if(session('success'))
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif

@yield('content')

{{-- Footer --}}
<footer class="ppdb-footer py-4 mt-5">
    <div class="container text-center">
        <p class="mb-1">© {{ date('Y') }} <strong class="text-white">Paskola</strong> — Sistem Informasi Manajemen Sekolah</p>
        <p class="mb-0">Butuh bantuan? Hubungi panitia PPDB di sekolah kami.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
