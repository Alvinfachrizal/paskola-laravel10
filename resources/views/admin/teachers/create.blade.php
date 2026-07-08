@extends('layouts.app-bootstrap')

@section('header')
    <h2 class="h3 mb-1 text-dark fw-bold" style="letter-spacing:-0.5px;">Tambah Data Guru</h2>
    <p class="text-muted mb-0" style="font-size:0.875rem;">Masukkan informasi detail guru dan akun sistem</p>
@endsection

@section('header_action')
    <a href="{{ route('admin.teachers.index') }}" class="btn btn-light text-secondary border d-flex align-items-center gap-2 rounded-pill shadow-sm transition-hover">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
<form action="{{ route('admin.teachers.store') }}" method="POST">
    @csrf
    
    <div class="row g-4">
        <!-- Kolom Kiri -->
        <div class="col-lg-6 d-flex flex-column gap-4">
            
            <!-- Card 1: Informasi Akun -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark mb-0" style="font-size:1.1rem;">Informasi Akun</h5>
                    <p class="text-muted mb-3" style="font-size:0.85rem;">Kredensial untuk login ke sistem</p>
                    <hr class="mt-0 mb-4 bg-light">
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <div class="mb-3">
                        <label for="email" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Email (Username) <span class="text-danger">*</span></label>
                        <input type="email" class="form-control form-control-lg bg-light border-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="guru@sekolah.com" style="font-size:0.95rem;">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Password Sementara <span class="text-danger">*</span></label>
                        <input type="password" class="form-control form-control-lg bg-light border-0 @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Minimal 8 karakter" style="font-size:0.95rem;">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="nip" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">NIP / NUPTK</label>
                        <input type="text" class="form-control form-control-lg bg-light border-0 @error('nip') is-invalid @enderror" id="nip" name="nip" value="{{ old('nip') }}" placeholder="198001012005011001" style="font-size:0.95rem;">
                        @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Card 2: Data Akademik -->
            <div class="card border-0 shadow-sm rounded-4 flex-grow-1">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark mb-0" style="font-size:1.1rem;">Data Kepegawaian</h5>
                    <p class="text-muted mb-3" style="font-size:0.85rem;">Informasi tugas dan jabatan guru</p>
                    <hr class="mt-0 mb-4 bg-light">
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="employment_type" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Jenis Pegawai</label>
                            <select class="form-select form-select-lg bg-light border-0 @error('employment_type') is-invalid @enderror" id="employment_type" name="employment_type" style="font-size:0.95rem;">
                                <option value="">-- Pilih --</option>
                                <option value="PNS" {{ old('employment_type') == 'PNS' ? 'selected' : '' }}>PNS</option>
                                <option value="Honorer" {{ old('employment_type') == 'Honorer' ? 'selected' : '' }}>Honorer</option>
                                <option value="Tetap Yayasan" {{ old('employment_type') == 'Tetap Yayasan' ? 'selected' : '' }}>Tetap Yayasan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Status Guru <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg bg-light border-0 @error('status') is-invalid @enderror" id="status" name="status" required style="font-size:0.95rem;">
                                <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="subject_specialty" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Spesialisasi Mata Pelajaran</label>
                        <input type="text" class="form-control form-control-lg bg-light border-0 @error('subject_specialty') is-invalid @enderror" id="subject_specialty" name="subject_specialty" value="{{ old('subject_specialty') }}" placeholder="Contoh: Matematika" style="font-size:0.95rem;">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="last_education" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Pendidikan Terakhir</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0 @error('last_education') is-invalid @enderror" id="last_education" name="last_education" value="{{ old('last_education') }}" placeholder="S1 Pendidikan Matematika" style="font-size:0.95rem;">
                        </div>
                        <div class="col-md-6">
                            <label for="join_date" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Tanggal Bergabung</label>
                            <input type="date" class="form-control form-control-lg bg-light border-0 @error('join_date') is-invalid @enderror" id="join_date" name="join_date" value="{{ old('join_date') }}" style="font-size:0.95rem;">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan -->
        <div class="col-lg-6 d-flex flex-column gap-4">
            
            <!-- Card 3: Data Pribadi -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark mb-0" style="font-size:1.1rem;">Data Pribadi</h5>
                    <p class="text-muted mb-3" style="font-size:0.85rem;">Informasi biodata lengkap</p>
                    <hr class="mt-0 mb-4 bg-light">
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <div class="mb-3">
                        <label for="name" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Budi Santoso, S.Pd." style="font-size:0.95rem;">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="gender" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg bg-light border-0 @error('gender') is-invalid @enderror" id="gender" name="gender" required style="font-size:0.95rem;">
                                <option value="">-- Pilih --</option>
                                <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                                <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="religion" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Agama</label>
                            <select class="form-select form-select-lg bg-light border-0 @error('religion') is-invalid @enderror" id="religion" name="religion" style="font-size:0.95rem;">
                                <option value="">-- Pilih --</option>
                                <option value="Islam" {{ old('religion') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('religion') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Katolik" {{ old('religion') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="Hindu" {{ old('religion') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('religion') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="birth_place" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Tempat Lahir</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0 @error('birth_place') is-invalid @enderror" id="birth_place" name="birth_place" value="{{ old('birth_place') }}" placeholder="Jakarta" style="font-size:0.95rem;">
                        </div>
                        <div class="col-md-6">
                            <label for="birth_date" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Tanggal Lahir</label>
                            <input type="date" class="form-control form-control-lg bg-light border-0 @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" style="font-size:0.95rem;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Kontak & Alamat -->
            <div class="card border-0 shadow-sm rounded-4 flex-grow-1">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark mb-0" style="font-size:1.1rem;">Kontak & Alamat</h5>
                    <p class="text-muted mb-3" style="font-size:0.85rem;">Informasi domisili dan komunikasi</p>
                    <hr class="mt-0 mb-4 bg-light">
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <div class="mb-3">
                        <label for="phone" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">No. HP / WhatsApp</label>
                        <input type="text" class="form-control form-control-lg bg-light border-0 @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="081234567890" style="font-size:0.95rem;">
                    </div>
                    <div>
                        <label for="address" class="form-label text-muted fw-semibold" style="font-size:0.85rem;">Alamat Lengkap</label>
                        <textarea class="form-control bg-light border-0 @error('address') is-invalid @enderror" id="address" name="address" rows="3" placeholder="Jl. Sudirman No. 123..." style="font-size:0.95rem; resize:none;">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.teachers.index') }}" class="btn btn-light px-4 py-2 rounded-pill fw-medium text-secondary">Batal</a>
            <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-medium shadow-sm transition-hover">
                <i class="bi bi-save me-2"></i>Simpan Data Guru
            </button>
        </div>
    </div>
</form>

<style>
    .transition-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .transition-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    }
    .form-control:focus, .form-select:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        border: 1px solid #0d6efd !important;
    }
    hr {
        border-top: 2px solid #e2e8f0;
        opacity: 1;
    }
</style>
@endsection
