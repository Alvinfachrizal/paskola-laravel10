@extends('layouts.app-bootstrap')

@section('header')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h3 mb-1 text-dark fw-bold" style="letter-spacing:-0.5px;">Materi Pembelajaran</h2>
            <p class="text-muted mb-0" style="font-size:0.875rem;">Akses dan kelola modul pembelajaran digital</p>
        </div>
        <div class="d-grid d-sm-block">
            @hasanyrole('Super Admin|Admin|Guru')
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-2 shadow-sm transition-hover fw-medium d-flex align-items-center justify-content-center gap-1" style="font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#uploadMateriModal">
                <i class="bi bi-cloud-arrow-up"></i> Unggah Materi
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

<!-- Filter / Search Bar -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-3 p-md-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 12px 0 0 12px;"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Cari judul materi atau mata pelajaran..." style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select bg-light border-0" style="border-radius: 12px;">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select bg-light border-0" style="border-radius: 12px;">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-primary rounded-3"><i class="bi bi-funnel"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Material Cards Grid -->
<div class="row g-4">
    @forelse($materials as $material)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 rounded-4 shadow-sm h-100 material-card transition-hover position-relative overflow-hidden">
                <!-- Top Decoration Line -->
                <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #3b82f6, #8b5cf6);"></div>
                
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center
                                {{ $material->type === 'document' ? 'bg-primary bg-opacity-10 text-primary' : ($material->type === 'video' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success') }}" 
                                style="width: 40px; height: 40px;">
                                <i class="bi {{ $material->type === 'document' ? 'bi-file-earmark-pdf' : ($material->type === 'video' ? 'bi-play-btn' : 'bi-link-45deg') }} fs-5"></i>
                            </div>
                            <div>
                                <span class="badge bg-light text-dark border fw-medium mb-1">{{ $material->subject->name ?? 'Umum' }}</span>
                                <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i>{{ $material->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        
                        <!-- Dropdown Menu for Edit/Delete (Only for owner/admin) -->
                        @if(Auth::id() === $material->teacher_id || Auth::user()->hasRole(['Super Admin', 'Admin']))
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-circle text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px;">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3 text-sm">
                                <li><a class="dropdown-item py-2" href="#"><i class="bi bi-pencil me-2 text-primary"></i> Edit</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('admin.lms-materials.destroy', $material->id) }}" method="POST" onsubmit="return confirm('Hapus materi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-trash me-2"></i> Hapus</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        @endif
                    </div>
                    
                    <h5 class="fw-bold text-dark mb-2" style="font-size: 1.1rem; line-height: 1.4;">{{ $material->title }}</h5>
                    <p class="text-muted mb-4" style="font-size: 0.85rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ $material->description ?: 'Tidak ada deskripsi.' }}
                    </p>
                    
                    <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex justify-content-center align-items-center" style="width: 28px; height: 28px; font-weight:600; font-size:0.7rem;">
                                {{ substr($material->teacher->name ?? 'G', 0, 1) }}
                            </div>
                            <small class="text-muted fw-medium">{{ explode(' ', $material->teacher->name ?? 'Guru')[0] }}</small>
                            <span class="text-muted mx-1">•</span>
                            <small class="text-muted"><i class="bi bi-people me-1"></i>{{ $material->schoolClass->name ?? 'Semua Kelas' }}</small>
                        </div>
                        
                        <a href="{{ $material->file_url ?? '#' }}" target="_blank" class="btn btn-sm rounded-pill
                            {{ $material->type === 'document' ? 'btn-outline-primary' : ($material->type === 'video' ? 'btn-outline-danger' : 'btn-outline-success') }} px-3 fw-medium">
                            {{ $material->type === 'document' ? 'Unduh' : 'Buka' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3 text-muted" style="width: 80px; height: 80px;">
                <i class="bi bi-folder2-open fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark">Belum ada materi</h5>
            <p class="text-muted">Materi pembelajaran yang diunggah akan muncul di sini.</p>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $materials->links() }}
</div>

<!-- Modal Upload Materi -->
@hasanyrole('Super Admin|Admin|Guru')
<div class="modal fade" id="uploadMateriModal" tabindex="-1" aria-labelledby="uploadMateriModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-bottom-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="uploadMateriModalLabel" style="font-size: 1.1rem;"><i class="bi bi-cloud-arrow-up me-2 text-primary"></i>Unggah Materi Baru</h5>
                <button type="button" class="btn-close bg-light rounded-circle" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.lms-materials.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Judul Materi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light border-0" name="title" required placeholder="Contoh: Bab 1 - Pengenalan Sel">
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
                        
                        <div class="col-12">
                            <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Deskripsi Singkat</label>
                            <textarea class="form-control bg-light border-0" name="description" rows="3" placeholder="Jelaskan ringkasan materi ini..."></textarea>
                        </div>
                    </div>

                    <h6 class="fw-bold mt-4 mb-3 text-dark">File / Tautan Materi</h6>
                    <div class="card bg-light border-0 rounded-3 mb-4">
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Tipe Materi <span class="text-danger">*</span></label>
                                <select class="form-select border-0 shadow-sm" name="type" id="materialType" onchange="toggleMaterialInput()" required>
                                    <option value="document">Dokumen (PDF, Word, PPT)</option>
                                    <option value="video">Video URL (YouTube, dll)</option>
                                    <option value="link">Tautan Web / Artikel</option>
                                </select>
                            </div>
                            
                            <!-- File Input -->
                            <div id="fileInputContainer">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Unggah File</label>
                                <input class="form-control border-0 shadow-sm" type="file" name="file" id="fileInput" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip">
                                <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Maksimal ukuran file 10MB.</div>
                            </div>

                            <!-- Link Input (Hidden by default) -->
                            <div id="linkInputContainer" style="display: none;">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">URL / Tautan</label>
                                <input type="url" class="form-control border-0 shadow-sm" name="link_url" id="linkInput" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-medium text-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-medium shadow-sm transition-hover"><i class="bi bi-cloud-arrow-up me-2"></i>Unggah Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleMaterialInput() {
        const type = document.getElementById('materialType').value;
        const fileContainer = document.getElementById('fileInputContainer');
        const linkContainer = document.getElementById('linkInputContainer');
        const fileInput = document.getElementById('fileInput');
        const linkInput = document.getElementById('linkInput');

        if (type === 'document') {
            fileContainer.style.display = 'block';
            linkContainer.style.display = 'none';
            // fileInput.required = true; // Make required if you want
            linkInput.required = false;
        } else {
            fileContainer.style.display = 'none';
            linkContainer.style.display = 'block';
            fileInput.required = false;
            // linkInput.required = true;
        }
    }
</script>
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
