@extends('layouts.app-bootstrap')
@section('title', 'Tugas & Ujian')

@section('header')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h3 mb-1 text-dark fw-bold" style="letter-spacing:-0.5px;">Tugas & Ujian</h2>
            <p class="text-muted mb-0" style="font-size:0.875rem;">Kelola penugasan dan evaluasi siswa</p>
        </div>
        <div class="d-grid d-sm-block">
            @hasanyrole('Super Admin|Admin|Guru')
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-2 shadow-sm transition-hover fw-medium d-flex align-items-center justify-content-center gap-1" style="font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                <i class="bi bi-plus-circle"></i> Buat Tugas
            </button>
            @endhasanyrole
        </div>
    </div>
@endsection

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 bg-success-subtle" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Assignments List -->
<div class="row g-4">
    @forelse($assignments as $assignment)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 rounded-4 shadow-sm h-100 material-card transition-hover position-relative overflow-hidden">
                @php
                    $isPastDue = \Carbon\Carbon::parse($assignment->due_date)->isPast();
                @endphp
                
                <!-- Status Border -->
                <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: {{ $isPastDue ? '#ef4444' : '#10b981' }};"></div>
                
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-light text-dark border fw-medium mb-2">{{ $assignment->subject->name ?? 'Umum' }}</span>
                            <h5 class="fw-bold text-dark mb-1" style="font-size: 1.1rem; line-height: 1.4;">{{ $assignment->title }}</h5>
                            <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-person me-1"></i>{{ $assignment->teacher->name ?? 'Guru' }}</div>
                        </div>
                        
                        <!-- Dropdown Menu for Edit/Delete (Only for owner/admin) -->
                        @if(Auth::id() === $assignment->teacher_id || Auth::user()->hasRole(['Super Admin', 'Admin']))
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-circle text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px;">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3 text-sm">
                                <li><a class="dropdown-item py-2" href="#"><i class="bi bi-pencil me-2 text-primary"></i> Edit</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('admin.lms-assignments.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('Hapus tugas ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-trash me-2"></i> Hapus</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        @endif
                    </div>
                    
                    <p class="text-muted mb-4 flex-grow-1" style="font-size: 0.85rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ $assignment->description ?: 'Tidak ada instruksi khusus.' }}
                    </p>
                    
                    <div class="bg-light rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted" style="font-size: 0.75rem;">Batas Pengumpulan:</span>
                            <span class="fw-semibold {{ $isPastDue ? 'text-danger' : 'text-success' }}" style="font-size: 0.8rem;">
                                {{ \Carbon\Carbon::parse($assignment->due_date)->format('d M Y, H:i') }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted" style="font-size: 0.75rem;">Kelas:</span>
                            <span class="fw-semibold text-dark" style="font-size: 0.8rem;">
                                <i class="bi bi-building me-1"></i>{{ $assignment->schoolClass->name ?? 'Semua' }}
                            </span>
                        </div>
                    </div>
                    
                    @if($assignment->file_url)
                        <div class="mb-3">
                            <a href="{{ $assignment->file_url }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" style="font-size: 0.8rem;">
                                <i class="bi bi-paperclip me-1"></i> Lampiran Tugas
                            </a>
                        </div>
                    @endif

                    <div class="d-grid mt-auto">
                        @if (Auth::user()->hasRole('Siswa'))
                            <button class="btn {{ $isPastDue ? 'btn-outline-danger' : 'btn-outline-primary' }} fw-medium rounded-pill shadow-sm" {{ $isPastDue ? 'disabled' : '' }}>
                                <i class="bi bi-cloud-upload me-1"></i> {{ $isPastDue ? 'Tenggat Berlalu' : 'Kumpulkan Tugas' }}
                            </button>
                        @else
                            <button class="btn btn-outline-dark fw-medium rounded-pill shadow-sm">
                                <i class="bi bi-journal-text me-1"></i> Lihat Pengumpulan ({{ $assignment->submissions_count ?? 0 }})
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3 text-muted" style="width: 80px; height: 80px;">
                <i class="bi bi-card-checklist fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark">Belum ada tugas</h5>
            <p class="text-muted">Daftar tugas yang diberikan akan muncul di sini.</p>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $assignments->links() }}
</div>

<!-- Modal Buat Tugas -->
@hasanyrole('Super Admin|Admin|Guru')
<div class="modal fade" id="createAssignmentModal" tabindex="-1" aria-labelledby="createAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-bottom-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="createAssignmentModalLabel" style="font-size: 1.1rem;"><i class="bi bi-plus-circle me-2 text-primary"></i>Buat Tugas Baru</h5>
                <button type="button" class="btn-close bg-light rounded-circle" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.lms-assignments.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Judul Tugas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light border-0" name="title" required placeholder="Contoh: Makalah Biologi Bab 2">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select bg-light border-0" name="subject_id" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Kelas Tujuan <span class="text-danger">*</span></label>
                            <select class="form-select bg-light border-0" name="class_id" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Tenggat Waktu (Due Date) <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control bg-light border-0" name="due_date" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Skor Maksimal</label>
                            <input type="number" class="form-control bg-light border-0" name="max_score" value="100" min="0" max="100">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Instruksi Tugas <span class="text-danger">*</span></label>
                            <textarea class="form-control bg-light border-0" name="description" rows="4" required placeholder="Jelaskan detail apa yang harus dikerjakan siswa..."></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Lampiran Berkas / Foto (Opsional)</label>
                            <input class="form-control bg-light border-0" type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.jpg,.jpeg,.png">
                            <div class="form-text mt-1" style="font-size: 0.75rem;"><i class="bi bi-info-circle me-1"></i>Format yang didukung: PDF, Word, PPT, ZIP, Gambar. Maks: 10MB.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-medium text-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-medium shadow-sm transition-hover"><i class="bi bi-save me-2"></i>Simpan Tugas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endhasanyrole

<style>
    .transition-hover {
        transition: all 0.3s ease;
    }
    .material-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important;
    }
</style>
@endsection
