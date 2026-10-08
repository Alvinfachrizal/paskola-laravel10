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

    @if($payment && $payment->status->value !== 'paid')
        {{-- PAYWALL: Form Pembayaran --}}
        <div class="ppdb-card card p-4 mb-4 text-center">
            <i class="bi bi-wallet2 text-primary mb-3" style="font-size: 3rem;"></i>
            <h4 class="fw-bold mb-2">Selesaikan Pembayaran Anda</h4>
            <p class="text-muted mb-4">
                Gelombang pendaftaran ini mewajibkan biaya formulir sebesar 
                <strong class="text-dark">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>. 
                Silakan transfer ke rekening berikut:
            </p>
            
            <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-4 mb-4">
                {{-- Info Rekening --}}
                <div class="bg-light p-4 rounded-4 text-start shadow-sm" style="min-width: 280px; border-left: 4px solid var(--ppdb-primary);">
                    <p class="mb-1 small text-muted"><i class="bi bi-bank me-2"></i>Bank Tujuan</p>
                    <p class="mb-2 fw-bold text-dark fs-6">{{ $settings['ppdb_bank_name'] ?? 'Bank BSI' }}</p>
                    
                    <p class="mb-1 small text-muted"><i class="bi bi-123 me-2"></i>No. Rekening</p>
                    <p class="mb-2 fw-bold fs-4 text-primary font-monospace">{{ $settings['ppdb_bank_account'] ?? '7123 4567 89' }}</p>
                    
                    <p class="mb-1 small text-muted"><i class="bi bi-person me-2"></i>Atas Nama</p>
                    <p class="mb-0 small fw-semibold">{{ $settings['ppdb_bank_owner'] ?? 'Yayasan Pendidikan Paskola' }}</p>
                </div>

                {{-- QRIS (Jika ada) --}}
                @if(!empty($settings['ppdb_qris_path']))
                <div class="text-center p-3 rounded-4 shadow-sm bg-white" style="border: 2px dashed #ccc; min-width: 280px;">
                    <p class="fw-bold mb-3 text-dark fs-6"><i class="bi bi-qr-code-scan me-2"></i>Atau Scan QRIS</p>
                    <img src="{{ Storage::url($settings['ppdb_qris_path']) }}" alt="QRIS Pembayaran" class="img-fluid rounded-3" style="max-width: 250px; object-fit: contain;">
                </div>
                @endif
            </div>

            @if(!$payment->proof_path)
                <form action="{{ route('ppdb.payment.store', $applicant->registration_code) }}" method="POST" enctype="multipart/form-data" class="text-start">
                    @csrf
                    <label class="form-label small fw-semibold">Upload Bukti Transfer <span class="text-danger">*</span></label>
                    <input type="file" name="payment_receipt" class="form-control" accept="image/*" required>
                    <small class="text-muted d-block mt-1 mb-3">Format: JPG, PNG. Maks: 2MB.</small>
                    <button type="submit" class="btn btn-primary w-100 rounded-3"><i class="bi bi-cloud-arrow-up me-2"></i>Kirim Bukti Pembayaran</button>
                </form>
            @elseif($payment->status->value === 'failed')
                <div class="alert alert-danger rounded-3 text-start mb-3">
                    <i class="bi bi-x-circle me-2"></i>
                    <strong>Pembayaran Ditolak!</strong> {{ $payment->notes }}
                </div>
                <form action="{{ route('ppdb.payment.store', $applicant->registration_code) }}" method="POST" enctype="multipart/form-data" class="text-start">
                    @csrf
                    <label class="form-label small fw-semibold">Upload Ulang Bukti Transfer <span class="text-danger">*</span></label>
                    <input type="file" name="payment_receipt" class="form-control" accept="image/*" required>
                    <small class="text-muted d-block mt-1 mb-3">Format: JPG, PNG. Maks: 2MB.</small>
                    <button type="submit" class="btn btn-danger w-100 rounded-3"><i class="bi bi-cloud-arrow-up me-2"></i>Kirim Ulang Bukti</button>
                </form>
            @else
                <div class="alert alert-info rounded-3 text-start">
                    <i class="bi bi-hourglass-split me-2"></i>
                    <strong>Bukti terkirim!</strong> Pembayaran Anda sedang diverifikasi oleh admin. Kami akan memprosesnya paling lambat 1x24 jam.
                </div>
            @endif
        </div>

    @elseif($needsCompleteData)
        {{-- FORM LENGKAPI DATA (Dokumen & Seragam) --}}
        <div class="ppdb-card card p-4 mb-4">
            <h4 class="fw-bold mb-3"><i class="bi bi-clipboard-check text-primary me-2"></i>Lengkapi Berkas Anda</h4>
            <p class="text-muted small">Pembayaran Anda telah diverifikasi! (Atau Anda mendaftar di gelombang gratis). Silakan lengkapi dokumen dan ukuran seragam untuk menyelesaikan pendaftaran.</p>
            
            <form action="{{ route('ppdb.complete-data.store', $applicant->registration_code) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <hr class="my-4">
                <h6 class="fw-bold mb-3">1. Pilih Ukuran Seragam</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small">Ukuran Seragam <span class="text-danger">*</span></label>
                        <select name="ukuran" class="form-select" required>
                            <option value="">- Pilih -</option>
                            <option value="S">S</option>
                            <option value="M">M</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                            <option value="XXL">XXL</option>
                        </select>
                    </div>
                    @if($applicant->gender === 'perempuan')
                        <div class="col-md-4">
                            <label class="form-label small">Memakai Kerudung? <span class="text-danger">*</span></label>
                            <select name="pakai_kerudung" class="form-select" required>
                                <option value="ya">Ya</option>
                                <option value="tidak">Tidak</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Jenis Bawahan <span class="text-danger">*</span></label>
                            <select name="jenis_bawahan" class="form-select" required>
                                <option value="rok">Rok</option>
                                <option value="celana">Celana</option>
                            </select>
                        </div>
                    @endif
                </div>

                <hr class="my-4">
                <h6 class="fw-bold mb-3">2. Upload Dokumen Syarat</h6>
                <div class="row g-3 mb-4">
                    @foreach(['ijazah' => 'Ijazah / SKL', 'kk' => 'Kartu Keluarga', 'akta_kelahiran' => 'Akta Kelahiran', 'pas_foto' => 'Pas Foto 3x4'] as $key => $label)
                    <div class="col-md-6">
                        <label class="form-label small">{{ $label }} <span class="text-danger">*</span></label>
                        <input type="file" name="dokumen[{{ $key }}]" class="form-control" accept=".jpg,.png,.pdf" required>
                    </div>
                    @endforeach
                </div>
                
                <button type="submit" class="btn btn-primary w-100 rounded-3"><i class="bi bi-save me-2"></i>Simpan Data & Selesaikan Pendaftaran</button>
            </form>
        </div>

    @else
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

        {{-- Tombol upload ulang jika ada dokumen yang ditolak --}}
        @if($applicant->status->value === 'need_revision' && $applicant->documents->filter(fn($d) => $d->status->value === 'invalid')->count() > 0)
        <div class="mt-3">
            <a href="{{ route('ppdb.reupload.form', $applicant->registration_code) }}"
               class="btn btn-warning fw-semibold w-100 rounded-3">
                <i class="bi bi-cloud-arrow-up me-2"></i>
                Upload Ulang Dokumen yang Ditolak
                <span class="badge bg-dark ms-1 rounded-pill">
                    {{ $applicant->documents->filter(fn($d) => $d->status->value === 'invalid')->count() }}
                </span>
            </a>
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
    @endif

</div>
@endsection
