@extends('layouts.app-bootstrap')
@section('title', 'Kelola Kategori Event')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold"><i class="bi bi-tags me-2 text-primary"></i>Kelola Kategori Event</h2>
        <p class="text-muted mb-0 small">Tambah atau ubah kategori untuk kalender akademik</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('calendar.index') }}" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-calendar3 me-1"></i>Kembali ke Kalender
        </a>
        <button class="btn btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
            <i class="bi bi-plus-lg me-1"></i>Tambah Kategori
        </button>
    </div>
</div>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 mb-3">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card rounded-3 border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kategori</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Warna</th>
                        <th class="text-center">Jumlah Event</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle d-inline-block" style="width:12px;height:12px;background:{{ $cat->color }}"></span>
                                <span class="fw-semibold">{{ $cat->name }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($cat->is_holiday)
                                <span class="badge rounded-pill" style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca">
                                    <i class="bi bi-moon-stars-fill me-1"></i>Hari Libur
                                </span>
                            @else
                                <span class="badge rounded-pill" style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0">
                                    <i class="bi bi-calendar-event me-1"></i>Kegiatan
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <input type="color" value="{{ $cat->color }}" disabled
                                   style="width:32px;height:32px;border:none;border-radius:6px;cursor:default;padding:2px">
                            <small class="text-muted ms-1">{{ $cat->color }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary rounded-pill">{{ $cat->events_count }}</span>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <button class="btn btn-sm btn-outline-primary rounded-2"
                                        onclick="fillEditKategori({{ $cat->toJson() }})"
                                        title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('calendar.categories.destroy', $cat) }}"
                                      onsubmit="return confirm('Hapus kategori \'{{ $cat->name }}\'? Kategori yang masih memiliki event tidak bisa dihapus.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger rounded-2" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-tags display-6 d-block mb-2"></i>
                            Belum ada kategori. Tambah kategori pertama Anda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Kategori --}}
<div class="modal fade" id="modalTambahKategori" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Tambah Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('calendar.categories.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" required
                               placeholder="Contoh: Hari Libur Nasional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Warna <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="color" value="#3B82F6" id="addColorPicker"
                                   class="form-control form-control-color rounded-3" style="width:50px;height:40px">
                            <input type="text" id="addColorText" value="#3B82F6" class="form-control rounded-3"
                                   readonly style="max-width:110px;font-family:monospace">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_holiday" id="addIsHoliday" value="1">
                            <label class="form-check-label" for="addIsHoliday">
                                <i class="bi bi-moon-stars-fill text-danger me-1"></i>
                                <strong>Kategori Hari Libur</strong>
                                <small class="d-block text-muted">Event dengan kategori ini akan menonaktifkan jadwal pelajaran</small>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Kategori --}}
<div class="modal fade" id="modalEditKategori" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formEditKategori">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_cat_name" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Warna <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="color" id="editColorPicker"
                                   class="form-control form-control-color rounded-3" style="width:50px;height:40px">
                            <input type="text" id="editColorText" class="form-control rounded-3"
                                   readonly style="max-width:110px;font-family:monospace">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_holiday" id="edit_cat_holiday" value="1">
                            <label class="form-check-label" for="edit_cat_holiday">
                                <i class="bi bi-moon-stars-fill text-danger me-1"></i>
                                <strong>Kategori Hari Libur</strong>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Sinkronkan color picker & text
document.getElementById('addColorPicker')?.addEventListener('input', function() {
    document.getElementById('addColorText').value = this.value.toUpperCase();
});
document.getElementById('editColorPicker')?.addEventListener('input', function() {
    document.getElementById('editColorText').value = this.value.toUpperCase();
});

function fillEditKategori(cat) {
    document.getElementById('formEditKategori').action = '/calendar/categories/' + cat.id;
    document.getElementById('edit_cat_name').value = cat.name;
    document.getElementById('editColorPicker').value = cat.color;
    document.getElementById('editColorText').value = cat.color.toUpperCase();
    document.getElementById('edit_cat_holiday').checked = cat.is_holiday == true;
    new bootstrap.Modal(document.getElementById('modalEditKategori')).show();
}
</script>
@endsection
