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
                            <select class="form-select @error('subject_specialty') is-invalid @enderror" id="subject_specialty" name="subject_specialty">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->name }}" {{ old('subject_specialty', $teacher->subject_specialty) == $subject->name ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
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

                    {{-- Alamat --}}
                    <h5 class="mb-3 mt-4 text-primary border-bottom pb-2">Alamat</h5>
                    @php
                        $sameAddr = old('same_address', $teacher->address_ktp && $teacher->address_ktp === $teacher->address_domicile);
                    @endphp

                    <div class="mb-2">
                        <label for="address_ktp" class="form-label">
                            Alamat KTP <span class="badge bg-secondary ms-1" style="font-size:0.7rem;">Sesuai KTP</span>
                        </label>
                        <textarea class="form-control @error('address_ktp') is-invalid @enderror"
                            id="address_ktp" name="address_ktp" rows="3"
                            placeholder="Jl. Sudirman No. 123, RT 01/RW 02, Kel. Menteng...">{{ old('address_ktp', $teacher->address_ktp) }}</textarea>
                        @error('address_ktp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="same_address" name="same_address" value="1"
                            {{ $sameAddr ? 'checked' : '' }}
                            onchange="toggleDomicile(this)">
                        <label class="form-check-label text-muted" for="same_address" style="cursor:pointer;">
                            <i class="bi bi-clipboard2-check me-1 text-primary"></i>Alamat domisili <strong>sama</strong> dengan alamat KTP
                        </label>
                    </div>

                    <div id="domicile_section" style="{{ $sameAddr ? 'display:none;' : '' }}">
                        <label for="address_domicile" class="form-label">
                            Alamat Domisili <span class="badge bg-info text-dark ms-1" style="font-size:0.7rem;">Saat ini tinggal di</span>
                        </label>
                        <textarea class="form-control @error('address_domicile') is-invalid @enderror"
                            id="address_domicile" name="address_domicile" rows="3"
                            placeholder="Jl. Kebon Jeruk No. 45...">{{ old('address_domicile', $teacher->address_domicile) }}</textarea>
                        @error('address_domicile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Update Data Guru</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDomicile(checkbox) {
    const section = document.getElementById('domicile_section');
    if (checkbox.checked) {
        section.style.display = 'none';
        document.getElementById('address_domicile').value = '';
    } else {
        section.style.display = '';
        document.getElementById('address_domicile').focus();
    }
}
</script>

@endsection
