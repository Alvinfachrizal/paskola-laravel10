@extends('layouts.app-bootstrap')
@section('title', 'Daftar Ujian')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Manajemen Ujian</h2>
        <p class="text-muted mb-0 small">Buat dan kelola ujian pilihan ganda untuk kelas Anda</p>
    </div>
    @can('create', App\Models\Exam::class)
    <a href="{{ route('exam.exams.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">
        <i class="bi bi-plus-circle me-1"></i> Buat Ujian Baru
    </a>
    @endcan
</div>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Filter Status --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
            <span class="small fw-semibold text-muted me-1">Filter:</span>
            @foreach([''=>'Semua', 'draft'=>'Draft', 'published'=>'Aktif', 'closed'=>'Selesai'] as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
                class="btn btn-sm rounded-pill {{ request('status', '') === $val ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
            </a>
            @endforeach
        </form>
    </div>
</div>

{{-- Daftar Ujian --}}
<div class="row g-3">
    @forelse($exams as $exam)
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 exam-card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge rounded-pill {{ $exam->status->badgeClass() }} px-2 py-1 small">
                                <i class="bi {{ $exam->status->icon() }} me-1"></i>{{ $exam->status->label() }}
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 small">
                                {{ $exam->subject->name ?? '—' }}
                            </span>
                        </div>

                        <h5 class="fw-bold mb-1">{{ $exam->title }}</h5>

                        <div class="d-flex flex-wrap gap-3 text-muted small mt-2">
                            <span><i class="bi bi-clock me-1"></i>{{ $exam->duration_minutes }} menit</span>
                            <span><i class="bi bi-calendar-event me-1"></i>
                                {{ $exam->start_at->format('d M Y, H:i') }} – {{ $exam->end_at->format('H:i') }}
                            </span>
                            <span><i class="bi bi-people me-1"></i>
                                {{ $exam->participants_count }} peserta
                            </span>
                            <span><i class="bi bi-collection me-1"></i>
                                {{ $exam->questions_count }} soal
                            </span>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2" style="min-width: 120px;">
                        <a href="{{ route('exam.exams.show', $exam) }}" class="btn btn-sm btn-outline-primary rounded-3">
                            <i class="bi bi-eye me-1"></i>Detail
                        </a>
                        @if($exam->status->value === 'draft')
                        <a href="{{ route('exam.exams.edit', $exam) }}" class="btn btn-sm btn-outline-secondary rounded-3">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        @endif
                        @if(in_array($exam->status->value, ['published', 'closed']))
                        <a href="{{ route('exam.exams.participants', $exam) }}" class="btn btn-sm btn-outline-success rounded-3">
                            <i class="bi bi-people me-1"></i>Peserta
                        </a>
                        @endif
                        @if(in_array($exam->status->value, ['selesai', 'closed', 'published']))
                        <a href="{{ route('exam.exams.results', $exam) }}" class="btn btn-sm btn-outline-info rounded-3">
                            <i class="bi bi-bar-chart me-1"></i>Hasil
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-journal-x display-4 d-block mb-3 opacity-25"></i>
                <p class="fw-semibold mb-1">Belum ada ujian</p>
                <p class="small mb-3">Mulai dengan membuat ujian baru.</p>
                @can('create', App\Models\Exam::class)
                <a href="{{ route('exam.exams.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">
                    <i class="bi bi-plus-circle me-1"></i> Buat Ujian Pertama
                </a>
                @endcan
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($exams->hasPages())
<div class="mt-3">{{ $exams->links('pagination::bootstrap-5') }}</div>
@endif

@endsection

@push('styles')
<style>
.exam-card { transition: transform 0.15s, box-shadow 0.15s; }
.exam-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important; }
</style>
@endpush
