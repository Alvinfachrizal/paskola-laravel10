@extends('layouts.app-bootstrap')
@section('title', 'Tambah Soal Baru')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Tambah Soal Baru</h2>
        <p class="text-muted mb-0 small">Soal akan disimpan di bank soal dan bisa dipakai di banyak ujian</p>
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

<form method="POST" action="{{ route('exam.questions.store') }}" enctype="multipart/form-data" id="questionForm">
    @csrf

    <div class="row g-4">
        {{-- Kolom Kiri: Soal Utama --}}
        <div class="col-lg-8">

            {{-- Card: Detail Soal --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 px-4 rounded-top-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-question-circle me-2 text-primary"></i>Teks Soal</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    {{-- Mata Pelajaran --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select rounded-3 @error('subject_id') is-invalid @enderror" required>
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Teks Soal --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Teks Soal <span class="text-danger">*</span></label>
                        <textarea name="question_text" rows="4"
                            class="form-control rounded-3 @error('question_text') is-invalid @enderror"
                            placeholder="Tulis pertanyaan di sini..." required>{{ old('question_text') }}</textarea>
                        @error('question_text')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Gambar Soal --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gambar Soal <span class="text-muted small fw-normal">(opsional, jpg/png/webp, maks 2MB)</span></label>
                        <input type="file" name="question_image" id="questionImageInput"
                            class="form-control rounded-3 @error('question_image') is-invalid @enderror"
                            accept="image/jpeg,image/png,image/webp"
                            onchange="previewImage(this, 'questionImagePreview')">
                        @error('question_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="questionImagePreview" class="mt-2"></div>
                    </div>

                    {{-- Simpan ke Bank Soal --}}
                    <div class="form-check">
                        <input type="checkbox" name="in_bank" value="1" id="inBank"
                            class="form-check-input" {{ old('in_bank', true) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="inBank">
                            Simpan ke bank soal (agar bisa dipakai ulang di ujian lain)
                        </label>
                    </div>
                </div>
            </div>

            {{-- Card: Pilihan Jawaban --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3 px-4 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-list-check me-2 text-success"></i>Pilihan Jawaban
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Min. 2, Maks. 5 pilihan</span>
                        <button type="button" id="addOptionBtn" class="btn btn-sm btn-outline-success rounded-3"
                            onclick="addOption()">
                            <i class="bi bi-plus me-1"></i> Tambah Pilihan
                        </button>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    @error('options')
                    <div class="alert alert-warning rounded-3 py-2 small mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                    </div>
                    @enderror

                    <div id="optionsList">
                        {{-- 4 pilihan default (A, B, C, D) --}}
                        @for($i = 0; $i < 4; $i++)
                        <div class="option-item border rounded-3 p-3 mb-3 position-relative" data-index="{{ $i }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-secondary rounded-pill fw-bold option-label">
                                    Pilihan {{ chr(65 + $i) }}
                                </span>
                                @if($i >= 2)
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-option-btn"
                                    onclick="removeOption(this)" title="Hapus pilihan ini">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                @endif
                            </div>

                            <div class="row g-2">
                                <div class="col-md-8">
                                    <label class="form-label small text-muted">Teks Jawaban</label>
                                    <input type="text" name="options[{{ $i }}][text]"
                                        class="form-control form-control-sm rounded-3"
                                        placeholder="Teks pilihan {{ chr(65 + $i) }}..."
                                        value="{{ old("options.{$i}.text") }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Gambar (opsional)</label>
                                    <input type="file" name="options[{{ $i }}][image]"
                                        class="form-control form-control-sm rounded-3"
                                        accept="image/jpeg,image/png,image/webp"
                                        onchange="previewOptionImage(this, {{ $i }})">
                                    <div id="optPreview{{ $i }}" class="mt-1"></div>
                                </div>
                            </div>

                            {{-- Tandai sebagai benar --}}
                            <div class="form-check mt-2">
                                <input type="radio" name="correct_option" value="{{ $i }}"
                                    class="form-check-input" id="correct{{ $i }}"
                                    onchange="markCorrect({{ $i }})"
                                    {{ old('correct_option') == $i ? 'checked' : '' }}>
                                <input type="hidden" name="options[{{ $i }}][is_correct]" id="hidden_correct_{{ $i }}" value="0">
                                <label class="form-check-label small fw-semibold" for="correct{{ $i }}">
                                    ✓ Ini jawaban yang benar
                                </label>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>

        </div>

        {{-- Kolom Kanan: Panduan --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-light mb-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb text-warning me-2"></i>Panduan</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Pilih <strong>tepat 1</strong> jawaban yang benar</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Setiap pilihan harus ada teks atau gambar</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Gambar: jpg/png/webp, maks <strong>2 MB</strong></li>
                        <li class="mb-2"><i class="bi bi-info-circle text-primary me-2"></i>Soal yang disimpan di bank soal bisa dipakai di banyak ujian berbeda</li>
                        <li><i class="bi bi-info-circle text-primary me-2"></i>Bank soal bersifat <strong>pribadi per guru</strong></li>
                    </ul>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-3 fw-semibold py-2">
                    <i class="bi bi-save me-2"></i>Simpan Soal
                </button>
                <a href="{{ route('exam.questions.index') }}" class="btn btn-outline-secondary rounded-3">
                    Batal
                </a>
            </div>
        </div>
    </div>
</form>

@endsection

@push('styles')
<style>
.option-item { background: #fafafa; transition: border-color 0.2s, background 0.2s; }
.option-item:has(input[type=radio]:checked) { border-color: #198754 !important; background: #f0fff4; }
.option-item img { max-height: 80px; border-radius: 6px; object-fit: contain; }
</style>
@endpush

@push('scripts')
<script>
let optionCount = 4; // sudah ada 4 pilihan default

// ── Tambah pilihan baru ──────────────────────────────────────────────────────
function addOption() {
    if (optionCount >= 5) {
        alert('Maksimal 5 pilihan jawaban.');
        return;
    }
    const idx = optionCount;
    const label = String.fromCharCode(65 + idx); // A=65, B=66, ...
    const html = `
    <div class="option-item border rounded-3 p-3 mb-3 position-relative" data-index="${idx}">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-secondary rounded-pill fw-bold option-label">Pilihan ${label}</span>
            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-option-btn"
                onclick="removeOption(this)" title="Hapus pilihan ini">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>
        <div class="row g-2">
            <div class="col-md-8">
                <label class="form-label small text-muted">Teks Jawaban</label>
                <input type="text" name="options[${idx}][text]"
                    class="form-control form-control-sm rounded-3" placeholder="Teks pilihan ${label}...">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Gambar (opsional)</label>
                <input type="file" name="options[${idx}][image]"
                    class="form-control form-control-sm rounded-3"
                    accept="image/jpeg,image/png,image/webp"
                    onchange="previewOptionImage(this, ${idx})">
                <div id="optPreview${idx}" class="mt-1"></div>
            </div>
        </div>
        <div class="form-check mt-2">
            <input type="radio" name="correct_option" value="${idx}"
                class="form-check-input" id="correct${idx}"
                onchange="markCorrect(${idx})">
            <input type="hidden" name="options[${idx}][is_correct]" id="hidden_correct_${idx}" value="0">
            <label class="form-check-label small fw-semibold" for="correct${idx}">
                ✓ Ini jawaban yang benar
            </label>
        </div>
    </div>`;
    document.getElementById('optionsList').insertAdjacentHTML('beforeend', html);
    optionCount++;
    updateAddButton();
}

// ── Hapus pilihan ────────────────────────────────────────────────────────────
function removeOption(btn) {
    const allItems = document.querySelectorAll('.option-item');
    if (allItems.length <= 2) {
        alert('Minimal 2 pilihan jawaban.');
        return;
    }
    btn.closest('.option-item').remove();
    optionCount--;
    reindexOptions();
    updateAddButton();
}

// ── Reindex setelah hapus ────────────────────────────────────────────────────
function reindexOptions() {
    const items = document.querySelectorAll('.option-item');
    items.forEach((item, i) => {
        const label = String.fromCharCode(65 + i);
        item.dataset.index = i;
        item.querySelector('.option-label').textContent = 'Pilihan ' + label;
        item.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/options\[\d+\]/, `options[${i}]`);
            if (el.id) el.id = el.id.replace(/\d+$/, i);
        });
        const radio = item.querySelector('input[type=radio]');
        if (radio) { radio.value = i; radio.onchange = () => markCorrect(i); }
        const label2 = item.querySelector('label[for^="correct"]');
        if (label2) label2.setAttribute('for', 'correct' + i);
    });
}

// ── Tandai jawaban benar ─────────────────────────────────────────────────────
function markCorrect(selectedIdx) {
    document.querySelectorAll('[id^="hidden_correct_"]').forEach((el, i) => {
        el.value = i === selectedIdx ? '1' : '0';
    });
}

// ── Preview gambar ────────────────────────────────────────────────────────────
function previewImage(input, previewId) {
    const container = document.getElementById(previewId);
    container.innerHTML = '';
    if (input.files && input.files[0]) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(input.files[0]);
        img.style.cssText = 'max-height:120px;border-radius:8px;object-fit:contain;border:1px solid #dee2e6;padding:4px;';
        container.appendChild(img);
    }
}

function previewOptionImage(input, idx) {
    const container = document.getElementById('optPreview' + idx);
    if (!container) return;
    container.innerHTML = '';
    if (input.files && input.files[0]) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(input.files[0]);
        img.style.cssText = 'max-height:60px;border-radius:6px;object-fit:contain;border:1px solid #dee2e6;';
        container.appendChild(img);
    }
}

function updateAddButton() {
    const btn = document.getElementById('addOptionBtn');
    if (btn) btn.disabled = optionCount >= 5;
}

// Sinkronkan hidden field saat form submit
document.getElementById('questionForm').addEventListener('submit', function() {
    const checkedRadio = document.querySelector('input[name=correct_option]:checked');
    if (checkedRadio) {
        markCorrect(parseInt(checkedRadio.value));
    }
});
</script>
@endpush
