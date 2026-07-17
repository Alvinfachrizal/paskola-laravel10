@extends('layouts.app-bootstrap')
@section('title', 'Atur Bobot Nilai')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Bobot Komponen Nilai</h2>
        <p class="text-muted mb-0 small">Total bobot semua komponen <strong>harus tepat 100%</strong></p>
    </div>
    <a href="{{ route('grades.input.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Input Nilai
    </a>
</div>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger rounded-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
</div>
@endif

{{-- Filter --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Semester</label>
                <select name="semester_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                    @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $semId == $sem->id ? 'selected' : '' }}>{{ $sem->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Mata Pelajaran</label>
                <select name="subject_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                    @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}" {{ $subjectId == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

@if($subjectId && $semId)
{{-- Form Bobot --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="bi bi-sliders me-2"></i>Atur Bobot Per Komponen</h6>
        {{-- Live total indicator --}}
        <span class="fw-bold" id="totalDisplay">
            Total: <span id="totalPct" class="{{ $totalPct == 100 ? 'text-success' : 'text-danger' }}">{{ $totalPct }}</span>%
        </span>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('grades.weights.store') }}" method="POST">
            @csrf
            <input type="hidden" name="subject_id" value="{{ $subjectId }}">
            <input type="hidden" name="semester_id" value="{{ $semId }}">

            <div class="row g-3 mb-4">
                @foreach($components as $comp)
                @php
                    $existing = $weights->firstWhere('component_type', $comp);
                    $pct = old("weights.{$comp->value}", $existing?->weight_percent ?? 0);
                @endphp
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">{{ $comp->label() }}</label>
                    <div class="input-group">
                        <input type="number" name="weights[{{ $comp->value }}]"
                               class="form-control rounded-start-3 weight-input"
                               value="{{ $pct }}" min="0" max="100" step="5"
                               placeholder="0">
                        <span class="input-group-text rounded-end-3">%</span>
                    </div>
                    <div class="form-text text-muted" style="font-size:.75rem">
                        {{ $comp->defaultSource() === 'lms' ? '🤖 Biasanya dari LMS' : '✏️ Input manual guru' }}
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Progress bar total --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Progress bobot</span>
                    <span id="progressLabel" class="fw-semibold">{{ $totalPct }}% / 100%</span>
                </div>
                <div class="progress rounded-pill" style="height:10px">
                    <div class="progress-bar {{ $totalPct == 100 ? 'bg-success' : ($totalPct > 100 ? 'bg-danger' : 'bg-warning') }}"
                         id="progressBar"
                         style="width:{{ min($totalPct, 100) }}%"></div>
                </div>
                @if($totalPct == 100)
                <p class="text-success small mt-1"><i class="bi bi-check-circle me-1"></i>Bobot sudah valid (total 100%)</p>
                @elseif($totalPct > 0)
                <p class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>Total harus tepat 100%, saat ini {{ $totalPct }}%</p>
                @endif
            </div>

            <button type="submit" class="btn btn-primary rounded-3" id="saveBtn">
                <i class="bi bi-save me-1"></i>Simpan Bobot
            </button>
        </form>
    </div>
</div>
@else
<div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
    <i class="bi bi-sliders fs-2 mb-2"></i>
    <p>Pilih semester dan mata pelajaran untuk mengatur bobot.</p>
</div>
@endif

@endsection

@push('scripts')
<script>
const inputs   = document.querySelectorAll('.weight-input');
const totalEl  = document.getElementById('totalPct');
const progBar  = document.getElementById('progressBar');
const progLbl  = document.getElementById('progressLabel');
const saveBtn  = document.getElementById('saveBtn');

function updateTotal() {
    let total = 0;
    inputs.forEach(i => total += parseInt(i.value) || 0);

    if (totalEl) {
        totalEl.textContent = total;
        totalEl.className   = total === 100 ? 'text-success' : 'text-danger';
    }
    if (progBar) {
        progBar.style.width = Math.min(total, 100) + '%';
        progBar.className   = 'progress-bar ' + (total === 100 ? 'bg-success' : total > 100 ? 'bg-danger' : 'bg-warning');
    }
    if (progLbl) progLbl.textContent = total + '% / 100%';
    if (saveBtn) saveBtn.disabled = total !== 100;
}

inputs.forEach(i => i.addEventListener('input', updateTotal));
updateTotal();
</script>
@endpush
