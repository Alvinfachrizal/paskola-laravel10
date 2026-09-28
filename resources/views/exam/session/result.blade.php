@extends('layouts.app-bootstrap')
@section('title', 'Hasil Ujian — ' . $exam->title)

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="col-12 col-md-8 col-lg-6">

        @if(session('info'))
        <div class="alert alert-info rounded-3 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success rounded-3 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
        @endif

        <div class="card border-0 shadow-lg rounded-4 text-center p-5">
            <div class="mb-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                    style="width:80px;height:80px;background:#f0fdf4;">
                    <i class="bi bi-check-lg text-success" style="font-size:2.5rem;"></i>
                </div>
                <h3 class="fw-bold mb-1">Ujian Selesai</h3>
                <p class="text-muted">{{ $exam->title }}</p>
            </div>

            <hr class="text-muted opacity-25">

            <div class="row g-4 my-3 text-start">
                <div class="col-6">
                    <p class="text-muted small mb-1">Status</p>
                    <p class="fw-semibold mb-0"><span class="badge rounded-pill {{ $participant->status->badgeClass() }}">{{ $participant->status->label() }}</span></p>
                </div>
                <div class="col-6">
                    <p class="text-muted small mb-1">Diselesaikan pada</p>
                    <p class="fw-semibold mb-0">{{ $participant->finished_at?->format('d M Y, H:i') ?? '—' }}</p>
                </div>
                
                @if($canSeeScore)
                <div class="col-12 mt-4 text-center">
                    <p class="text-muted small mb-2 fw-semibold text-uppercase letter-spacing-1">Nilai Anda</p>
                    <div class="display-1 fw-bold {{ $participant->score >= 75 ? 'text-success' : ($participant->score >= 60 ? 'text-warning' : 'text-danger') }}">
                        {{ $participant->score !== null ? number_format($participant->score, 1) : '—' }}
                    </div>
                </div>
                @else
                <div class="col-12 mt-4">
                    <div class="alert alert-secondary rounded-3 text-center border-0 bg-light p-3 mb-0">
                        <i class="bi bi-lock me-2 text-muted"></i>
                        <span class="small text-muted">
                            @if($exam->show_score->value === 'after_approve' && $participant->grade_status->value !== 'approved')
                            Nilai akan ditampilkan setelah disetujui oleh guru.
                            @else
                            Nilai tidak ditampilkan untuk ujian ini.
                            @endif
                        </span>
                    </div>
                </div>
                @endif
            </div>

            <div class="mt-5">
                <a href="{{ route('exam.session.my-exams') }}" class="btn btn-outline-secondary rounded-3 fw-semibold px-4">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Ujian
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.letter-spacing-1 { letter-spacing: 2px; }
</style>
@endpush
