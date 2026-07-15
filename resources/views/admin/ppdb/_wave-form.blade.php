{{-- Partial form untuk modal tambah/edit gelombang --}}
<div class="row g-3">
    <div class="col-12">
        <label class="form-label small fw-semibold">Nama Gelombang <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control rounded-3"
               value="{{ old('name', $wave?->name) }}"
               placeholder="Contoh: Gelombang 1" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-semibold">Tanggal Buka <span class="text-danger">*</span></label>
        <input type="date" name="start_date" class="form-control rounded-3"
               value="{{ old('start_date', $wave?->start_date?->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-semibold">Tanggal Tutup <span class="text-danger">*</span></label>
        <input type="date" name="end_date" class="form-control rounded-3"
               value="{{ old('end_date', $wave?->end_date?->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-semibold">Kuota <span class="text-danger">*</span></label>
        <input type="number" name="quota" class="form-control rounded-3"
               value="{{ old('quota', $wave?->quota ?? 50) }}" min="1" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-semibold">Biaya Pendaftaran</label>
        <div class="input-group">
            <span class="input-group-text rounded-start-3">Rp</span>
            <input type="number" name="registration_fee" class="form-control rounded-end-3"
                   value="{{ old('registration_fee', $wave?->registration_fee ?? 0) }}"
                   min="0" step="1000" placeholder="0 = gratis">
        </div>
    </div>
    <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi</label>
        <textarea name="description" class="form-control rounded-3" rows="2"
                  placeholder="Informasi tambahan untuk calon pendaftar...">{{ old('description', $wave?->description) }}</textarea>
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                   {{ old('is_active', $wave?->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label small fw-semibold" for="is_active">Gelombang Aktif (terlihat di portal publik)</label>
        </div>
    </div>
</div>
