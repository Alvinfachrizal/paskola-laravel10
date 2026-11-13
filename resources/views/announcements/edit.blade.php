@extends('layouts.app-bootstrap')

@section('title', 'Edit Pengumuman')

@section('styles')
<style>
    .form-section {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .form-section h6 {
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }
    .role-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 0.5rem;
    }
    .role-check-item {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: all 0.15s;
    }
    .role-check-item:has(input:checked) {
        background: var(--primary-bg);
        border-color: var(--primary-color);
    }
    .content-textarea { min-height: 200px; resize: vertical; font-size: 0.9375rem; line-height: 1.6; }
</style>
@endsection

@section('header')
    <h2><i class="bi bi-pencil me-2 text-primary"></i>Edit Pengumuman</h2>
@endsection

@section('header_action')
    <a href="{{ route('announcements.show', $announcement->id) }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">

        @if($announcement->isPublished())
            <div class="alert alert-warning rounded-4 mb-4">
                <i class="bi bi-info-circle me-1"></i>
                Pengumuman ini sudah dipublikasikan. Edit akan mengubah konten yang terlihat pengguna.
            </div>
        @endif

        <form action="{{ route('announcements.update', $announcement->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-section">
                <h6><i class="bi bi-card-text me-2 text-primary"></i>Isi Pengumuman</h6>

                <div class="mb-4">
                    <label class="form-label fw-semibold" for="title">Judul <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror"
                           id="title" name="title"
                           value="{{ old('title', $announcement->title) }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="content">Isi Pengumuman <span class="text-danger">*</span></label>
                    <textarea class="form-control content-textarea @error('content') is-invalid @enderror"
                              id="content" name="content" required>{{ old('content', $announcement->content) }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-section">
                <h6><i class="bi bi-people me-2 text-primary"></i>Target Penerima</h6>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Kirim ke Role</label>
                    <div class="role-checkbox-grid">
                        @foreach($roles as $role)
                        <label class="role-check-item">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input m-0" name="target_roles[]" value="{{ $role }}"
                                    {{ in_array($role, old('target_roles', $announcement->target_roles ?? [])) ? 'checked' : '' }}>
                                <span class="small fw-medium">{{ $role }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Spesifik Kelas</label>
                    <select class="form-select" name="target_class_ids[]" multiple size="5">
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}"
                                {{ in_array($class->id, old('target_class_ids', $announcement->target_class_ids ?? [])) ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h6><i class="bi bi-gear me-2 text-primary"></i>Pengaturan</h6>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold" for="priority">Prioritas</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="normal" {{ old('priority', $announcement->priority) === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="urgent" {{ old('priority', $announcement->priority) === 'urgent' ? 'selected' : '' }}>⚠️ Penting / Urgent</option>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold" for="expires_at">Berakhir pada</label>
                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at"
                               value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1"
                           {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_pinned">
                        <i class="bi bi-pin-fill text-warning me-1"></i>Sematkan pengumuman ini
                    </label>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-3 justify-content-end">
                <a href="{{ route('announcements.show', $announcement->id) }}" class="btn btn-outline-secondary px-4">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

