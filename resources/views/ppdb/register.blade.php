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

        {{-- ── BAGIAN 4: DATA SERAGAM ── --}}
        <div class="ppdb-card card p-4 mb-4">
            <p class="form-section-title"><i class="bi bi-bag me-2"></i>Data Kebutuhan Seragam</p>
            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Data ini digunakan panitia untuk memesan seragam sebelum tahun ajaran dimulai.
                Satu ukuran berlaku untuk semua jenis seragam (olahraga, OSIS, Pramuka).
            </p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="ukuran">Ukuran Seragam <span class="text-danger">*</span></label>
                    <select class="form-select @error('ukuran') is-invalid @enderror" id="ukuran" name="ukuran" required>
                        <option value="">— Pilih Ukuran —</option>
                        @foreach($ukuranList as $uk)
                            <option value="{{ $uk }}" {{ old('ukuran') === $uk ? 'selected' : '' }}>{{ $uk }}</option>
                        @endforeach
                    </select>
                    @error('ukuran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Field kondisional untuk perempuan — ditampilkan via JS --}}
                <div class="col-md-4" id="field_kerudung" style="display:none">
                    <label class="form-label" for="pakai_kerudung">Memakai Kerudung? <span class="text-danger">*</span></label>
                    <select class="form-select @error('pakai_kerudung') is-invalid @enderror"
                            id="pakai_kerudung" name="pakai_kerudung">
                        <option value="">— Pilih —</option>
                        <option value="ya"    {{ old('pakai_kerudung') === 'ya'    ? 'selected' : '' }}>Ya, Memakai Kerudung</option>
                        <option value="tidak" {{ old('pakai_kerudung') === 'tidak' ? 'selected' : '' }}>Tidak Memakai Kerudung</option>
                    </select>
                    @error('pakai_kerudung')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4" id="field_bawahan" style="display:none">
                    <label class="form-label" for="jenis_bawahan">Jenis Bawahan <span class="text-danger">*</span></label>
                    <select class="form-select @error('jenis_bawahan') is-invalid @enderror"
                            id="jenis_bawahan" name="jenis_bawahan">
                        <option value="">— Pilih —</option>
                        <option value="rok"    {{ old('jenis_bawahan') === 'rok'    ? 'selected' : '' }}>Rok</option>
                        <option value="celana" {{ old('jenis_bawahan') === 'celana' ? 'selected' : '' }}>Celana</option>
                    </select>
                    @error('jenis_bawahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Catatan untuk laki-laki --}}
                <div class="col-12" id="info_lakilaki" style="display:none">
                    <div class="alert alert-info py-2 px-3 small rounded-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Untuk pendaftar laki-laki, jenis bawahan otomatis <strong>Celana</strong>.
                    </div>
                </div>
            </div>
        </div>

        {{-- ── BAGIAN 5: UPLOAD DOKUMEN ── --}}
        <div class="ppdb-card card p-4 mb-4">
            <p class="form-section-title"><i class="bi bi-file-earmark-arrow-up me-2"></i>Upload Dokumen Persyaratan</p>
            <p class="text-muted small mb-4">
                <i class="bi bi-info-circle me-1"></i>
                Format yang diterima: <strong>JPG, JPEG, PNG, PDF</strong>. Ukuran maksimal: <strong>2 MB</strong> per file.
                Pastikan dokumen jelas, tidak buram, dan tidak terpotong.
            </p>
            <div class="row g-3">
                @foreach($docTypes as $key => $label)
                <div class="col-md-6">
                    <label class="form-label" for="dok_{{ $key }}">
                        {{ $label }} <span class="text-danger">*</span>
                    </label>
                    <div class="upload-zone position-relative" id="zone_{{ $key }}">
                        <input type="file" class="form-control @error('dokumen.'.$key) is-invalid @enderror"
                               id="dok_{{ $key }}" name="dokumen[{{ $key }}]"
                               accept=".jpg,.jpeg,.png,.pdf" required
                               style="position:absolute;width:100%;height:100%;top:0;left:0;opacity:0;cursor:pointer;z-index:2"
                               onchange="updateUploadZone('{{ $key }}', this)">
                        <div id="zone_label_{{ $key }}">
                            <i class="bi bi-cloud-arrow-up fs-3 text-muted mb-1 d-block"></i>
                            <p class="mb-0 small text-muted">Klik atau seret file ke sini</p>
                        </div>
                    </div>
                    @error('dokumen.'.$key)
                        <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>
                @endforeach
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
 * Show/hide field seragam berdasarkan gender yang dipilih.
 * Tidak butuh library — JavaScript vanilla sederhana.
 */
const genderSelect     = document.getElementById('gender');
const fieldKerudung    = document.getElementById('field_kerudung');
const fieldBawahan     = document.getElementById('field_bawahan');
const infoLakilaki     = document.getElementById('info_lakilaki');
const selectKerudung   = document.getElementById('pakai_kerudung');
const selectBawahan    = document.getElementById('jenis_bawahan');

function toggleSeragamFields() {
    const gender = genderSelect.value;

    if (gender === 'perempuan') {
        fieldKerudung.style.display = '';
        fieldBawahan.style.display  = '';
        infoLakilaki.style.display  = 'none';
        // Jadikan required
        selectKerudung.required = true;
        selectBawahan.required  = true;
    } else if (gender === 'laki-laki') {
        fieldKerudung.style.display = 'none';
        fieldBawahan.style.display  = 'none';
        infoLakilaki.style.display  = '';
        // Hapus required & reset value (backend handle otomatis)
        selectKerudung.required = false;
        selectBawahan.required  = false;
        selectKerudung.value    = '';
        selectBawahan.value     = '';
    } else {
        fieldKerudung.style.display = 'none';
        fieldBawahan.style.display  = 'none';
        infoLakilaki.style.display  = 'none';
    }
}

genderSelect.addEventListener('change', toggleSeragamFields);

// Jalankan saat halaman load (untuk kasus old() value setelah validation error)
toggleSeragamFields();

/**
 * Update tampilan upload zone setelah file dipilih.
 */
function updateUploadZone(key, input) {
    const zone  = document.getElementById('zone_' + key);
    const label = document.getElementById('zone_label_' + key);
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        zone.style.borderColor  = '#0e9f6e';
        zone.style.background   = '#f0fdf4';
        label.innerHTML = `
            <i class="bi bi-check-circle-fill text-success fs-3 mb-1 d-block"></i>
            <p class="mb-0 small text-success fw-semibold">${file.name}</p>
            <p class="mb-0 text-muted" style="font-size:.75rem">${sizeMB} MB</p>
        `;
    }
}

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
