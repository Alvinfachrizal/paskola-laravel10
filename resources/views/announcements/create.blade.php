@extends('layouts.app-bootstrap')

@section('title', 'Buat Pengumuman')

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
    .content-textarea {
        min-height: 200px;
        resize: vertical;
        font-family: inherit;
        font-size: 0.9375rem;
        line-height: 1.6;
    }
</style>
@endsection

@section('header')
    <h2><i class="bi bi-megaphone me-2 text-primary"></i>Buat Pengumuman</h2>
    <p>Buat pengumuman baru untuk siswa, guru, atau semua warga sekolah</p>
@endsection

@section('header_action')
    <a href="{{ route('announcements.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">

        <form action="{{ route('announcements.store') }}" method="POST">
            @csrf

            {{-- Judul & Konten --}}
            <div class="form-section">
                <h6><i class="bi bi-card-text me-2 text-primary"></i>Isi Pengumuman</h6>

                <div class="mb-4">
                    <label class="form-label fw-semibold" for="title">Judul <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control form-control-lg @error('title') is-invalid @enderror"
                           id="title" name="title"
                           value="{{ old('title') }}"
                           placeholder="Judul pengumuman yang jelas dan singkat"
                           required autofocus>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="content">Isi Pengumuman <span class="text-danger">*</span></label>
                    <textarea class="form-control content-textarea @error('content') is-invalid @enderror"
                              id="content" name="content"
                              placeholder="Tulis isi pengumuman secara lengkap..."
                              required>{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Target Penerima --}}
            <div class="form-section">
                <h6><i class="bi bi-people me-2 text-primary"></i>Target Penerima</h6>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Kirim ke Role <small class="text-muted fw-normal">(kosongkan = semua role)</small></label>
                    <div class="role-checkbox-grid">
                        @foreach($roles as $role)
                        <label class="role-check-item">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input m-0" name="target_roles[]" value="{{ $role }}"
                                    {{ in_array($role, old('target_roles', [])) ? 'checked' : '' }}>
                                <span class="small fw-medium">{{ $role }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Spesifik Kelas <small class="text-muted fw-normal">(opsional, kosongkan = semua kelas)</small></label>
                    <select class="form-select" name="target_class_ids[]" multiple size="5">
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}"
                                {{ in_array($class->id, old('target_class_ids', [])) ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Tahan Ctrl untuk memilih beberapa kelas</div>
                </div>
            </div>

            {{-- Pengaturan --}}
            <div class="form-section">
                <h6><i class="bi bi-gear me-2 text-primary"></i>Pengaturan</h6>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold" for="priority">Prioritas</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>⚠️ Penting / Urgent</option>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold" for="expires_at">Berakhir pada <small class="text-muted fw-normal">(opsional)</small></label>
                        <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror"
                               id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                        @error('expires_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1"
                           {{ old('is_pinned') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_pinned">
                        <i class="bi bi-pin-fill text-warning me-1"></i>
                        Sematkan pengumuman ini di bagian atas
                    </label>
                </div>
            </div>

            {{-- Action --}}
            <div class="d-flex flex-wrap gap-3 justify-content-end">
                <button type="submit" name="publish_now" value="0" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-save me-2"></i>Simpan sebagai Draft
                </button>
                <button type="submit" name="publish_now" value="1" class="btn btn-primary px-4">
                    <i class="bi bi-send me-2"></i>Publish & Kirim Notifikasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

