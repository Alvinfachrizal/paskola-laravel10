@extends('layouts.app-bootstrap')
@section('title', 'Pengaturan Pembayaran PPDB')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Pengaturan PPDB</h2>
        <p class="text-muted mb-0 small">Atur informasi rekening dan QRIS pendaftaran PPDB</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.ppdb.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.ppdb.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <h5 class="fw-bold mb-4"><i class="bi bi-bank text-primary me-2"></i>Rekening Pembayaran Manual</h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" name="ppdb_bank_name" class="form-control rounded-3" 
                                   value="{{ old('ppdb_bank_name', $settings['ppdb_bank_name'] ?? 'Bank BSI') }}" 
                                   placeholder="Contoh: Bank BSI" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor Rekening <span class="text-danger">*</span></label>
                            <input type="text" name="ppdb_bank_account" class="form-control rounded-3" 
                                   value="{{ old('ppdb_bank_account', $settings['ppdb_bank_account'] ?? '7123 4567 89') }}" 
                                   placeholder="Contoh: 7123 4567 89" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Atas Nama (Pemilik Rekening) <span class="text-danger">*</span></label>
                            <input type="text" name="ppdb_bank_owner" class="form-control rounded-3" 
                                   value="{{ old('ppdb_bank_owner', $settings['ppdb_bank_owner'] ?? 'Yayasan Pendidikan Paskola') }}" 
                                   placeholder="Contoh: Yayasan Pendidikan Paskola" required>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="fw-bold mb-3"><i class="bi bi-qr-code text-success me-2"></i>Barcode QRIS</h5>
                    <p class="text-muted small mb-3">Upload gambar kode QRIS (opsional) jika sekolah mendukung pembayaran e-Wallet.</p>

                    <div class="row g-3 align-items-center">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Upload Gambar QRIS</label>
                            <input type="file" name="ppdb_qris" class="form-control rounded-3" accept="image/png, image/jpeg">
                            <small class="text-muted mt-1 d-block">Format JPG/PNG, Maksimal 2MB. Kosongkan jika tidak ingin mengubah.</small>
                        </div>
                        <div class="col-md-4 text-center">
                            @if(!empty($settings['ppdb_qris_path']))
                                <img src="{{ Storage::url($settings['ppdb_qris_path']) }}" class="img-thumbnail rounded-3 shadow-sm" style="max-height: 150px; object-fit: contain;">
                            @else
                                <div class="bg-light border rounded-3 d-flex align-items-center justify-content-center text-muted small mx-auto" style="height: 150px; width: 150px;">
                                    Belum ada QRIS
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="submit" class="btn btn-primary px-4 rounded-3">
                            <i class="bi bi-save me-1"></i>Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
