@extends('layouts.app-bootstrap')
@section('title', 'Ujian Saya')

@section('header')
<div>
    <h2 class="h3 mb-1 fw-bold">Ujian Saya</h2>
    <p class="text-muted mb-0 small">Daftar ujian yang tersedia untuk kelas Anda</p>
</div>
@endsection

@section('content')

{{-- Tombol Masukkan Kode --}}
<div class="card border-0 rounded-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body p-4 text-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1">Punya kode ujian?</h5>
            <p class="mb-0 opacity-75 small">Masukkan kode yang diberikan guru untuk mulai mengerjakan</p>
        </div>
        <a href="{{ route('exam.session.enter') }}" class="btn btn-white rounded-3 fw-semibold px-4"
            style="background:white;color:#764ba2;">
            <i class="bi bi-key me-2"></i>Masukkan Kode
        </a>
    </div>
</div>

{{-- Daftar Ujian --}}
@forelse($exams as $exam)
@php $participant = $participants->get($exam->id); @endphp
<div class="card border-0 shadow-sm rounded-4 mb-3 exam-card">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge rounded-pill {{ $exam->status->badgeClass() }} px-2 py-1 small">
                        {{ $exam->status->label() }}
                    </span>
                    @if($participant)
                    <span class="badge rounded-pill {{ $participant->status->badgeClass() }} px-2 py-1 small">
                        {{ $participant->status->label() }}
                    </span>
                    @endif
                </div>
                <h5 class="fw-bold mb-1">{{ $exam->title }}</h5>
                <p class="text-muted small mb-2">{{ $exam->subject->name }} • {{ $exam->teacher->name }}</p>
                <div class="d-flex flex-wrap gap-3 text-muted small">
                    <span><i class="bi bi-calendar me-1"></i>{{ $exam->start_at->format('d M Y, H:i') }}</span>
                    <span><i class="bi bi-clock me-1"></i>{{ $exam->duration_minutes }} menit</span>
                    <span><i class="bi bi-collection me-1"></i>{{ $exam->questions()->count() }} soal</span>
                </div>
            </div>

            {{-- Tombol aksi berdasarkan status --}}
            <div>
                @if($participant)
                    @if($participant->status->value === 'mengerjakan')
                    <a href="{{ route('exam.session.show', $participant) }}"
                        class="btn btn-warning rounded-3 fw-semibold">
                        <i class="bi bi-play-fill me-1"></i>Lanjutkan
                    </a>
                    @elseif($participant->status->isFinished())
                    <a href="{{ route('exam.session.result', $participant) }}"
                        class="btn btn-outline-success rounded-3">
                        <i class="bi bi-bar-chart me-1"></i>Lihat Hasil
                    </a>
                    @else
                    <a href="{{ route('exam.session.enter') }}"
                        class="btn btn-primary rounded-3 fw-semibold">
                        <i class="bi bi-key me-1"></i>Masuk Ujian
                    </a>
                    @endif
                @else
                <span class="text-muted small">Tidak terdaftar</span>
                @endif
            </div>
        </div>
    </div>
</div>
@empty
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-journal-x display-4 d-block mb-3 opacity-25"></i>
        <p class="fw-semibold mb-0">Belum ada ujian untuk kelas Anda</p>
    </div>
</div>
@endforelse

@endsection

@push('styles')
<style>
.exam-card { transition: transform 0.15s; }
.exam-card:hover { transform: translateY(-2px); }
</style>
@endpush
