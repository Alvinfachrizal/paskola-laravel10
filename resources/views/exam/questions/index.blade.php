@extends('layouts.app-bootstrap')
@section('title', 'Bank Soal')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Bank Soal</h2>
        <p class="text-muted mb-0 small">Kelola soal pilihan ganda yang bisa dipakai ulang di berbagai ujian</p>
    </div>
    @can('create', App\Models\Question::class)
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm rounded-3 px-3" data-bs-toggle="modal" data-bs-target="#importAikenModal">
            <i class="bi bi-file-earmark-text me-1"></i> Import (Aiken)
        </button>
        <a href="{{ route('exam.questions.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">
            <i class="bi bi-plus-circle me-1"></i> Tambah Soal
        </a>
    </div>
    @endcan
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

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Filter --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Filter Mata Pelajaran</label>
                <select name="subject_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                    <option value="">— Semua Mata Pelajaran —</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1">Cari Soal</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control rounded-start-3"
                        placeholder="Ketik teks soal..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            @if(request()->hasAny(['subject_id', 'search']))
            <div class="col-md-3">
                <a href="{{ route('exam.questions.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 w-100">
                    <i class="bi bi-x-circle me-1"></i> Reset Filter
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Daftar Soal --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center rounded-top-4">
        <span class="fw-semibold">
            <i class="bi bi-collection me-2 text-primary"></i>
            {{ $questions->total() }} Soal Ditemukan
        </span>
    </div>
    <div class="card-body p-0">
        @forelse($questions as $question)
        <div class="border-bottom p-4 question-item" style="transition: background 0.15s;">
            <div class="d-flex justify-content-between align-items-start gap-3">
                {{-- Nomor & Mapel --}}
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1 small fw-semibold">
                            {{ $question->subject->name ?? '—' }}
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">
                            {{ $question->options->count() }} pilihan
                        </span>
                        @if($question->image_path)
                        <span class="badge bg-info-subtle text-info rounded-pill px-2 py-1 small">
                            <i class="bi bi-image me-1"></i>Bergambar
                        </span>
                        @endif
                    </div>

                    {{-- Teks soal (truncated) --}}
                    <p class="mb-2 fw-semibold" style="font-size: 0.95rem; line-height: 1.5;">
                        {{ Str::limit(strip_tags($question->question_text), 150) }}
                    </p>

                    {{-- Pilihan jawaban --}}
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach($question->options->sortBy('position') as $opt)
                        <span class="badge rounded-pill px-3 py-2 {{ $opt->is_correct ? 'bg-success text-white fw-bold' : 'bg-light text-secondary border' }}"
                            style="font-size: 0.8rem;">
                            {{ $opt->positionLabel() }}.
                            {{ Str::limit($opt->option_text ?? '[Gambar]', 40) }}
                            @if($opt->is_correct) <i class="bi bi-check-lg ms-1"></i> @endif
                        </span>
                        @endforeach
                    </div>
                </div>

                {{-- Aksi --}}
                <div class="d-flex flex-column gap-1" style="min-width: 80px;">
                    @can('update', $question)
                    <a href="{{ route('exam.questions.edit', $question) }}"
                        class="btn btn-sm btn-outline-primary rounded-3">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @endcan
                    @can('delete', $question)
                    <form method="POST" action="{{ route('exam.questions.destroy', $question) }}"
                        onsubmit="return confirm('Hapus soal ini dari bank soal?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 w-100">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox display-4 d-block mb-3 opacity-25"></i>
            <p class="mb-1 fw-semibold">Bank soal masih kosong</p>
            <p class="small mb-3">Mulai tambah soal agar bisa dipakai di ujian.</p>
            @can('create', App\Models\Question::class)
            <a href="{{ route('exam.questions.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">
                <i class="bi bi-plus-circle me-1"></i> Tambah Soal Pertama
            </a>
            @endcan
        </div>
        @endforelse
    </div>
    @if($questions->hasPages())
    <div class="card-footer bg-white border-0 rounded-bottom-4 py-3 px-4">
        {{ $questions->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@can('create', App\Models\Question::class)
<!-- Modal Import Aiken -->
<div class="modal fade" id="importAikenModal" tabindex="-1" aria-labelledby="importAikenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold" id="importAikenModalLabel">Import Soal Cepat (Format Aiken)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('exam.questions.import-aiken') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-4 bg-light">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select rounded-3" required>
                            <option value="">— Pilih Mapel —</option>
                            @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Teks Soal (Format Aiken) <span class="text-danger">*</span></label>
                        <div class="alert alert-info small py-2 mb-3 rounded-3 border-0">
                            <i class="bi bi-info-circle-fill me-1"></i> <strong>Cara Menulis Format Aiken:</strong><br>
                            <ul class="mb-0 mt-1 ps-3">
                                <li>Tulis pertanyaan dalam satu atau beberapa baris teks biasa.</li>
                                <li>Setiap pilihan jawaban harus diawali dengan huruf besar dan titik (contoh: <code>A.</code> atau <code>B.</code>).</li>
                                <li>Baris terakhir dari setiap soal <strong>WAJIB</strong> menuliskan kunci jawaban dengan huruf besar (contoh: <code>JAWABAN: B</code>).</li>
                                <li>Beri jarak satu baris kosong (Enter) untuk memisahkan antar soal satu dan soal lainnya.</li>
                            </ul>
                        </div>
                        <textarea name="aiken_text" class="form-control rounded-3 font-monospace small bg-white" rows="12" placeholder="1. Ibukota negara Indonesia adalah...
A. Bandung
B. Jakarta
C. Surabaya
D. Bali
JAWABAN: B

2. Siapakah Presiden pertama Indonesia?
A. Soeharto
B. Soekarno
C. B.J. Habibie
JAWABAN: B" required></textarea>
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 border-top">
                    <button type="button" class="btn btn-light rounded-3 px-3 fw-medium" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-medium">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection

@push('styles')
<style>
.question-item:hover { background-color: #f8fafc; }
</style>
@endpush
