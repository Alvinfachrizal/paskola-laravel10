@extends('layouts.app-bootstrap')
@section('title', 'Rapor Siswa')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Manajemen Rapor</h2>
        <p class="text-muted mb-0 small">Verifikasi dan publish rapor siswa</p>
    </div>
    @can('publish', \App\Models\ReportCard::class)
    @if($classId && $semesterId)
    <form action="{{ route('grades.report-cards.publish') }}" method="POST">
        @csrf
        <input type="hidden" name="class_id" value="{{ $classId }}">
        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
        <button type="submit" class="btn btn-success btn-sm rounded-3"
                onclick="return confirm('Publish semua rapor terverifikasi untuk kelas ini?')">
            <i class="bi bi-send-check me-1"></i>Publish Semua Terverifikasi
        </button>
    </form>
    @endif
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

{{-- Filter --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Semester</label>
                <select name="semester_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                    @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $semesterId == $sem->id ? 'selected' : '' }}>{{ $sem->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Kelas</label>
                <select name="class_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Filter Status</label>
                <select name="status" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    @foreach($statusOptions as $val => $label)
                    <option value="{{ $val }}" {{ $statusFilter == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

{{-- Tabel Rapor --}}
@if($students->isNotEmpty())
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
        <h6 class="fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>Rekap Nilai & Status Rapor</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:.85rem">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3" style="min-width:160px">Siswa</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Nilai Akhir</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Status Rapor</th>
                        <th class="text-center pe-4">Aksi Wali Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportCards as $studentId => $cards)
                    @php $student = $students->firstWhere('id', $studentId); @endphp
                    @foreach($cards as $i => $rc)
                    <tr>
                        @if($i === 0)
                        <td class="ps-4 fw-semibold" rowspan="{{ $cards->count() }}">
                            {{ $student?->user?->name ?? '—' }}
                        </td>
                        @endif
                        <td>{{ $rc->subject?->name ?? '—' }}</td>
                        <td class="text-center">
                            @if($rc->final_score !== null)
                            <span class="badge rounded-pill px-3
                                {{ $rc->final_score >= 75 ? 'bg-success' : ($rc->final_score >= 60 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                {{ number_format($rc->final_score, 1) }}
                            </span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center fw-bold">{{ $rc->scoreToGrade() }}</td>
                        <td class="text-center">
                            <span class="badge {{ $rc->status->badgeClass() }} rounded-pill small">
                                {{ $rc->status->label() }}
                            </span>
                            @if($rc->status->value === 'terverifikasi' && $rc->verifiedBy)
                            <div class="text-muted" style="font-size:.7rem">oleh {{ $rc->verifiedBy->name }}</div>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            @can('updateStatus', $rc)
                            <div class="d-flex gap-1 justify-content-center">
                                @if($rc->status->value === 'draft' || $rc->status->value === 'perlu_verifikasi')
                                <form action="{{ route('grades.report-cards.status', $rc) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="terverifikasi">
                                    <button type="submit" class="btn btn-success btn-sm rounded-3 px-2"
                                            style="font-size:.75rem">
                                        <i class="bi bi-check-circle me-1"></i>Verifikasi
                                    </button>
                                </form>
                                @endif
                                @if($rc->status->value === 'terverifikasi')
                                <form action="{{ route('grades.report-cards.status', $rc) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="perlu_verifikasi">
                                    <button type="submit" class="btn btn-warning btn-sm rounded-3 px-2"
                                            style="font-size:.75rem"
                                            onclick="return confirm('Kembalikan ke guru mapel untuk direvisi?')">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Revisi
                                    </button>
                                </form>
                                @endif
                            </div>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                            Belum ada data rapor untuk filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
    <i class="bi bi-filter-circle fs-2 mb-2"></i>
    <p>Pilih kelas dan semester untuk melihat rekap rapor.</p>
</div>
@endif

@endsection
