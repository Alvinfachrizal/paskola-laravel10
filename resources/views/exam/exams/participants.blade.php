@extends('layouts.app-bootstrap')
@section('title', 'Peserta Ujian — ' . $exam->title)

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Daftar Peserta</h2>
        <p class="text-muted mb-0 small">{{ $exam->title }} • {{ $exam->subject->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('exam.exams.show', $exam) }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        {{-- Sinkronkan Peserta --}}
        @can('syncParticipants', $exam)
        <form method="POST" action="{{ route('exam.exams.sync-participants', $exam) }}"
            onsubmit="return confirm('Tambahkan siswa baru yang belum terdaftar sebagai peserta?')">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm rounded-3">
                <i class="bi bi-arrow-repeat me-1"></i>Sinkronkan Peserta
            </button>
        </form>
        @endcan

        {{-- Tombol Cetak Kode --}}
        <a href="#" onclick="window.print()" class="btn btn-outline-dark btn-sm rounded-3">
            <i class="bi bi-printer me-1"></i>Cetak Kode
        </a>

        {{-- Buka Susulan --}}
        @can('openMakeup', $exam)
        <button type="button" class="btn btn-warning btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#makeupModal">
            <i class="bi bi-clock-history me-1"></i>Buka Susulan
        </button>
        @endcan
    </div>
</div>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Statistik Singkat --}}
<div class="row g-3 mb-4">
    @php
    $statuses = $participants->groupBy(fn($p) => $p->status->value);
    @endphp
    @foreach(['belum_mulai'=>['label'=>'Belum Mulai','icon'=>'bi-clock','color'=>'secondary'], 'mengerjakan'=>['label'=>'Sedang','icon'=>'bi-pencil','color'=>'warning'], 'selesai'=>['label'=>'Selesai','icon'=>'bi-check-circle','color'=>'success'], 'waktu_habis'=>['label'=>'Waktu Habis','icon'=>'bi-alarm','color'=>'danger']] as $s => $info)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3">
            <div class="text-{{ $info['color'] }} fs-4 mb-1"><i class="bi {{ $info['icon'] }}"></i></div>
            <div class="fw-bold fs-5">{{ $statuses->get($s, collect())->count() }}</div>
            <div class="text-muted small">{{ $info['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Tabel Peserta --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" id="participantsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Siswa</th>
                        <th class="text-center">Kode Ujian</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Mulai</th>
                        <th class="text-center">Selesai</th>
                        <th class="text-center">Nilai</th>
                        <th class="text-center">Susulan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($participants as $p)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold small">{{ $p->student->name ?? '—' }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $p->student->nisn ?? '' }}</div>
                        </td>
                        <td class="text-center">
                            <code class="bg-light border rounded px-2 py-1 fw-bold letter-spacing-1">{{ $p->code }}</code>
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill {{ $p->status->badgeClass() }}">
                                {{ $p->status->label() }}
                            </span>
                        </td>
                        <td class="text-center small text-muted">
                            {{ $p->started_at ? $p->started_at->format('H:i') : '—' }}
                        </td>
                        <td class="text-center small text-muted">
                            {{ $p->finished_at ? $p->finished_at->format('H:i') : '—' }}
                        </td>
                        <td class="text-center fw-bold">
                            {{ $p->score !== null ? number_format($p->score, 1) : '—' }}
                        </td>
                        <td class="text-center small text-muted">
                            @if($p->allowed_from)
                            <span class="text-primary" title="{{ $p->allowed_from->format('d M H:i') }} – {{ $p->allowed_until?->format('H:i') }}">
                                <i class="bi bi-clock-history me-1"></i>{{ $p->allowed_from->format('d/m H:i') }}
                            </span>
                            @else
                            —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if($participants->hasPages())
    <div class="card-footer bg-white border-0 py-3 px-4 rounded-bottom-4">
        {{ $participants->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Modal Susulan --}}
@can('openMakeup', $exam)
<div class="modal fade" id="makeupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-clock-history me-2 text-warning"></i>Buka Ujian Susulan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('exam.exams.makeup', $exam) }}">
            @csrf
            <div class="modal-body px-4">
                <p class="text-muted small mb-3">Hanya peserta yang <strong>belum pernah memulai</strong> (status: Belum Mulai) yang dapat diberi jadwal susulan.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Jendela Susulan Mulai <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="allowed_from" class="form-control rounded-3" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Jendela Susulan Selesai <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="allowed_until" class="form-control rounded-3" required>
                </div>
                <p class="text-muted small">Siswa yang belum mulai di bawah ini akan dibuka jadwal susulannya (kosongkan untuk semua):</p>
                <div style="max-height:200px;overflow-y:auto;" class="border rounded-3 p-2">
                    @foreach($participants->where('status.value', 'belum_mulai') as $p)
                    <div class="form-check">
                        <input type="checkbox" name="participant_ids[]" value="{{ $p->id }}"
                            class="form-check-input" id="mp_{{ $p->id }}" checked>
                        <label class="form-check-label small" for="mp_{{ $p->id }}">
                            {{ $p->student->name ?? $p->id }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning rounded-3 fw-semibold">
                    <i class="bi bi-clock-history me-1"></i>Buka Susulan
                </button>
            </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection

@push('styles')
<style>
.letter-spacing-1 { letter-spacing: 2px; }
@media print {
    .btn, nav, .sidebar, header, .card-footer, form { display: none !important; }
    #participantsTable th, #participantsTable td { font-size: 11px; }
}
</style>
@endpush
