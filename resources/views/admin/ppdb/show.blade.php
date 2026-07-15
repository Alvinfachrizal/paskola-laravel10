@extends('layouts.app-bootstrap')
@section('title', 'Detail Pendaftar — ' . $applicant->full_name)

@section('header')
<div class="d-flex align-items-center gap-3 w-100">
    <a href="{{ route('admin.ppdb.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="h3 mb-0 fw-bold">{{ $applicant->full_name }}</h2>
        <p class="text-muted mb-0 small">{{ $applicant->registration_code }} · {{ $applicant->wave->name ?? '' }}</p>
    </div>
    <div class="ms-auto">
        <span class="badge {{ $applicant->status->badgeClass() }} fs-6 px-3 py-2 rounded-pill">
            {{ $applicant->status->label() }}
        </span>
    </div>
</div>
@endsection

@section('content')

{{-- Flash --}}
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

<div class="row g-4">
    {{-- Kolom Kiri: Informasi Pendaftar --}}
    <div class="col-lg-5">

        {{-- Data Pribadi --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-person me-2 text-primary"></i>Data Pribadi</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3 small">
                    <div class="col-12"><span class="text-muted d-block">Nama Lengkap</span><strong>{{ $applicant->full_name }}</strong></div>
                    <div class="col-6"><span class="text-muted d-block">NISN</span>{{ $applicant->nisn ?? '—' }}</div>
                    <div class="col-6"><span class="text-muted d-block">Jenis Kelamin</span>{{ ucfirst($applicant->gender) }}</div>
                    <div class="col-6"><span class="text-muted d-block">Tempat Lahir</span>{{ $applicant->birth_place ?? '—' }}</div>
                    <div class="col-6"><span class="text-muted d-block">Tanggal Lahir</span>{{ $applicant->birth_date->format('d M Y') }}</div>
                    <div class="col-12"><span class="text-muted d-block">Alamat</span>{{ $applicant->address ?? '—' }}</div>
                    <div class="col-6"><span class="text-muted d-block">Orang Tua / Wali</span>{{ $applicant->parent_name }}</div>
                    <div class="col-6"><span class="text-muted d-block">No. HP Ortu</span>{{ $applicant->parent_phone }}</div>
                    <div class="col-12"><span class="text-muted d-block">Email</span>{{ $applicant->email ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Data Seragam --}}
        @if($applicant->uniformOrder)
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-bag me-2 text-primary"></i>Data Seragam</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="d-flex flex-wrap gap-3">
                    <div class="text-center p-3 rounded-3" style="background:#eff6ff;min-width:80px">
                        <div class="text-muted small">Ukuran</div>
                        <div class="fw-bold fs-4">{{ $applicant->uniformOrder->ukuran }}</div>
                    </div>
                    @if($applicant->uniformOrder->gender === 'perempuan')
                    <div class="text-center p-3 rounded-3" style="background:#f0fdf4;min-width:80px">
                        <div class="text-muted small">Kerudung</div>
                        <div class="fw-semibold">{{ $applicant->uniformOrder->pakai_kerudung ? 'Ya' : 'Tidak' }}</div>
                    </div>
                    <div class="text-center p-3 rounded-3" style="background:#fefce8;min-width:80px">
                        <div class="text-muted small">Bawahan</div>
                        <div class="fw-semibold">{{ ucfirst($applicant->uniformOrder->jenis_bawahan) }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Override Status --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-sliders me-2 text-warning"></i>Override Status</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('admin.ppdb.applicants.status', $applicant) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status Baru</label>
                        <select name="status" class="form-select form-select-sm rounded-3" required>
                            @foreach(\App\Enums\PpdbApplicantStatus::options() as $val => $label)
                                <option value="{{ $val }}" {{ $applicant->status->value === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan Panitia</label>
                        <textarea name="admin_notes" class="form-control form-control-sm rounded-3" rows="2"
                                  placeholder="Alasan perubahan status (opsional)">{{ $applicant->admin_notes }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm rounded-3 w-100 fw-semibold">
                        <i class="bi bi-pencil-square me-1"></i>Simpan Perubahan Status
                    </button>
                </form>
            </div>
        </div>

    </div>

    {{-- Kolom Kanan: Dokumen, Nilai, Daftar Ulang --}}
    <div class="col-lg-7">

        {{-- Verifikasi Dokumen --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-check me-2 text-primary"></i>Verifikasi Dokumen</h6>
                @if($applicant->allDocumentsValid())
                    <span class="badge bg-success rounded-pill small">Semua Valid</span>
                @elseif($applicant->hasPendingDocuments())
                    <span class="badge bg-warning text-dark rounded-pill small">Ada yang Pending</span>
                @endif
            </div>
            <div class="card-body px-4 pb-4">
                @if($applicant->documents->isEmpty())
                    <p class="text-muted text-center py-3">Belum ada dokumen yang diupload.</p>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($applicant->documents as $doc)
                        <div class="p-3 rounded-3 border" style="background:#f8fafc">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="fw-semibold small">{{ $doc->docTypeLabel() }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $doc->original_name }}</div>
                                </div>
                                <span class="badge {{ $doc->status->badgeClass() }} rounded-pill small">
                                    {{ $doc->status->label() }}
                                </span>
                            </div>
                            @if($doc->rejection_notes)
                            <div class="alert alert-warning py-1 px-2 mb-2 rounded-3" style="font-size:.8rem">
                                <i class="bi bi-chat-left me-1"></i>{{ $doc->rejection_notes }}
                            </div>
                            @endif
                            @if($doc->status->value !== 'valid')
                            {{-- Form tombol verifikasi --}}
                            <div class="d-flex gap-2 mt-2">
                                <form action="{{ route('admin.ppdb.documents.verify', [$applicant, $doc]) }}" method="POST" class="d-inline flex-grow-1">
                                    @csrf
                                    <input type="hidden" name="action" value="valid">
                                    <button type="submit" class="btn btn-success btn-sm rounded-3 w-100" onclick="return confirm('Tandai dokumen ini sebagai Valid?')">
                                        <i class="bi bi-check-lg me-1"></i>Valid
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger btn-sm rounded-3 flex-grow-1"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $doc->id }}">
                                    <i class="bi bi-x-lg me-1"></i>Tolak
                                </button>
                            </div>

                            {{-- Modal Tolak --}}
                            <div class="modal fade" id="rejectModal{{ $doc->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">Tolak Dokumen</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.ppdb.documents.verify', [$applicant, $doc]) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="invalid">
                                            <div class="modal-body">
                                                <p class="text-muted small">Dokumen: <strong>{{ $doc->docTypeLabel() }}</strong></p>
                                                <label class="form-label small fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                                                <textarea name="rejection_notes" class="form-control rounded-3" rows="3"
                                                          placeholder="Contoh: Foto tidak jelas, mohon upload ulang..." required></textarea>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger rounded-3">Konfirmasi Tolak</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="text-muted small mt-1">
                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                Diverifikasi oleh {{ $doc->verifiedBy?->name ?? 'Panitia' }}
                                pada {{ $doc->verified_at?->format('d M Y H:i') }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Input Nilai Seleksi --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Nilai Seleksi</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('admin.ppdb.scores.store', $applicant) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        @foreach($scoreTypes as $typeKey => $typeLabel)
                        @php
                            $existing = $applicant->selectionScores->firstWhere('score_type', $typeKey);
                        @endphp
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">{{ $typeLabel }}</label>
                            <div class="input-group input-group-sm">
                                <input type="hidden" name="scores[{{ $loop->index }}][score_type]" value="{{ $typeKey }}">
                                <input type="number" name="scores[{{ $loop->index }}][score_value]"
                                       class="form-control rounded-start-3"
                                       placeholder="0–100" min="0" max="100" step="0.5"
                                       value="{{ $existing?->score_value }}">
                                <span class="input-group-text rounded-end-3 text-muted" style="font-size:.75rem">/ 100</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($applicant->selectionScores->isNotEmpty())
                    <div class="mt-3 p-3 rounded-3" style="background:#eff6ff">
                        <span class="text-primary small fw-semibold">
                            <i class="bi bi-calculator me-1"></i>
                            Rata-rata: <strong>{{ number_format($applicant->averageScore(), 2) }}</strong>
                        </span>
                    </div>
                    @endif
                    <button type="submit" class="btn btn-primary btn-sm rounded-3 mt-3 w-100">
                        <i class="bi bi-save me-1"></i>Simpan Nilai Seleksi
                    </button>
                </form>
            </div>
        </div>

        {{-- Daftar Ulang --}}
        @if($applicant->status->value === 'selected' || $applicant->status->value === 're_registered')
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-person-check me-2 text-success"></i>Daftar Ulang</h6>
            </div>
            <div class="card-body px-4 pb-4">
                @if($applicant->reregistration?->status?->value === 're_registered' || $applicant->status->value === 're_registered')
                    <div class="alert alert-success rounded-3 mb-0">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Daftar ulang selesai!</strong><br>
                        Siswa telah terdaftar di sistem.
                        @if($applicant->reregistration?->student)
                            <a href="{{ route('admin.students.show', $applicant->reregistration->student) }}"
                               class="btn btn-outline-success btn-sm rounded-3 mt-2 d-block">
                                <i class="bi bi-person-badge me-1"></i>Lihat Data Siswa
                            </a>
                        @endif
                    </div>
                @else
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Proses ini akan membuat akun User + data Siswa baru berdasarkan data pendaftaran.
                        Password default: <strong>tanggal lahir (ddmmyyyy)</strong>.
                    </p>
                    <form action="{{ route('admin.ppdb.reregistration.process', $applicant) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">NIS (Nomor Induk Siswa)</label>
                            <input type="text" name="nis" class="form-control form-control-sm rounded-3"
                                   placeholder="Opsional, bisa diisi nanti">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Catatan</label>
                            <input type="text" name="notes" class="form-control form-control-sm rounded-3"
                                   placeholder="Catatan daftar ulang (opsional)">
                        </div>
                        <button type="submit" class="btn btn-success rounded-3 w-100 fw-semibold"
                                onclick="return confirm('Proses daftar ulang dan buat akun siswa untuk {{ $applicant->full_name }}?')">
                            <i class="bi bi-person-plus me-2"></i>Proses Daftar Ulang & Buat Akun Siswa
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

@endsection
