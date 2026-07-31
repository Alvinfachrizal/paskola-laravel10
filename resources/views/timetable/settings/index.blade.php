@extends('layouts.app-bootstrap')

@section('title', 'Setting Jadwal Pelajaran')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-1 fw-bold">
            <i class="bi bi-gear text-primary me-2"></i>Pengaturan Jadwal Pelajaran
        </h2>
        <p class="text-muted mb-0">Atur hari aktif dan jam pelajaran (Time Slot)</p>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Kolom Kiri: Hari Aktif -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-check me-2"></i>Hari Aktif</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('timetable.settings.days.update') }}" method="POST">
                    @csrf
                    @php
                        $hariLokal = [
                            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 
                            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
                        ];
                        // Jadikan array untuk memudahkan cek checked
                        $activeDaysArr = $academicDays->where('is_active', true)->pluck('day_of_week')->toArray();
                    @endphp

                    <div class="list-group mb-3">
                        @foreach($hariLokal as $num => $namaHari)
                            <label class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $namaHari }}
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="active_days[]" value="{{ $num }}"
                                        {{ in_array($num, $activeDaysArr) ? 'checked' : '' }}>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Simpan Hari Aktif</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Time Slots -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-clock me-2"></i>Daftar Jam Pelajaran</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTimeSlotModal">
                    <i class="bi bi-plus-lg"></i> Tambah Jam
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Hari</th>
                                <th>Shift</th>
                                <th>Jam Ke-</th>
                                <th>Waktu</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($timeSlots as $ts)
                                <tr>
                                    <td>{{ $hariLokal[$ts->day_of_week] }}</td>
                                    <td><span class="badge bg-{{ $ts->shift == 'Pagi' ? 'info' : 'warning' }}">{{ $ts->shift }}</span></td>
                                    <td>Jam ke-{{ $ts->period_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($ts->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($ts->end_time)->format('H:i') }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('timetable.settings.timeslots.destroy', $ts->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jam pelajaran ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada jam pelajaran yang diatur.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Time Slot -->
<div class="modal fade" id="addTimeSlotModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Tambah Jam Pelajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('timetable.settings.timeslots.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Hari</label>
                        <select name="day_of_week" class="form-select" required>
                            @foreach($hariLokal as $num => $namaHari)
                                <option value="{{ $num }}">{{ $namaHari }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Shift</label>
                            <select name="shift" class="form-select" required>
                                <option value="Pagi">Pagi</option>
                                <option value="Siang">Siang</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Ke-</label>
                            <input type="number" name="period_number" class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Mulai</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Selesai</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Jam</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
