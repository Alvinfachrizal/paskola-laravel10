@extends('layouts.app-bootstrap')
@section('title', 'Hasil Ujian — ' . $exam->title)

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Hasil & Approval</h2>
        <p class="text-muted mb-0 small">{{ $exam->title }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('exam.exams.participants', $exam) }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i>Peserta
        </a>
        @can('approve', $exam)
        @if($participants->where('grade_status.value', 'menunggu_approve')->count() > 0)
        <form method="POST" action="{{ route('exam.exams.approve', $exam) }}"
            onsubmit="return confirm('Approve semua yang sudah selesai?')">
            @csrf
            <button type="submit" class="btn btn-success btn-sm rounded-3">
                <i class="bi bi-patch-check me-1"></i>Approve Semua
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

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Siswa</th>
                        <th class="text-center">Nilai</th>
                        <th class="text-center">Status Ujian</th>
                        <th class="text-center">Mulai – Selesai</th>
                        <th class="text-center">Approval</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $rank => $p)
                    <tr>
                        <td class="ps-4 text-muted small">{{ $rank + 1 }}</td>
                        <td>
                            <div class="fw-semibold small">{{ $p->student->name ?? '—' }}</div>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold fs-5 {{ $p->score >= 75 ? 'text-success' : ($p->score >= 60 ? 'text-warning' : 'text-danger') }}">
                                {{ $p->score !== null ? number_format($p->score, 1) : '—' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill {{ $p->status->badgeClass() }}">{{ $p->status->label() }}</span>
                        </td>
                        <td class="text-center small text-muted">
                            {{ $p->started_at?->format('H:i') ?? '—' }} – {{ $p->finished_at?->format('H:i') ?? '—' }}
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill {{ $p->grade_status->badgeClass() }}">
                                <i class="bi {{ $p->grade_status->icon() }} me-1"></i>{{ $p->grade_status->label() }}
                            </span>
                            @if($p->approved_at)
                            <div class="text-muted" style="font-size:0.7rem;">{{ $p->approved_at->format('d/m H:i') }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('exam.exams.participant-detail', [$exam, $p]) }}"
                                    class="btn btn-sm btn-outline-secondary rounded-2" title="Lihat detail jawaban">
                                    <i class="bi bi-list-ul"></i>
                                </a>
                                @can('approve', $exam)
                                @if($p->grade_status->value === 'menunggu_approve')
                                <form method="POST" action="{{ route('exam.exams.approve', $exam) }}">
                                    @csrf
                                    <input type="hidden" name="participant_id" value="{{ $p->id }}">
                                    <button type="submit" class="btn btn-sm btn-success rounded-2" title="Approve nilai ini">
                                        <i class="bi bi-patch-check"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada peserta yang menyelesaikan ujian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
