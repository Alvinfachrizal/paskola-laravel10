@extends('ppdb.layout')
@section('title', 'Status Pendaftaran — ' . $applicant->registration_code)

@section('content')
<div class="container py-5" style="max-width: 760px;">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('ppdb.cek-status.form') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="fw-bold mb-0" style="font-size:1.6rem">Status Pendaftaran</h1>
            <p class="text-muted mb-0 small">Kode: <strong>{{ $applicant->registration_code }}</strong></p>
        </div>
    </div>

    {{-- Status Utama --}}
    @php
        $status     = $applicant->status; // PpdbApplicantStatus enum
        $statusVal  = $status->value;
    @endphp
    <div class="ppdb-card card p-4 mb-4"
         style="border-left: 5px solid var(--ppdb-primary);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <p class="text-muted small mb-1 fw-semibold text-uppercase" style="letter-spacing:1px">Status Saat Ini</p>
                <span class="badge {{ $status->badgeClass() }} fs-6 px-3 py-2 rounded-pill">
                    {{ $status->label() }}
                </span>
            </div>
            <div class="text-end">
                <p class="text-muted small mb-1">Gelombang</p>
                <p class="fw-bold mb-0">{{ $applicant->wave->name ?? '-' }}</p>
            </div>
        </div>

        @if($applicant->admin_notes)
        <div class="alert alert-warning rounded-3 mt-3 mb-0 py-2">
            <i class="bi bi-chat-left-text me-2"></i>
            <strong>Catatan Panitia:</strong> {{ $applicant->admin_notes }}
        </div>
        @endif
    </div>

    {{-- Data Pendaftar --}}
    <div class="ppdb-card card p-4 mb-4">
        <p class="form-section-title"><i class="bi bi-person me-2"></i>Data Pendaftar</p>
        <div class="row g-2 small">
            <div class="col-6 col-md-4">
                <p class="text-muted mb-0">Nama Lengkap</p>
                <p class="fw-semibold mb-0">{{ $applicant->full_name }}</p>
            </div>
            <div class="col-6 col-md-4">
                <p class="text-muted mb-0">NISN</p>
                <p class="fw-semibold mb-0">{{ $applicant->nisn ?? '—' }}</p>
            </div>
            <div class="col-6 col-md-4">
                <p class="text-muted mb-0">Jenis Kelamin</p>
                <p class="fw-semibold mb-0">{{ ucfirst($applicant->gender) }}</p>
            </div>
            <div class="col-6 col-md-4">
                <p class="text-muted mb-0">Tanggal Lahir</p>
                <p class="fw-semibold mb-0">{{ $applicant->birth_date->format('d M Y') }}</p>
            </div>
            <div class="col-6 col-md-4">
                <p class="text-muted mb-0">Orang Tua / Wali</p>
                <p class="fw-semibold mb-0">{{ $applicant->parent_name }}</p>
            </div>
            <div class="col-6 col-md-4">
                <p class="text-muted mb-0">No. HP Orang Tua</p>
                <p class="fw-semibold mb-0">{{ $applicant->parent_phone }}</p>
            </div>
        </div>
    </div>

    {{-- Status Dokumen --}}
    <div class="ppdb-card card p-4 mb-4">
        <p class="form-section-title"><i class="bi bi-file-earmark-check me-2"></i>Status Verifikasi Dokumen</p>
        @if($applicant->documents->isEmpty())
            <p class="text-muted small mb-0">Belum ada dokumen yang diupload.</p>
        @else
            <div class="d-flex flex-column gap-2">
                @foreach($applicant->documents as $doc)
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3"
                     style="background:#f8fafc;border:1px solid #e2e8f0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark text-primary"></i>
                        <span class="fw-semibold small">{{ $doc->docTypeLabel() }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($doc->status->value === 'invalid' && $doc->rejection_notes)
                            <span class="text-danger small"><i class="bi bi-chat-left me-1"></i>{{ $doc->rejection_notes }}</span>
                        @endif
                        <span class="badge {{ $doc->status->badgeClass() }} rounded-pill small">
                            {{ $doc->status->label() }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Seragam --}}
    @if($applicant->uniformOrder)
    <div class="ppdb-card card p-4 mb-4">
        <p class="form-section-title"><i class="bi bi-bag me-2"></i>Data Seragam</p>
        <div class="d-flex flex-wrap gap-3 small">
            <div class="p-3 rounded-3 text-center" style="background:#eff6ff;min-width:100px">
                <p class="text-muted mb-0">Ukuran</p>
                <p class="fw-bold mb-0 fs-5">{{ $applicant->uniformOrder->ukuran }}</p>
            </div>
            @if($applicant->uniformOrder->gender === 'perempuan')
            <div class="p-3 rounded-3 text-center" style="background:#f0fdf4;min-width:100px">
                <p class="text-muted mb-0">Kerudung</p>
                <p class="fw-bold mb-0">{{ $applicant->uniformOrder->pakai_kerudung ? 'Ya' : 'Tidak' }}</p>
            </div>
            <div class="p-3 rounded-3 text-center" style="background:#fefce8;min-width:100px">
                <p class="text-muted mb-0">Bawahan</p>
                <p class="fw-bold mb-0">{{ ucfirst($applicant->uniformOrder->jenis_bawahan) }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Nilai Seleksi (tampil hanya jika ada) --}}
    @if($applicant->selectionScores->isNotEmpty())
    <div class="ppdb-card card p-4 mb-4">
        <p class="form-section-title"><i class="bi bi-bar-chart me-2"></i>Hasil Seleksi</p>
        <div class="row g-2">
            @foreach($applicant->selectionScores as $score)
            <div class="col-4 col-md-3">
                <div class="p-3 rounded-3 text-center" style="background:#f8fafc;border:1px solid #e2e8f0">
                    <p class="text-muted small mb-0">{{ \App\Models\PpdbSelectionScore::$scoreTypes[$score->score_type] ?? $score->score_type }}</p>
                    <p class="fw-bold mb-0 fs-5">{{ number_format($score->score_value, 1) }}</p>
                </div>
            </div>
            @endforeach
            <div class="col-4 col-md-3">
                <div class="p-3 rounded-3 text-center" style="background:#eff6ff;border:1px solid #bfdbfe">
                    <p class="text-primary small fw-semibold mb-0">Rata-rata</p>
                    <p class="fw-bold mb-0 fs-5 text-primary">{{ number_format($applicant->averageScore(), 1) }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Pembayaran (tampil hanya jika ada) --}}
    @if($applicant->payments->isNotEmpty())
    <div class="ppdb-card card p-4 mb-4">
        <p class="form-section-title"><i class="bi bi-cash-coin me-2"></i>Status Pembayaran</p>
        @foreach($applicant->payments as $payment)
        <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-2"
             style="background:#f8fafc;border:1px solid #e2e8f0">
            <div>
                <p class="fw-semibold small mb-0">
                    {{ \App\Models\PpdbPayment::$paymentTypes[$payment->payment_type] ?? $payment->payment_type }}
                </p>
                <p class="text-muted small mb-0">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
            </div>
            <span class="badge {{ $payment->status->badgeClass() }} rounded-pill">
                {{ $payment->status->label() }}
            </span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Daftar Ulang --}}
    @if($applicant->reregistration)
    <div class="ppdb-card card p-4 mb-4">
        <p class="form-section-title"><i class="bi bi-person-check me-2"></i>Daftar Ulang</p>
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <p class="fw-semibold mb-0">Status Daftar Ulang</p>
                @if($applicant->reregistration->completed_at)
                <p class="text-muted small mb-0">
                    Selesai pada: {{ $applicant->reregistration->completed_at->format('d M Y, H:i') }}
                </p>
                @endif
            </div>
            <span class="badge {{ $applicant->reregistration->status->badgeClass() }} rounded-pill fs-6 px-3">
                {{ $applicant->reregistration->status->label() }}
            </span>
        </div>
        @if($applicant->reregistration->status->value === 'completed')
        <div class="alert alert-success rounded-3 mt-3 mb-0">
            <i class="bi bi-check-circle-fill me-2"></i>
            Selamat! Anda resmi terdaftar sebagai siswa. Data Anda sudah dimasukkan ke sistem sekolah.
        </div>
        @endif
    </div>
    @endif

    <div class="text-center">
        <a href="{{ route('ppdb.cek-status.form') }}" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-arrow-left me-2"></i>Cek Status Lain
        </a>
    </div>
</div>
@endsection
