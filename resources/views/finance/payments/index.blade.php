@extends('layouts.app-bootstrap')

@section('title', 'Verifikasi Pembayaran')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-1 fw-bold">
            <i class="bi bi-cash-coin text-warning me-2"></i>Verifikasi Pembayaran
        </h2>
        <p class="text-muted mb-0">Periksa dan validasi bukti transfer yang diupload oleh siswa</p>
    </div>
</div>
@endsection

@section('content')

{{-- Filter Status --}}
<div class="mb-4 d-flex gap-2">
    <a href="{{ route('finance.payments.index', ['status' => 'pending']) }}" 
       class="btn {{ $status === 'pending' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-4">
        Menunggu Verifikasi 
        @php $pendingCount = \App\Models\Payment::where('status', 'pending')->count(); @endphp
        @if($pendingCount > 0)
            <span class="badge bg-white text-primary ms-1 rounded-circle">{{ $pendingCount }}</span>
        @endif
    </a>
    <a href="{{ route('finance.payments.index', ['status' => 'verified']) }}" 
       class="btn {{ $status === 'verified' ? 'btn-success' : 'btn-outline-success' }} rounded-pill px-4">
        Terverifikasi
    </a>
    <a href="{{ route('finance.payments.index', ['status' => 'rejected']) }}" 
       class="btn {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }} rounded-pill px-4">
        Ditolak
    </a>
    <a href="{{ route('finance.payments.index', ['status' => 'all']) }}" 
       class="btn {{ $status === 'all' ? 'btn-secondary' : 'btn-outline-secondary' }} rounded-pill px-4">
        Semua Data
    </a>
</div>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3 py-3">Siswa</th>
                        <th class="py-3">Tagihan</th>
                        <th class="py-3">Nominal Bayar</th>
                        <th class="py-3 text-center">Bukti Transfer</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-center">Aksi / Verifikator</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $payment->studentBill->student->name }}</div>
                                <div class="text-muted small">NISN: {{ $payment->studentBill->student->nisn }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $payment->studentBill->billType->name }}</div>
                                <div class="text-muted small">
                                    Target: Rp {{ number_format($payment->studentBill->amount, 0, ',', '.') }}
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold {{ $payment->amount_paid < $payment->studentBill->amount ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#proofModal-{{ $payment->id }}">
                                    <i class="bi bi-image me-1"></i>Lihat
                                </button>

                                {{-- Modal Bukti Transfer --}}
                                <div class="modal fade text-start" id="proofModal-{{ $payment->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content rounded-4 border-0 shadow-lg">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold">Bukti Transfer</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center p-4">
                                                <img src="{{ asset('storage/' . $payment->proof_file) }}" class="img-fluid rounded-3 shadow-sm" alt="Bukti Transfer">
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $payment->status->badgeClass() }} rounded-pill px-3 py-2">
                                    {{ $payment->status->label() }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($payment->status->value === 'pending')
                                    <div class="d-flex gap-2 justify-content-center">
                                        {{-- Approve --}}
                                        <form action="{{ route('finance.payments.verify', $payment) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-success rounded-3" title="Setujui Pembayaran" onclick="return confirm('Verifikasi pembayaran ini sebagai valid?')">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>

                                        {{-- Reject Modal Trigger --}}
                                        <button type="button" class="btn btn-sm btn-danger rounded-3" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $payment->id }}" title="Tolak Pembayaran">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                    {{-- Modal Reject --}}
                                    <div class="modal fade" id="rejectModal-{{ $payment->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content rounded-4 border-0 shadow-lg text-start">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-danger">Tolak Pembayaran</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('finance.payments.verify', $payment) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="reject">
                                                        <p class="text-muted small mb-3">Siswa akan diminta untuk mengupload ulang bukti transfer yang benar.</p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                                                            <textarea name="rejection_reason" class="form-control rounded-3" rows="3" required placeholder="Contoh: Bukti transfer buram, atau nominal tidak sesuai"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4">Tolak Pembayaran</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-muted small">
                                        <div class="fw-semibold">{{ $payment->verifier->name ?? 'Sistem' }}</div>
                                        <div>{{ $payment->verified_at?->format('d/m/Y H:i') }}</div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle display-4 d-block mb-3 opacity-25"></i>
                                Tidak ada data pembayaran untuk status ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
