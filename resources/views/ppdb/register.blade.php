@extends('ppdb.layout')
@section('title', 'Form Pendaftaran PPDB')

@section('content')

<div class="container py-5" style="max-width: 860px;">
    {{-- Header --}}
    <div class="text-center mb-4">
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-2 fw-semibold" style="font-size:.8rem">
            <i class="bi bi-pencil-square me-1"></i>PENDAFTARAN BARU
        </span>
        <h1 class="fw-bold" style="font-size:1.8rem">Formulir Pendaftaran Peserta Didik Baru</h1>
        <p class="text-muted">Isi semua data dengan benar. Pastikan dokumen yang diupload jelas dan terbaca.</p>
    </div>

    {{-- Validation errors --}}
    @if($errors->any())
    <div class="alert alert-danger rounded-3 mb-4">
        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Ada kesalahan pada form:</div>
        <ul class="mb-0 ps-3 small">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('ppdb.register.store') }}" method="POST" enctype="multipart/form-data" id="ppdbForm">
        @csrf

        {{-- ── BAGIAN 1: PILIH GELOMBANG ── --}}
        <div class="ppdb-card card p-4 mb-4">
            <p class="form-section-title"><i class="bi bi-layers me-2"></i>Pilih Gelombang Pendaftaran</p>
            <div class="mb-0">
                <label class="form-label" for="wave_id">Gelombang <span class="text-danger">*</span></label>
                <select class="form-select @error('wave_id') is-invalid @enderror" id="wave_id" name="wave_id" required>
                    <option value="">— Pilih Gelombang —</option>
                    @foreach($activeWaves as $wave)
                        <option value="{{ $wave->id }}" {{ old('wave_id') == $wave->id ? 'selected' : '' }}>
                            {{ $wave->name }}
                            ({{ $wave->start_date->format('d M') }} – {{ $wave->end_date->format('d M Y') }})
                            | Sisa Kuota: {{ $wave->remainingQuota() }}
                            | {{ $wave->hasFee() ? 'Biaya: Rp '.number_format($wave->registration_fee,0,',','.') : 'Gratis' }}
                        </option>
                    @endforeach
                </select>
                @error('wave_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- ── BAGIAN 2: DATA PRIBADI CALON SISWA ── --}}
        <div class="ppdb-card card p-4 mb-4">
            <p class="form-section-title"><i class="bi bi-person me-2"></i>Data Pribadi Calon Siswa</p>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label" for="full_name">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                           id="full_name" name="full_name" value="{{ old('full_name') }}"
                           placeholder="Sesuai ijazah/akta kelahiran" required>
                    @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="nisn">NISN</label>
                    <input type="text" class="form-control @error('nisn') is-invalid @enderror"
                           id="nisn" name="nisn" value="{{ old('nisn') }}"
                           placeholder="10 digit (opsional)" maxlength="10">
                    @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="birth_place">Tempat Lahir <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('birth_place') is-invalid @enderror"
                           id="birth_place" name="birth_place" value="{{ old('birth_place') }}"
                           placeholder="Kota/Kabupaten" required>
                    @error('birth_place')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="birth_date">
                        Tanggal Lahir <span class="text-danger">*</span>
                        <small class="text-muted fw-normal">(digunakan untuk login ulang)</small>
                    </label>
                    <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                           id="birth_date" name="birth_date" value="{{ old('birth_date') }}"
                           max="{{ date('Y-m-d', strtotime('-5 years')) }}" required>
                    @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="gender">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select class="form-select @error('gender') is-invalid @enderror"
                            id="gender" name="gender" required>
                        <option value="">— Pilih —</option>
                        <option value="laki-laki"  {{ old('gender') === 'laki-laki'  ? 'selected' : '' }}>Laki-laki</option>
                        <option value="perempuan"  {{ old('gender') === 'perempuan'  ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label" for="address">Alamat Lengkap <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('address') is-invalid @enderror"
                              id="address" name="address" rows="2"
                              placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota" required>{{ old('address') }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- ── BAGIAN 3: DATA ORANG TUA / WALI ── --}}
        <div class="ppdb-card card p-4 mb-4">
            <p class="form-section-title"><i class="bi bi-people me-2"></i>Data Orang Tua / Wali</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="parent_name">Nama Orang Tua / Wali <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('parent_name') is-invalid @enderror"
                           id="parent_name" name="parent_name" value="{{ old('parent_name') }}"
                           placeholder="Nama lengkap orang tua/wali" required>
                    @error('parent_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="parent_phone">No. HP Orang Tua / Wali <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control @error('parent_phone') is-invalid @enderror"
                           id="parent_phone" name="parent_phone" value="{{ old('parent_phone') }}"
                           placeholder="08xxxxxxxxxx" required>
                    @error('parent_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email <small class="text-muted fw-normal">(opsional, untuk notifikasi)</small></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}"
                           placeholder="email@contoh.com">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>


        {{-- Submit --}}
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('ppdb.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <button type="submit" class="btn btn-ppdb-primary px-5" id="submitBtn">
                <i class="bi bi-send-fill me-2"></i>Kirim Pendaftaran
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>

/**
 * Loading state saat form di-submit.
 */
document.getElementById('ppdbForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
    btn.disabled  = true;
});
</script>
@endpush
