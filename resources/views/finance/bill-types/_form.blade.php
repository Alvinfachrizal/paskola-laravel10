{{--
    Partial form untuk Tambah & Edit Jenis Tagihan.
    Dipanggil dari modal di index.blade.php.
    $billType = null  → mode tambah (form kosong)
    $billType = object → mode edit (form terisi)
--}}

<div class="mb-3">
    <label class="form-label fw-semibold">Nama Tagihan <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
        value="{{ old('name', $billType?->name) }}"
        placeholder="Contoh: SPP, Uang Gedung, Seragam" required>
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Deskripsi <span class="text-muted fw-normal">(opsional)</span></label>
    <textarea name="description" class="form-control rounded-3" rows="2"
        placeholder="Keterangan singkat tentang jenis tagihan ini">{{ old('description', $billType?->description) }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Nominal Default (Rp) <span class="text-danger">*</span></label>
    <div class="input-group">
        <span class="input-group-text">Rp</span>
        <input type="number" name="default_amount" class="form-control rounded-end-3 @error('default_amount') is-invalid @enderror"
            value="{{ old('default_amount', $billType?->default_amount ?? 0) }}"
            min="0" step="1000" placeholder="350000">
    </div>
    <div class="form-text">Nominal ini bisa di-override per siswa saat membuat tagihan manual.</div>
    @error('default_amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="form-check form-switch ps-0">
            <div class="d-flex align-items-center gap-3 p-3 rounded-3 border bg-light">
                <input class="form-check-input ms-0 fs-5" type="checkbox" name="is_recurring" id="is_recurring_{{ $billType?->id ?? 'new' }}"
                    value="1" {{ old('is_recurring', $billType?->is_recurring) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_recurring_{{ $billType?->id ?? 'new' }}">
                    <div class="fw-semibold"><i class="bi bi-arrow-repeat text-success me-1"></i>Tagihan Berulang</div>
                    <div class="text-muted small">Centang jika tagihan ini harus digenerate tiap bulan (contoh: SPP, Uang Komite)</div>
                </label>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check form-switch ps-0">
            <div class="d-flex align-items-center gap-3 p-3 rounded-3 border bg-light">
                <input class="form-check-input ms-0 fs-5" type="checkbox" name="is_initial_bill" id="is_initial_{{ $billType?->id ?? 'new' }}"
                    value="1" {{ old('is_initial_bill', $billType?->is_initial_bill) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_initial_{{ $billType?->id ?? 'new' }}">
                    <div class="fw-semibold"><i class="bi bi-door-open text-info me-1"></i>Tagihan Awal Masuk</div>
                    <div class="text-muted small">Centang jika tagihan ini otomatis dibuat saat siswa baru mendaftar (contoh: Uang Gedung, Seragam)</div>
                </label>
            </div>
        </div>
    </div>
</div>

<div class="form-check form-switch ps-0">
    <div class="d-flex align-items-center gap-3 p-3 rounded-3 border">
        <input class="form-check-input ms-0 fs-5" type="checkbox" name="is_active" id="is_active_{{ $billType?->id ?? 'new' }}"
            value="1" {{ old('is_active', $billType ? $billType->is_active : true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active_{{ $billType?->id ?? 'new' }}">
            <div class="fw-semibold"><i class="bi bi-toggle-on text-primary me-1"></i>Aktif</div>
            <div class="text-muted small">Jenis tagihan yang nonaktif tidak bisa digunakan untuk membuat tagihan baru</div>
        </label>
    </div>
</div>
