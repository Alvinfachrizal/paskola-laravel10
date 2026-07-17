@extends('layouts.app-bootstrap')
@section('title', 'Detail Pengumpulan Tugas')

@section('header')
<div class="d-flex align-items-center gap-3 w-100">
    <a href="{{ route('admin.lms-submissions.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="h4 mb-0 fw-bold">Detail Pengumpulan</h2>
        <p class="text-muted mb-0 small">{{ $lmsSubmission->assignment?->title }}</p>
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

<div class="row g-4">
    {{-- Kolom Kiri: Info Submission --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-person-circle me-2 text-primary"></i>Info Siswa & Jawaban</h6>
            </div>
            <div class="card-body px-4 pb-4">
                {{-- Info siswa --}}
                <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-4" style="background:#eff6ff">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold"
                         style="width:48px;height:48px;font-size:1.1rem">
                        {{ strtoupper(substr($lmsSubmission->student?->name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $lmsSubmission->student?->name ?? '—' }}</div>
                        <div class="text-muted small">{{ $lmsSubmission->student?->email ?? '' }}</div>
                    </div>
                    <div class="ms-auto text-end">
                        <div class="text-muted small">Dikumpulkan:</div>
                        <div class="fw-semibold small">
                            {{ $lmsSubmission->submitted_at?->format('d M Y, H:i') ?? '—' }}
                        </div>
                    </div>
                </div>

                {{-- Catatan siswa --}}
                @if($lmsSubmission->notes || $lmsSubmission->text_content)
                <div class="mb-4">
                    <label class="text-muted small fw-semibold d-block mb-2">Catatan / Jawaban Siswa</label>
                    <div class="p-3 bg-light rounded-3" style="font-size:.875rem;white-space:pre-wrap">
                        {{ $lmsSubmission->notes ?? $lmsSubmission->text_content }}
                    </div>
                </div>
                @endif

                {{-- File submission --}}
                @if($lmsSubmission->file_path || $lmsSubmission->file_url)
                <div class="mb-4">
                    <label class="text-muted small fw-semibold d-block mb-2">File Jawaban</label>
                    <a href="{{ $lmsSubmission->file_url ?? Storage::url($lmsSubmission->file_path) }}"
                       target="_blank"
                       class="btn btn-outline-primary rounded-3 px-4">
                        <i class="bi bi-download me-2"></i>Download File Jawaban
                    </a>
                </div>
                @else
                <div class="alert alert-light border rounded-3 small text-muted">
                    <i class="bi bi-file-earmark-x me-2"></i>Siswa tidak mengupload file.
                </div>
                @endif

                {{-- Info tugas --}}
                <div class="border-top pt-3 mt-2">
                    <div class="row g-2 text-sm">
                        <div class="col-6">
                            <div class="text-muted small">Mata Pelajaran</div>
                            <div class="fw-semibold">{{ $lmsSubmission->assignment?->subject?->name ?? '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Batas Pengumpulan</div>
                            <div class="fw-semibold {{ \Carbon\Carbon::parse($lmsSubmission->assignment?->due_date)->isPast() ? 'text-danger' : 'text-success' }}">
                                {{ $lmsSubmission->assignment?->due_date ? \Carbon\Carbon::parse($lmsSubmission->assignment->due_date)->format('d M Y, H:i') : '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Form Penilaian (hanya Guru/Admin) --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-star me-2 text-warning"></i>Penilaian Guru</h6>
            </div>
            <div class="card-body px-4 pb-4">

                {{-- Status nilai saat ini --}}
                @if($lmsSubmission->score !== null)
                <div class="text-center mb-4 p-3 rounded-3"
                     style="background:{{ $lmsSubmission->score >= 75 ? '#f0fdf4' : ($lmsSubmission->score >= 60 ? '#fffbeb' : '#fef2f2') }}">
                    <div class="fw-bold" style="font-size:3rem;line-height:1;
                        color:{{ $lmsSubmission->score >= 75 ? '#16a34a' : ($lmsSubmission->score >= 60 ? '#d97706' : '#dc2626') }}">
                        {{ $lmsSubmission->score }}
                    </div>
                    <div class="text-muted small mt-1">dari {{ $lmsSubmission->assignment?->max_score ?? 100 }}</div>
                    @if($lmsSubmission->graded_at)
                    <div class="text-muted mt-1" style="font-size:.75rem">
                        Dinilai {{ $lmsSubmission->graded_at->diffForHumans() }}
                    </div>
                    @endif
                </div>
                @else
                <div class="alert alert-warning border-0 rounded-3 small mb-4">
                    <i class="bi bi-clock me-2"></i>Belum dinilai
                </div>
                @endif

                @if($lmsSubmission->feedback)
                <div class="mb-4">
                    <label class="text-muted small fw-semibold d-block mb-1">Feedback Sebelumnya</label>
                    <div class="p-3 bg-light rounded-3 small">{{ $lmsSubmission->feedback }}</div>
                </div>
                @endif

                {{-- Form beri nilai (hanya Guru/Admin/Kepsek) --}}
                @hasanyrole('Super Admin|Admin|Kepala Sekolah|Guru')
                <form action="{{ route('admin.lms-submissions.update', $lmsSubmission->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">
                            Nilai
                            <span class="text-muted fw-normal">(0–{{ $lmsSubmission->assignment?->max_score ?? 100 }})</span>
                        </label>
                        <input type="number" name="score" class="form-control rounded-3 text-center fw-bold"
                               style="font-size:1.5rem;height:60px"
                               value="{{ $lmsSubmission->score ?? '' }}"
                               min="0" max="{{ $lmsSubmission->assignment?->max_score ?? 100 }}"
                               placeholder="—">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted">Feedback / Komentar</label>
                        <textarea name="feedback" class="form-control bg-light border-0 rounded-3"
                                  rows="3" placeholder="Tulis komentar atau catatan perbaikan...">{{ $lmsSubmission->feedback }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold">
                        <i class="bi bi-save me-2"></i>Simpan Penilaian
                    </button>
                </form>
                @endhasanyrole

                {{-- Siswa: hanya baca feedback --}}
                @hasrole('Siswa')
                <div class="text-center text-muted p-3">
                    <i class="bi bi-eye-slash fs-3 d-block mb-2"></i>
                    <p class="small mb-0">Nilai dan feedback dari guru akan muncul di sini setelah dinilai.</p>
                </div>
                @endhasrole
            </div>
        </div>
    </div>
</div>

@endsection
