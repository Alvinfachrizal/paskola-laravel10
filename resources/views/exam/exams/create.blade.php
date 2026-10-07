@extends('layouts.app-bootstrap')
@section('title', 'Buat Ujian Baru')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Buat Ujian Baru</h2>
        <p class="text-muted mb-0 small">Pilih soal dari bank soal, tentukan jadwal dan durasi</p>
    </div>
    <a href="{{ route('exam.exams.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection

@section('content')

@if($errors->any())
<div class="alert alert-danger rounded-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('exam.exams.store') }}" id="examForm">
@csrf

<div class="row g-4">
    {{-- Kolom Kiri: Info Ujian --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3 px-4 rounded-top-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Detail Ujian</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Judul Ujian <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control rounded-3 @error('title') is-invalid @enderror"
                        placeholder="contoh: UTS Matematika Kelas X" value="{{ old('title') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                    <select name="subject_id" id="examSubject" class="form-select rounded-3 @error('subject_id') is-invalid @enderror" required>
                        <option value="">— Pilih Mapel —</option>
                        @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Kelas Peserta <span class="text-danger">*</span></label>
                    <div class="border rounded-3 p-3" style="max-height:160px;overflow-y:auto;">
                        @foreach($classes as $class)
                        <div class="form-check">
                            <input type="checkbox" name="class_ids[]" value="{{ $class->id }}"
                                class="form-check-input" id="class_{{ $class->id }}"
                                {{ in_array($class->id, old('class_ids', [])) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="class_{{ $class->id }}">{{ $class->name }}</label>
                        </div>
                        @endforeach
                    </div>
                    @error('class_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Mulai <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_at" class="form-control form-control-sm rounded-3 @error('start_at') is-invalid @enderror"
                            value="{{ old('start_at') }}" required>
                        @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Selesai <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_at" class="form-control form-control-sm rounded-3 @error('end_at') is-invalid @enderror"
                            value="{{ old('end_at') }}" required>
                        @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Durasi Pengerjaan (menit) <span class="text-danger">*</span></label>
                    <input type="number" name="duration_minutes" class="form-control rounded-3 @error('duration_minutes') is-invalid @enderror"
                        min="1" max="480" value="{{ old('duration_minutes', 60) }}" required>
                    <div class="form-text small">Waktu dihitung dari saat siswa pertama kali masuk dengan kodenya.</div>
                    @error('duration_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tampilkan Nilai ke Siswa</label>
                    <select name="show_score" class="form-select rounded-3">
                        <option value="after_approve" {{ old('show_score', 'after_approve') == 'after_approve' ? 'selected' : '' }}>
                            Setelah disetujui guru
                        </option>
                        <option value="after_submit" {{ old('show_score') == 'after_submit' ? 'selected' : '' }}>
                            Langsung setelah submit
                        </option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Deskripsi <span class="text-muted small fw-normal">(opsional)</span></label>
                    <textarea name="description" rows="3" class="form-control rounded-3"
                        placeholder="Petunjuk atau catatan untuk siswa...">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary rounded-3 fw-semibold py-2">
                <i class="bi bi-save me-2"></i>Simpan sebagai Draft
            </button>
            <a href="{{ route('exam.exams.index') }}" class="btn btn-outline-secondary rounded-3">Batal</a>
        </div>
    </div>

    {{-- Kolom Kanan: Pilih Soal --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 px-4 rounded-top-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-collection me-2 text-success"></i>Pilih Soal dari Bank Soal</h6>
                <span class="badge bg-primary-subtle text-primary rounded-pill" id="selectedCount">0 soal dipilih</span>
            </div>
            <div class="card-body p-0">
                {{-- Cari dan Filter soal --}}
                <div class="p-3 border-bottom bg-light">
                    <div class="row g-2 mb-2">
                        <div class="col-md-7">
                            <input type="text" id="questionSearch" class="form-control form-control-sm rounded-3" placeholder="Cari teks soal...">
                        </div>
                        <div class="col-md-5">
                            <select id="subjectFilter" class="form-select form-select-sm rounded-3">
                                <option value="">Semua Mapel</option>
                                @foreach($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3 w-100" id="selectAllBtn">
                        <i class="bi bi-check2-all me-1"></i> Pilih Semua (Filter Saat Ini)
                    </button>
                </div>

                @error('question_ids')
                <div class="alert alert-warning rounded-0 mb-0 py-2 small px-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                </div>
                @enderror

                <div id="questionList" style="max-height:480px;overflow-y:auto;">
                    @forelse($questions as $idx => $question)
                    <div class="question-row border-bottom p-3 d-flex gap-3 align-items-start"
                        data-text="{{ strtolower($question->question_text) }}"
                        data-subject-id="{{ $question->subject_id }}">
                        <div class="pt-1">
                            <input type="checkbox" name="question_ids[]" value="{{ $question->id }}"
                                class="form-check-input question-check" id="q_{{ $question->id }}"
                                onchange="updateCount(); updatePoints(this)"
                                {{ in_array($question->id, old('question_ids', [])) ? 'checked' : '' }}>
                        </div>
                        <label for="q_{{ $question->id }}" class="flex-grow-1 cursor-pointer mb-0" style="cursor:pointer;">
                            <span class="badge bg-light text-secondary border small me-1">{{ $question->subject->name }}</span>
                            <p class="mb-1 small fw-semibold mt-1">{{ Str::limit($question->question_text, 100) }}</p>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($question->options->sortBy('position') as $opt)
                                <span class="badge rounded-pill {{ $opt->is_correct ? 'bg-success' : 'bg-light border text-secondary' }}"
                                    style="font-size:0.7rem;">
                                    {{ $opt->positionLabel() }}. {{ Str::limit($opt->option_text ?? '[Gambar]', 25) }}
                                </span>
                                @endforeach
                            </div>
                        </label>
                        <div style="min-width:80px;">
                            <label class="form-label small text-muted mb-1">Poin</label>
                            <input type="number" name="points[]" class="form-control form-control-sm rounded-3 point-input"
                                value="{{ old('points.' . $idx, 1) }}" min="0.01" step="0.01"
                                style="width:70px;" {{ in_array($question->id, old('question_ids', [])) ? '' : 'disabled' }}>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted small p-3">
                        <i class="bi bi-inbox d-block mb-2 fs-3 opacity-25"></i>
                        Bank soal masih kosong.
                        <a href="{{ route('exam.questions.create') }}" class="d-block mt-2">Tambah soal ke bank soal →</a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
function updateCount() {
    const count = document.querySelectorAll('.question-check:checked').length;
    document.getElementById('selectedCount').textContent = count + ' soal dipilih';
}

function updatePoints(checkbox) {
    const row = checkbox.closest('.question-row');
    const pointInput = row.querySelector('.point-input');
    if (pointInput) pointInput.disabled = !checkbox.checked;
}

// Filter soal
function filterQuestions() {
    const q = document.getElementById('questionSearch').value.toLowerCase();
    const subjectId = document.getElementById('subjectFilter').value;
    
    document.querySelectorAll('.question-row').forEach(row => {
        const matchText = row.dataset.text.includes(q);
        const matchSubject = subjectId === '' || row.dataset.subjectId === subjectId;
        
        if (matchText && matchSubject) {
            row.classList.remove('d-none');
            row.classList.add('d-flex');
        } else {
            row.classList.remove('d-flex');
            row.classList.add('d-none');
        }
    });
}

document.getElementById('questionSearch').addEventListener('input', filterQuestions);
document.getElementById('subjectFilter').addEventListener('change', filterQuestions);

// Sinkronkan pilihan mapel kiri dengan filter kanan otomatis
document.getElementById('examSubject').addEventListener('change', function() {
    document.getElementById('subjectFilter').value = this.value;
    filterQuestions();
});

// Pilih Semua
document.getElementById('selectAllBtn').addEventListener('click', function() {
    const visibleRows = Array.from(document.querySelectorAll('.question-row')).filter(row => !row.classList.contains('d-none'));
    
    let allChecked = true;
    visibleRows.forEach(row => {
        if (!row.querySelector('.question-check').checked) allChecked = false;
    });

    visibleRows.forEach(row => {
        const cb = row.querySelector('.question-check');
        cb.checked = !allChecked;
        updatePoints(cb);
    });
    updateCount();
});

// Init
updateCount();
</script>
@endpush
