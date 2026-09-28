@extends('layouts.app-bootstrap')
@section('title', $exam->title)

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">{{ $exam->title }}</h2>
        <p class="text-muted mb-0 small">{{ $exam->subject->name ?? '—' }} • {{ $exam->teacher->name ?? '—' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('exam.exams.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        @can('update', $exam)
        @if($exam->status->value === 'draft')
        <a href="{{ route('exam.exams.edit', $exam) }}" class="btn btn-outline-primary btn-sm rounded-3">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form method="POST" action="{{ route('exam.exams.publish', $exam) }}"
            onsubmit="return confirm('Publish ujian ini? Kode peserta akan otomatis dibuat.')">
            @csrf
            <button type="submit" class="btn btn-success btn-sm rounded-3">
                <i class="bi bi-broadcast me-1"></i>Publish
            </button>
        </form>
        @endif
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

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    {{-- Info Ujian --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi Ujian</h6>
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Status</dt>
                    <dd class="col-7">
                        <span class="badge {{ $exam->status->badgeClass() }} rounded-pill">
                            {{ $exam->status->label() }}
                        </span>
                    </dd>
                    <dt class="col-5 text-muted">Mata Pelajaran</dt>
                    <dd class="col-7">{{ $exam->subject->name ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Durasi</dt>
                    <dd class="col-7">{{ $exam->duration_minutes }} menit</dd>
                    <dt class="col-5 text-muted">Mulai</dt>
                    <dd class="col-7">{{ $exam->start_at->format('d M Y, H:i') }}</dd>
                    <dt class="col-5 text-muted">Berakhir</dt>
                    <dd class="col-7">{{ $exam->end_at->format('d M Y, H:i') }}</dd>
                    <dt class="col-5 text-muted">Tampilkan nilai</dt>
                    <dd class="col-7">{{ $exam->show_score->label() }}</dd>
                    <dt class="col-5 text-muted">Kelas</dt>
                    <dd class="col-7">{{ $exam->classes->pluck('name')->join(', ') ?: '—' }}</dd>
                    <dt class="col-5 text-muted">Jumlah Soal</dt>
                    <dd class="col-7">{{ $exam->questions->count() }} soal</dd>
                    <dt class="col-5 text-muted">Total Poin</dt>
                    <dd class="col-7">{{ number_format($exam->totalPoints(), 0) }} poin</dd>
                </dl>
            </div>
        </div>

        @if($exam->status->value !== 'draft')
        <div class="d-grid gap-2">
            <a href="{{ route('exam.exams.participants', $exam) }}" class="btn btn-outline-primary rounded-3">
                <i class="bi bi-people me-2"></i>Lihat Daftar Peserta
            </a>
            <a href="{{ route('exam.exams.results', $exam) }}" class="btn btn-outline-success rounded-3">
                <i class="bi bi-bar-chart me-2"></i>Lihat Hasil & Approval
            </a>
        </div>
        @endif
    </div>

    {{-- Daftar Soal --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 px-4 rounded-top-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-collection me-2 text-success"></i>Soal dalam Ujian Ini</h6>
            </div>
            <div class="card-body p-0">
                @forelse($exam->questions->sortBy('pivot.position') as $idx => $question)
                <div class="border-bottom p-3 px-4">
                    <div class="d-flex gap-3 align-items-start">
                        <span class="badge bg-secondary-subtle text-secondary rounded-circle fw-bold" style="width:28px;height:28px;line-height:28px;text-align:center;font-size:0.8rem;">
                            {{ $idx + 1 }}
                        </span>
                        <div class="flex-grow-1">
                            <p class="mb-1 small fw-semibold">{{ Str::limit($question->question_text, 120) }}</p>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($question->options->sortBy('position') as $opt)
                                <span class="badge rounded-pill {{ $opt->is_correct ? 'bg-success' : 'bg-light text-secondary border' }}" style="font-size:0.75rem;">
                                    {{ $opt->positionLabel() }}. {{ Str::limit($opt->option_text ?? '[Gambar]', 30) }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        <span class="text-muted small fw-semibold text-nowrap">
                            {{ number_format($question->pivot->points, 0) }} poin
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted small">Belum ada soal ditambahkan.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
