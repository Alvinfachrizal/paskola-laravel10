@extends('layouts.app-bootstrap')

@section('title', 'Manajemen Ruangan')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-1 fw-bold">
            <i class="bi bi-door-open text-primary me-2"></i>Manajemen Ruangan
        </h2>
        <p class="text-muted mb-0">Kelola daftar ruang kelas, laboratorium, dan fasilitas lainnya.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Ruang
    </button>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nama Ruangan</th>
                                <th>Tipe Ruang</th>
                                <th>Kapasitas</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rooms as $room)
                                <tr>
                                    <td class="ps-4 fw-medium">{{ $room->name }}</td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill">
                                            {{ $room->type }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($room->capacity)
                                            <i class="bi bi-people text-muted me-1"></i> {{ $room->capacity }} orang
                                        @else
                                            <span class="text-muted fst-italic">Tidak diatur</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <!-- Tombol Edit -->
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editRoomModal{{ $room->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <form action="{{ route('timetable.rooms.destroy', $room->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Edit Ruangan -->
                                <div class="modal fade" id="editRoomModal{{ $room->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header border-bottom-0">
                                                <h5 class="modal-title fw-bold">Edit Ruangan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('timetable.rooms.update', $room->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Ruangan</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $room->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Tipe Ruangan</label>
                                                        <input type="text" name="type" class="form-control" list="roomTypesList" value="{{ $room->type }}" required placeholder="Pilih atau ketik tipe ruang">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Kapasitas <small class="text-muted">(Opsional)</small></label>
                                                        <input type="number" name="capacity" class="form-control" value="{{ $room->capacity }}" min="1">
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-door-closed display-4 d-block mb-3"></i>
                                            <p class="mb-0">Belum ada data ruangan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Ruangan -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Tambah Ruangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('timetable.rooms.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Ruangan</label>
                        <input type="text" name="name" class="form-control" required placeholder="Contoh: Kelas 10-A, Lab Komputer">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Ruangan</label>
                        <input type="text" name="type" class="form-control" list="roomTypesList" required placeholder="Pilih atau ketik tipe ruang" autocomplete="off">
                        <!-- Datalist untuk Tipe Ruangan dinamis -->
                        <datalist id="roomTypesList">
                            @foreach($suggestedTypes as $type)
                                <option value="{{ $type }}">
                            @endforeach
                        </datalist>
                        <div class="form-text">Anda bisa memilih dari opsi yang ada atau mengetikkan tipe ruang baru secara bebas.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kapasitas <small class="text-muted">(Opsional)</small></label>
                        <input type="number" name="capacity" class="form-control" min="1" placeholder="Contoh: 30">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Ruangan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
