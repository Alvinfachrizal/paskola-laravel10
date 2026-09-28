@extends('layouts.app-bootstrap')
@section('title', 'Edit Soal')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Edit Soal</h2>
        <p class="text-muted mb-0 small">Perubahan akan berlaku untuk semua ujian yang memakai soal ini</p>
    </div>
    <a href="{{ route('exam.questions.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection

@section('content')

@if($errors->any())
<div class="alert alert-danger rounded-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Periksa kembali isian:</strong>
    <ul class="mb-0 mt-2 ps-3">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('exam.questions.update', $question) }}" enctype="multipart/form-data" id="questionForm">
    @csrf @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 px-4 rounded-top-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-question-circle me-2 text-primary"></i>Teks Soal</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select rounded-3 @error('subject_id') is-invalid @enderror" required>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ $question->subject_id == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('subject_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Teks Soal <span class="text-danger">*</span></label>
                        <textarea name="question_text" rows="4"
                            class="form-control rounded-3 @error('question_text') is-invalid @enderror" required>{{ old('question_text', $question->question_text) }}</textarea>
                        @error('question_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gambar Soal</label>
                        @if($question->image_path)
                        <div class="mb-2">
                            <img src="{{ Storage::url($question->image_path) }}"
                                alt="Gambar soal" style="max-height:120px;border-radius:8px;border:1px solid #dee2e6;padding:4px;">
                            <p class="small text-muted mt-1 mb-0">Upload gambar baru untuk mengganti gambar di atas.</p>
                        </div>
                        @endif
                        <input type="file" name="question_image"
                            class="form-control rounded-3 @error('question_image') is-invalid @enderror"
                            accept="image/jpeg,image/png,image/webp"
                            onchange="previewImage(this, 'questionImagePreview')">
                        @error('question_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div id="questionImagePreview" class="mt-2"></div>
                    </div>
                </div>
            </div>

            {{-- Pilihan Jawaban --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3 px-4 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-check me-2 text-success"></i>Pilihan Jawaban</h6>
                    <button type="button" id="addOptionBtn" class="btn btn-sm btn-outline-success rounded-3" onclick="addOption()">
                        <i class="bi bi-plus me-1"></i> Tambah Pilihan
                    </button>
                </div>
                <div class="card-body px-4 pb-4">
                    @error('options')
                    <div class="alert alert-warning rounded-3 py-2 small mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                    </div>
                    @enderror

                    <div id="optionsList">
                        @foreach($question->options->sortBy('position') as $idx => $opt)
                        <div class="option-item border rounded-3 p-3 mb-3" data-index="{{ $idx }}">
                            <input type="hidden" name="options[{{ $idx }}][id]" value="{{ $opt->id }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-secondary rounded-pill fw-bold option-label">
                                    Pilihan {{ $opt->positionLabel() }}
                                </span>
                                @if($idx >= 2)
                                <button type="button" class="btn btn-sm btn-link text-danger p-0"
                                    onclick="removeOption(this)">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                @endif
                            </div>
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <input type="text" name="options[{{ $idx }}][text]"
                                        class="form-control form-control-sm rounded-3"
                                        placeholder="Teks pilihan..." value="{{ $opt->option_text }}">
                                </div>
                                <div class="col-md-4">
                                    @if($opt->image_path)
                                    <div class="mb-1">
                                        <img src="{{ Storage::url($opt->image_path) }}"
                                            style="max-height:50px;border-radius:4px;border:1px solid #dee2e6;">
                                    </div>
                                    @endif
                                    <input type="file" name="options[{{ $idx }}][image]"
                                        class="form-control form-control-sm rounded-3"
                                        accept="image/jpeg,image/png,image/webp"
                                        onchange="previewOptionImage(this, {{ $idx }})">
                                    <div id="optPreview{{ $idx }}" class="mt-1"></div>
                                </div>
                            </div>
                            <div class="form-check mt-2">
                                <input type="radio" name="correct_option" value="{{ $idx }}"
                                    class="form-check-input" id="correct{{ $idx }}"
                                    onchange="markCorrect({{ $idx }})"
                                    {{ $opt->is_correct ? 'checked' : '' }}>
                                <input type="hidden" name="options[{{ $idx }}][is_correct]"
                                    id="hidden_correct_{{ $idx }}" value="{{ $opt->is_correct ? '1' : '0' }}">
                                <label class="form-check-label small fw-semibold" for="correct{{ $idx }}">
                                    ✓ Ini jawaban yang benar
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-warning bg-opacity-10 border-warning mb-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-2 text-warning-emphasis"><i class="bi bi-exclamation-triangle me-2"></i>Perhatian</h6>
                    <p class="small text-muted mb-0">Perubahan pada soal ini akan berdampak pada semua ujian yang menggunakannya. Pastikan perubahan tidak mengubah makna soal secara fundamental.</p>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-3 fw-semibold py-2">
                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                </button>
                <a href="{{ route('exam.questions.index') }}" class="btn btn-outline-secondary rounded-3">Batal</a>
            </div>
        </div>
    </div>
</form>

@endsection

@push('styles')
<style>
.option-item { background: #fafafa; }
.option-item:has(input[type=radio]:checked) { border-color: #198754 !important; background: #f0fff4; }
</style>
@endpush

@push('scripts')
<script>
let optionCount = {{ $question->options->count() }};

function addOption() {
    if (optionCount >= 5) { alert('Maksimal 5 pilihan jawaban.'); return; }
    const idx = optionCount;
    const label = String.fromCharCode(65 + idx);
    const html = `
    <div class="option-item border rounded-3 p-3 mb-3" data-index="${idx}">
        <input type="hidden" name="options[${idx}][id]" value="">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-secondary rounded-pill fw-bold option-label">Pilihan ${label}</span>
            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeOption(this)">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>
        <div class="row g-2">
            <div class="col-md-8">
                <input type="text" name="options[${idx}][text]" class="form-control form-control-sm rounded-3" placeholder="Teks pilihan ${label}...">
            </div>
            <div class="col-md-4">
                <input type="file" name="options[${idx}][image]" class="form-control form-control-sm rounded-3"
                    accept="image/jpeg,image/png,image/webp" onchange="previewOptionImage(this, ${idx})">
                <div id="optPreview${idx}" class="mt-1"></div>
            </div>
        </div>
        <div class="form-check mt-2">
            <input type="radio" name="correct_option" value="${idx}" class="form-check-input" id="correct${idx}" onchange="markCorrect(${idx})">
            <input type="hidden" name="options[${idx}][is_correct]" id="hidden_correct_${idx}" value="0">
            <label class="form-check-label small fw-semibold" for="correct${idx}">✓ Ini jawaban yang benar</label>
        </div>
    </div>`;
    document.getElementById('optionsList').insertAdjacentHTML('beforeend', html);
    optionCount++;
    document.getElementById('addOptionBtn').disabled = optionCount >= 5;
}

function removeOption(btn) {
    if (document.querySelectorAll('.option-item').length <= 2) { alert('Minimal 2 pilihan.'); return; }
    btn.closest('.option-item').remove();
    optionCount--;
    reindexOptions();
    document.getElementById('addOptionBtn').disabled = optionCount >= 5;
}

function reindexOptions() {
    document.querySelectorAll('.option-item').forEach((item, i) => {
        const label = String.fromCharCode(65 + i);
        item.dataset.index = i;
        item.querySelector('.option-label').textContent = 'Pilihan ' + label;
        item.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/options\[\d+\]/, `options[${i}]`);
            if (el.id) el.id = el.id.replace(/\d+$/, i);
        });
        const radio = item.querySelector('input[type=radio]');
        if (radio) { radio.value = i; radio.onchange = () => markCorrect(i); }
        const lbl = item.querySelector('label[for^="correct"]');
        if (lbl) lbl.setAttribute('for', 'correct' + i);
    });
}

function markCorrect(idx) {
    document.querySelectorAll('[id^="hidden_correct_"]').forEach((el, i) => { el.value = i === idx ? '1' : '0'; });
}

function previewImage(input, previewId) {
    const c = document.getElementById(previewId); c.innerHTML = '';
    if (input.files && input.files[0]) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(input.files[0]);
        img.style.cssText = 'max-height:120px;border-radius:8px;border:1px solid #dee2e6;padding:4px;';
        c.appendChild(img);
    }
}

function previewOptionImage(input, idx) {
    const c = document.getElementById('optPreview' + idx); if (!c) return; c.innerHTML = '';
    if (input.files && input.files[0]) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(input.files[0]);
        img.style.cssText = 'max-height:60px;border-radius:6px;border:1px solid #dee2e6;';
        c.appendChild(img);
    }
}

document.getElementById('questionForm').addEventListener('submit', function() {
    const checked = document.querySelector('input[name=correct_option]:checked');
    if (checked) markCorrect(parseInt(checked.value));
});
</script>
@endpush
