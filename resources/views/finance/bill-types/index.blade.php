@extends('layouts.app-bootstrap')

@section('title', 'Manajemen Jenis Tagihan')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-1 fw-bold">
            <i class="bi bi-tags text-success me-2"></i>Jenis Tagihan
        </h2>
        <p class="text-muted mb-0">Master data jenis tagihan yang bisa ditambah dan dikelola Admin</p>
    </div>
    <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg me-1"></i> Tambah Jenis Tagihan
    </button>
</div>
@endsection

@section('content')

{{-- Legend --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 bg-success text-white">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2"><i class="bi bi-arrow-repeat fs-4"></i></div>
                <div>
                    <div class="small opacity-75">Tagihan Berulang</div>
                    <div class="fw-bold fs-5">{{ $billTypes->where('is_recurring', true)->count() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 bg-info text-white">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2"><i class="bi bi-door-open fs-4"></i></div>
                <div>
                    <div class="small opacity-75">Tagihan Awal Masuk</div>
                    <div class="fw-bold fs-5">{{ $billTypes->where('is_initial_bill', true)->count() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 bg-primary text-white">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2"><i class="bi bi-check-circle fs-4"></i></div>
                <div>
                    <div class="small opacity-75">Aktif</div>
                    <div class="fw-bold fs-5">{{ $billTypes->where('is_active', true)->count() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 bg-secondary text-white">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2"><i class="bi bi-tags fs-4"></i></div>
                <div>
                    <div class="small opacity-75">Total Jenis</div>
                    <div class="fw-bold fs-5">{{ $billTypes->count() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel Jenis Tagihan --}}
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Nama Tagihan</th>
                        <th class="py-3">Nominal Default</th>
                        <th class="py-3 text-center">Berulang?</th>
                        <th class="py-3 text-center">Auto Awal Masuk?</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($billTypes as $bt)
                        <tr class="{{ !$bt->is_active ? 'opacity-50' : '' }}">
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $bt->name }}</div>
                                @if($bt->description)
                                    <div class="text-muted" style="font-size:0.8rem;">{{ $bt->description }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold text-success">Rp {{ number_format($bt->default_amount, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-center">
                                @if($bt->is_recurring)
                                    <span class="badge bg-success rounded-pill"><i class="bi bi-arrow-repeat me-1"></i>Ya</span>
                                @else
                                    <span class="badge bg-light text-dark rounded-pill border">Tidak</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($bt->is_initial_bill)
                                    <span class="badge bg-info rounded-pill"><i class="bi bi-door-open me-1"></i>Ya</span>
                                @else
                                    <span class="badge bg-light text-dark rounded-pill border">Tidak</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('finance.bill-types.toggle', $bt) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="badge border-0 {{ $bt->is_active ? 'bg-success' : 'bg-secondary' }} rounded-pill px-3 py-2"
                                        style="cursor:pointer;" title="{{ $bt->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                                        {{ $bt->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-outline-primary rounded-3"
                                        data-bs-toggle="modal" data-bs-target="#modalEdit-{{ $bt->id }}"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('finance.bill-types.destroy', $bt) }}" method="POST"
                                        onsubmit="return confirm('Hapus jenis tagihan \'{{ $bt->name }}\'? Pastikan tidak ada tagihan siswa yang menggunakannya.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Edit --}}
                        <div class="modal fade" id="modalEdit-{{ $bt->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title fw-bold">Edit Jenis Tagihan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('finance.bill-types.update', $bt) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-body">
                                            @include('finance.bill-types._form', ['billType' => $bt])
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                                <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-tags display-4 d-block mb-3 opacity-25"></i>
                                Belum ada jenis tagihan. Klik <strong>Tambah Jenis Tagihan</strong> untuk mulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-success me-2"></i>Tambah Jenis Tagihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('finance.bill-types.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @include('finance.bill-types._form', ['billType' => null])
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Buka modal edit jika ada error validasi (agar error tidak hilang)
    @if($errors->any())
        // Cek apakah error berasal dari form edit (ada old input dengan id)
        const editId = '{{ old("_edit_id") }}';
        if (editId) {
            const modal = document.getElementById('modalEdit-' + editId);
            if (modal) new bootstrap.Modal(modal).show();
        } else {
            new bootstrap.Modal(document.getElementById('modalTambah')).show();
        }
    @endif
</script>
@endsection
