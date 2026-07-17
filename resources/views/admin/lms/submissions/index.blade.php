@extends('layouts.app-bootstrap')
@section('title', Auth::user()->hasRole('Siswa') ? 'Tugas Saya' : 'Pengumpulan Tugas')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">
            @if(Auth::user()->hasRole('Siswa'))
                <i class="bi bi-cloud-upload me-2 text-primary"></i>Tugas Saya
            @else
                <i class="bi bi-cloud-arrow-up me-2 text-primary"></i>Pengumpulan Tugas Siswa
            @endif
        </h2>
        <p class="text-muted mb-0 small">
            @if(Auth::user()->hasRole('Siswa'))
                Daftar tugas yang sudah Anda kumpulkan
            @else
                Semua submission dari siswa untuk tugas Anda
            @endif
        </p>
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

{{-- Filter --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1">Filter Tugas</label>
                <select name="assignment_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                    <option value="">Semua Tugas</option>
                    @foreach($assignments as $a)
                    <option value="{{ $a->id }}" {{ request('assignment_id') == $a->id ? 'selected' : '' }}>
                        {{ $a->title }}
                    </option>
                    @endforeach
                </select>
            </div>
            @if(request('assignment_id'))
            <div class="col-auto">
                <a href="{{ route('admin.lms-submissions.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">
                    Reset
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
        <h6 class="fw-bold mb-0">
            <i class="bi bi-list-check me-2"></i>
            Daftar Submission ({{ $submissions->total() }})
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:.875rem">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Tugas</th>
                        @if(!Auth::user()->hasRole('Siswa'))
                        <th>Siswa</th>
                        @endif
                        <th class="text-center">Dikumpulkan</th>
                        <th class="text-center">Nilai</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $sub)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold">{{ $sub->assignment?->title ?? '—' }}</div>
                            <div class="text-muted small">{{ $sub->assignment?->subject?->name ?? '' }}</div>
                        </td>
                        @if(!Auth::user()->hasRole('Siswa'))
                        <td>{{ $sub->student?->name ?? '—' }}</td>
                        @endif
                        <td class="text-center text-muted small">
                            {{ $sub->submitted_at ? \Carbon\Carbon::parse($sub->submitted_at)->format('d M Y H:i') : '—' }}
                        </td>
                        <td class="text-center">
                            @if($sub->score !== null)
                                <span class="badge rounded-pill px-3
                                    {{ $sub->score >= 75 ? 'bg-success' : ($sub->score >= 60 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ $sub->score }}
                                </span>
                            @else
                                <span class="text-muted">Belum dinilai</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($sub->score !== null)
                                <span class="badge bg-success-subtle text-success border border-success rounded-pill small">Sudah Dinilai</span>
                            @elseif($sub->submitted_at)
                                <span class="badge bg-info-subtle text-info border border-info rounded-pill small">Terkumpul</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill small">Draft</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.lms-submissions.show', $sub->id) }}"
                               class="btn btn-sm btn-outline-primary rounded-3 px-3" style="font-size:.78rem">
                                <i class="bi bi-eye me-1"></i>Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Belum ada submission.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($submissions->hasPages())
        <div class="px-4 pb-4 pt-3 d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Menampilkan {{ $submissions->firstItem() }} – {{ $submissions->lastItem() }} dari {{ $submissions->total() }}
            </div>
            {{ $submissions->links() }}
        </div>
        @endif
    </div>
</div>

@endsection
