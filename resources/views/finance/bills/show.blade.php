@extends('layouts.app-bootstrap')

@section('title', 'Detail Tagihan')

@section('header')
<div class="d-flex align-items-center gap-3">
    <a href="{{ route('finance.bills.index') }}" class="btn btn-outline-secondary rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="h4 mb-1 fw-bold">Detail Tagihan</h2>
        <p class="text-muted mb-0">{{ $bill->billType->name }} {{ $bill->periodLabel() ? '- ' . $bill->periodLabel() : '' }}</p>
    </div>
</div>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        {{-- Card Informasi Tagihan --}}
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Informasi Tagihan</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 150px;">Siswa</td>
                            <td class="fw-semibold">{{ $bill->student->name }} ({{ $bill->student->nisn }})</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenis Tagihan</td>
                            <td class="fw-semibold">{{ $bill->billType->name }}</td>
                        </tr>
                        @if($bill->periodLabel())
                        <tr>
                            <td class="text-muted">Periode</td>
                            <td class="fw-semibold">{{ $bill->periodLabel() }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Nominal</td>
                            <td class="fw-bold fs-5 text-primary">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jatuh Tempo</td>
                            <td>
                                @if($bill->due_date)
                                    <span class="{{ $bill->due_date->isPast() && !$bill->isPaid() ? 'text-danger fw-semibold' : '' }}">
                                        {{ $bill->due_date->translatedFormat('d F Y') }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                <span class="badge {{ $bill->status->badgeClass() }} px-3 py-2 rounded-pill">
                                    <i class="bi {{ $bill->status->icon() }} me-1"></i> {{ $bill->status->label() }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Jika status belum lunas & belum menunggu verifikasi, tampilkan form upload --}}
        @if($bill->status->value === 'belum_bayar' || $bill->status->value === 'terlambat')
            <div class="card shadow-sm border-0 rounded-4 border-top border-primary border-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-upload text-primary me-2"></i>Upload Bukti Pembayaran</h5>
                    <p class="text-muted small mb-4">
                        Silakan transfer sebesar <strong>Rp {{ number_format($bill->amount, 0, ',', '.') }}</strong> ke rekening sekolah, lalu upload foto/screenshot bukti transfer di bawah ini.
                    </p>

                    <form action="{{ route('finance.payments.store', $bill) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nominal Pembayaran (Rp)</label>
                            <input type="number" name="amount_paid" class="form-control rounded-3 @error('amount_paid') is-invalid @enderror" 
                                value="{{ old('amount_paid', $bill->amount) }}" required min="1">
                            @error('amount_paid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Upload Bukti Transfer</label>
                            <input type="file" name="proof_file" class="form-control rounded-3 @error('proof_file') is-invalid @enderror" 
                                accept="image/jpeg,image/png,image/jpg" required>
                            <div class="form-text">Format: JPG, JPEG, PNG. Maksimal 2MB.</div>
                            @error('proof_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 w-100">
                            <i class="bi bi-send-fill me-2"></i>Kirim Bukti Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        {{-- Riwayat Upload Pembayaran --}}
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Riwayat Pembayaran</h5>
            </div>
            <div class="card-body">
                @if($bill->payments->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-receipt fs-1 d-block mb-2 opacity-25"></i>
                        Belum ada riwayat upload bukti.
                    </div>
                @else
                    <div class="timeline mt-3">
                        @foreach($bill->payments->sortByDesc('created_at') as $payment)
                            <div class="d-flex mb-4">
                                <div class="me-3 mt-1">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white {{ $payment->status->badgeClass() }}" style="width: 32px; height: 32px;">
                                        @if($payment->status->value === 'pending')
                                            <i class="bi bi-hourglass"></i>
                                        @elseif($payment->status->value === 'verified')
                                            <i class="bi bi-check-lg"></i>
                                        @else
                                            <i class="bi bi-x-lg"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-grow-1 bg-light rounded-4 p-3 border">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-semibold">
                                            Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}
                                        </span>
                                        <span class="text-muted small">{{ $payment->created_at->format('d M Y H:i') }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="badge {{ $payment->status->badgeClass() }} rounded-pill">
                                            {{ $payment->status->label() }}
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <a href="{{ asset('storage/' . $payment->proof_file) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill">
                                            <i class="bi bi-image me-1"></i>Lihat Bukti
                                        </a>
                                    </div>
                                    @if($payment->status->value === 'rejected' && $payment->rejection_reason)
                                        <div class="alert alert-danger mb-0 p-2 small mt-2 rounded-3 border-danger">
                                            <strong>Alasan Ditolak:</strong><br>
                                            {{ $payment->rejection_reason }}
                                        </div>
                                    @endif
                                    @if($payment->status->value === 'verified' && $payment->verifier)
                                        <div class="text-muted small mt-2">
                                            <i class="bi bi-person-check me-1"></i>Diverifikasi oleh {{ $payment->verifier->name }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
