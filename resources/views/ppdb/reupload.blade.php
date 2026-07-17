@extends('ppdb.layout')
@section('title', 'Upload Ulang Dokumen — ' . $applicant->registration_code)

@section('content')
<div class="container py-5" style="max-width: 700px;">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('ppdb.status', $applicant->registration_code) }}"
           class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="fw-bold mb-0" style="font-size:1.6rem">Upload Ulang Dokumen</h1>
            <p class="text-muted mb-0 small">
                {{ $applicant->full_name }} &middot; {{ $applicant->registration_code }}
            </p>
        </div>
    </div>

    {{-- Alert instruksi --}}
    <div class="alert alert-warning rounded-3 mb-4">
        <div class="fw-semibold mb-1">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Dokumen Perlu Diperbaiki
        </div>
        <p class="small mb-0">
            Dokumen di bawah ini ditolak oleh panitia. Pastikan file yang Anda upload
            <strong>jelas, tidak buram, tidak terpotong</strong>, dan sesuai dengan yang diminta.
            Setelah upload ulang, panitia akan memverifikasi kembali dokumen Anda.
        </p>
    </div>

    {{-- Error --}}
    @if($errors->any())
    <div class="alert alert-danger rounded-3 mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ $errors->first() }}
    </div>
    @endif

    {{-- Form Upload Ulang --}}
    <form action="{{ route('ppdb.reupload.store', $applicant->registration_code) }}"
          method="POST" enctype="multipart/form-data" id="reuploadForm">
        @csrf

        @if($invalidDocs->isEmpty())
            <div class="ppdb-card card p-5 text-center text-muted">
                <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                <p class="mb-0">Tidak ada dokumen yang perlu diupload ulang.</p>
                <a href="{{ route('ppdb.status', $applicant->registration_code) }}"
                   class="btn btn-outline-primary rounded-3 mt-3">
                    Kembali ke Status
                </a>
            </div>
        @else
            <div class="d-flex flex-column gap-4">
                @foreach($invalidDocs as $doc)
                <div class="ppdb-card card p-4">
                    {{-- Header dokumen --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="fw-bold mb-1">{{ $doc->docTypeLabel() }}</h6>
                            <span class="badge bg-danger rounded-pill small">Ditolak</span>
                        </div>
                        <i class="bi bi-file-earmark-x text-danger fs-3"></i>
                    </div>

                    {{-- Alasan penolakan --}}
                    @if($doc->rejection_notes)
                    <div class="alert alert-danger py-2 px-3 rounded-3 mb-3" style="font-size:.85rem">
                        <i class="bi bi-chat-left-fill me-2"></i>
                        <strong>Catatan panitia:</strong> {{ $doc->rejection_notes }}
                    </div>
                    @endif

                    {{-- Upload zone --}}
                    <div class="upload-zone position-relative" id="zone_{{ $doc->id }}">
                        <input type="file"
                               name="dokumen[{{ $doc->id }}]"
                               id="dok_{{ $doc->id }}"
                               accept=".jpg,.jpeg,.png,.pdf"
                               required
                               style="position:absolute;width:100%;height:100%;top:0;left:0;opacity:0;cursor:pointer;z-index:2"
                               onchange="updateZone('{{ $doc->id }}', this)">
                        <div id="zone_label_{{ $doc->id }}">
                            <i class="bi bi-cloud-arrow-up fs-3 text-muted mb-1 d-block"></i>
                            <p class="mb-0 small text-muted">Klik atau seret file baru ke sini</p>
                            <p class="mb-0 text-muted" style="font-size:.75rem">JPG, PNG, PDF — maks. 2 MB</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('ppdb.status', $applicant->registration_code) }}"
                   class="btn btn-outline-secondary rounded-3">
                    <i class="bi bi-arrow-left me-1"></i>Batal
                </a>
                <button type="submit" class="btn btn-ppdb-primary px-5" id="submitBtn">
                    <i class="bi bi-cloud-arrow-up me-2"></i>Kirim Ulang Dokumen
                </button>
            </div>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
function updateZone(id, input) {
    const zone  = document.getElementById('zone_' + id);
    const label = document.getElementById('zone_label_' + id);
    if (input.files && input.files[0]) {
        const file   = input.files[0];
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        zone.style.borderColor = '#0e9f6e';
        zone.style.background  = '#f0fdf4';
        label.innerHTML = `
            <i class="bi bi-check-circle-fill text-success fs-3 mb-1 d-block"></i>
            <p class="mb-0 small text-success fw-semibold">${file.name}</p>
            <p class="mb-0 text-muted" style="font-size:.75rem">${sizeMB} MB — siap diupload</p>
        `;
    }
}

document.getElementById('reuploadForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
    btn.disabled  = true;
});
</script>
@endpush
