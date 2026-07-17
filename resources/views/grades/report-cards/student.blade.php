@extends('layouts.app-bootstrap')
@section('title', 'Rapor Saya')

@section('content')
<div class="container py-4" style="max-width:800px">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-0 h3">Rapor Nilai</h1>
            <p class="text-muted mb-0 small">Nilai akhir yang sudah dipublish oleh sekolah</p>
        </div>
    </div>

    {{-- Pilih semester --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold mb-1">Pilih Semester</label>
                        <select name="semester_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                            @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $semesterId == $sem->id ? 'selected' : '' }}>
                                {{ $sem->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($reportCards->isEmpty())
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
        <i class="bi bi-journal-x fs-1 mb-3"></i>
        <p class="fw-semibold mb-1">Rapor belum tersedia</p>
        <p class="small">Rapor semester ini belum dipublish oleh sekolah.</p>
    </div>
    @else
    {{-- Ringkasan --}}
    @php
        $avg = $reportCards->avg('final_score');
        $passing = $reportCards->filter(fn($r) => $r->final_score >= 75)->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3">
                <div class="fw-bold fs-3 text-primary">{{ $reportCards->count() }}</div>
                <div class="text-muted small">Mata Pelajaran</div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3">
                <div class="fw-bold fs-3 {{ $avg >= 75 ? 'text-success' : 'text-warning' }}">
                    {{ number_format($avg, 1) }}
                </div>
                <div class="text-muted small">Rata-rata</div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3">
                <div class="fw-bold fs-3 text-success">{{ $passing }}</div>
                <div class="text-muted small">Lulus (≥75)</div>
            </div>
        </div>
    </div>

    {{-- Tabel rapor --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" style="font-size:.875rem">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Mata Pelajaran</th>
                            <th class="text-center">Nilai Akhir</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center pe-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportCards as $rc)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $rc->subject?->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge fs-6 px-3 rounded-pill
                                    {{ $rc->final_score >= 75 ? 'bg-success' : ($rc->final_score >= 60 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ number_format($rc->final_score, 1) }}
                                </span>
                            </td>
                            <td class="text-center fw-bold fs-5">{{ $rc->scoreToGrade() }}</td>
                            <td class="text-center pe-4 text-muted small">
                                {{ $rc->final_score >= 75 ? '✅ Lulus' : '❌ Perlu Perbaikan' }}
                            </td>
                        </tr>
                        @if($rc->description)
                        <tr style="background:#f8fafc">
                            <td colspan="4" class="ps-4 pe-4 py-2 text-muted small fst-italic">
                                💬 {{ $rc->description }}
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td class="ps-4 py-3 fw-bold">Rata-rata</td>
                            <td class="text-center fw-bold">{{ number_format($avg, 1) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
