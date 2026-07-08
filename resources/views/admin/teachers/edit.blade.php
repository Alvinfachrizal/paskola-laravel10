@extends('layouts.app-bootstrap')

@section('header')
    <h2 class="h4 mb-0 text-dark fw-bold">{{ __('Edit Data Guru') }}</h2>
@endsection

@section('header_action')
    <a href="{{ route('admin.teachers.index') }}" class="btn btn-light text-secondary border d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('admin.teachers.update', $teacher->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <h5 class="mb-3 text-primary border-bottom pb-2">Informasi Akun (Login)</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $teacher->user->email ?? '') }}" required placeholder="Contoh: guru@sekolah.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <small class="text-muted">(Kosongkan jika tidak ingin diubah)</small></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h5 class="mb-3 mt-4 text-primary border-bottom pb-2">Data Pribadi & Profil Guru</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $teacher->name) }}" required placeholder="Contoh: Budi Santoso, S.Pd.">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nip" class="form-label">NIP / NUPTK (Opsional)</label>
                            <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" name="nip" value="{{ old('nip', $teacher->nip) }}" placeholder="Contoh: 198001012005011001">
                            @error('nip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                <option value="">-- Pilih --</option>
                                <option value="L" {{ old('gender', $teacher->gender) == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                                <option value="P" {{ old('gender', $teacher->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">No. Telepon / WhatsApp (Opsional)</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $teacher->phone) }}" placeholder="Contoh: 081234567890">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subject_specialty" class="form-label">Spesialisasi Mata Pelajaran (Opsional)</label>
                            <input type="text" class="form-control @error('subject_specialty') is-invalid @enderror" id="subject_specialty" name="subject_specialty" value="{{ old('subject_specialty', $teacher->subject_specialty) }}" placeholder="Contoh: Matematika">
                            @error('subject_specialty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status Guru <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $teacher->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status', $teacher->status) == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                <option value="retired" {{ old('status', $teacher->status) == 'retired' ? 'selected' : '' }}>Pensiun / Keluar</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Update Data Guru</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
