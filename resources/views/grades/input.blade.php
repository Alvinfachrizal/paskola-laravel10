@extends('layouts.app-bootstrap')
@section('title', 'Input Nilai Siswa')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Input Nilai Siswa</h2>
        <p class="text-muted mb-0 small">Masukkan nilai per komponen untuk setiap siswa</p>
    </div>
    <a href="{{ route('grades.weights.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-sliders me-1"></i>Atur Bobot Nilai
    </a>
</div>
@endsection

@section('content')

{{-- Flash messages --}}
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
                    <option value="{{ $sem->id }}" {{ $semesterId == $sem->id ? 'selected' : '' }}>
                        {{ $sem->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Kelas</label>
                <select name="class_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Mata Pelajaran</label>
                <select name="subject_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="semester_id" value="{{ $semesterId }}">
        </form>
    </div>
</div>

{{-- Info bobot --}}
@if($weights->isNotEmpty())
<div class="card border-0 shadow-sm rounded-4 mb-4" style="background:linear-gradient(135deg,#eff6ff,#f0fdf4)">
    <div class="card-body p-3">
        <p class="small fw-semibold text-muted mb-2"><i class="bi bi-info-circle me-1"></i>Bobot Nilai Mapel Ini</p>
        <div class="d-flex flex-wrap gap-2">
            @foreach($weights as $w)
            <span class="badge bg-primary rounded-pill px-3 py-2">
                {{ $w->component_type->labelShort() }}: {{ $w->weight_percent }}%
            </span>
            @endforeach
            <span class="badge bg-dark rounded-pill px-3 py-2">Total: {{ $weights->sum('weight_percent') }}%</span>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning rounded-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Bobot nilai belum diatur untuk mapel ini.
    <a href="{{ route('grades.weights.index') }}?subject_id={{ $subjectId }}&semester_id={{ $semesterId }}" class="alert-link">Atur sekarang</a>
</div>
@endif

{{-- Tabel Input Nilai --}}
@if($students->isNotEmpty())
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
        <h6 class="fw-bold mb-0"><i class="bi bi-table me-2"></i>Daftar Siswa & Nilai</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:.85rem">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3" style="min-width:160px">Nama Siswa</th>
                        @foreach($components as $comp)
                        <th class="text-center" style="min-width:110px">{{ $comp->labelShort() }}</th>
                        @endforeach
                        <th class="text-center pe-4">Nilai Sementara</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    @php
                        $studentGrades = $grades->get($student->id, collect())->keyBy(fn($g) => $g->component_type->value);
                        $reportCard    = \App\Models\ReportCard::where('student_id', $student->id)
                            ->where('subject_id', $subjectId)
                            ->where('semester_id', $semesterId)
                            ->first();
                    @endphp
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $student->user?->name ?? $student->full_name ?? '—' }}</td>
                        @foreach($components as $comp)
                        @php $existingGrade = $studentGrades->get($comp->value); @endphp
                        <td class="text-center p-1">
                            <form action="{{ route('grades.input.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <input type="hidden" name="subject_id" value="{{ $subjectId }}">
                                <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                                <input type="hidden" name="component_type" value="{{ $comp->value }}">
                                <div class="input-group input-group-sm" style="width:100px;margin:auto">
                                    <input type="number" name="score"
                                           class="form-control text-center rounded-start-3"
                                           style="font-size:.8rem"
                                           value="{{ $existingGrade ? number_format($existingGrade->score, 0) : '' }}"
                                           min="0" max="100" step="0.5"
                                           placeholder="—">
                                    <button type="submit" class="btn btn-outline-primary btn-sm"
                                            style="font-size:.7rem">✓</button>
                                </div>
                                @if($existingGrade)
                                <div class="text-muted" style="font-size:.65rem">
                                    {{ $existingGrade->source === 'lms' ? '🤖 LMS' : '✏️ Manual' }}
                                </div>
                                @endif
                            </form>
                        </td>
                        @endforeach
                        <td class="text-center pe-4">
                            @if($reportCard && $reportCard->final_score !== null)
                                <span class="badge fs-6 px-3 rounded-pill
                                    {{ $reportCard->final_score >= 75 ? 'bg-success' : ($reportCard->final_score >= 60 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ number_format($reportCard->final_score, 1) }}
                                </span>
                                <div class="text-muted" style="font-size:.7rem">{{ $reportCard->scoreToGrade() }}</div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@elseif($classId && $subjectId)
<div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
    <i class="bi bi-people fs-2 mb-2"></i>
    <p class="mb-0">Tidak ada siswa di kelas ini atau filter belum dipilih.</p>
</div>
@else
<div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
    <i class="bi bi-arrow-up-circle fs-2 mb-2"></i>
    <p class="mb-0">Pilih kelas dan mata pelajaran untuk mulai input nilai.</p>
</div>
@endif

@endsection
