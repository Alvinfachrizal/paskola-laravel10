@extends('layouts.app-bootstrap')

@section('title', 'Input Jadwal Pelajaran')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-1 fw-bold">
            <i class="bi bi-calendar-plus text-primary me-2"></i>Input Jadwal Pelajaran
        </h2>
        <p class="text-muted mb-0">Tentukan jadwal guru, kelas, dan mapel tanpa khawatir bentrok.</p>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Form Input Jadwal -->
    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Form Input</h5>
                <small class="text-muted">Semester Aktif: <strong>{{ $activeSemester->name }}</strong></small>
            </div>
            <div class="card-body">
                <form action="{{ route('timetable.schedules.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Waktu (Hari & Jam) <span class="text-danger">*</span></label>
                        <select name="time_slot_id" class="form-select select2-enable" required>
                            <option value="">-- Pilih Jam Pelajaran --</option>
                            @foreach($timeSlots as $ts)
                                <option value="{{ $ts->id }}" {{ old('time_slot_id') == $ts->id ? 'selected' : '' }}>
                                    {{ $ts->label }}
                                </option>
                            @endforeach
                        </select>
                        @error('time_slot_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Kelas <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-select select2-enable" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->grade }} - {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-select select2-enable" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} ({{ $s->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Guru Pengajar <span class="text-danger">*</span></label>
                        <select name="teacher_id" id="teacher_id" class="form-select select2-enable" required>
                            <option value="">-- Pilih Guru --</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->user->name ?? 'Unknown' }}
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">Ruangan <span class="text-danger">*</span></label>
                        <select name="room_id" class="form-select select2-enable" required>
                            <option value="">-- Pilih Ruangan --</option>
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}" {{ old('room_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->name }} ({{ $r->type }})
                                </option>
                            @endforeach
                        </select>
                        @error('room_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="bi bi-save me-1"></i> Simpan Jadwal
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Riwayat Input Terakhir -->
    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Baru Ditambahkan (10 Terakhir)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Hari & Jam</th>
                                <th>Kelas</th>
                                <th>Mapel & Guru</th>
                                <th>Ruang</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSchedules as $sched)
                                <tr>
                                    <td class="ps-3">
                                        <strong>{{ $hariLokal[$sched->timeSlot->day_of_week] }}</strong><br>
                                        <small class="text-muted">
                                            Jam ke-{{ $sched->timeSlot->period_number }} 
                                            ({{ \Carbon\Carbon::parse($sched->timeSlot->start_time)->format('H:i') }})
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">
                                            {{ $sched->schoolClass->grade ?? '' }} - {{ $sched->schoolClass->name ?? '' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-truncate" style="max-width: 150px;" title="{{ $sched->subject->name ?? '' }}">
                                            {{ $sched->subject->name ?? '' }}
                                        </div>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $sched->teacher->user->name ?? '' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $sched->room->name ?? '' }}</small>
                                    </td>
                                    <td class="text-end pe-3">
                                        <form action="{{ route('timetable.schedules.destroy', $sched->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jadwal ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x display-4 d-block mb-3 text-secondary opacity-50"></i>
                                        Belum ada jadwal yang diinput di semester ini.
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
@endsection

@section('scripts')
<!-- jQuery (Required for Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2-enable').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Logika Filter Guru berdasarkan Mata Pelajaran
        $('#subject_id').on('change', function() {
            var subjectId = $(this).val();
            var teacherSelect = $('#teacher_id');
            
            // Hapus semua pilihan saat ini dan beri loading text
            teacherSelect.empty().append('<option value="">Memuat data guru...</option>');
            teacherSelect.trigger('change.select2'); // Update select2 UI
            
            $.ajax({
                url: '{{ route('timetable.schedules.get-teachers') }}',
                type: 'GET',
                data: { subject_id: subjectId },
                success: function(response) {
                    teacherSelect.empty().append('<option value="">-- Pilih Guru --</option>');
                    
                    if (response.results) {
                        // Looping Optgroup (Rekomendasi vs Lainnya)
                        $.each(response.results, function(index, group) {
                            var optgroup = $('<optgroup>').attr('label', group.text);
                            $.each(group.children, function(idx, teacher) {
                                optgroup.append($('<option>').val(teacher.id).text(teacher.text));
                            });
                            teacherSelect.append(optgroup);
                        });
                    }
                    // Refresh UI
                    teacherSelect.trigger('change.select2');
                },
                error: function() {
                    teacherSelect.empty().append('<option value="">Gagal memuat guru</option>');
                }
            });
        });
        
        // Panggil saat load pertama kali (jika subject_id ada value dari old() validation)
        if ($('#subject_id').val() !== "") {
            var selectedTeacherId = '{{ old('teacher_id') }}';
            $('#subject_id').trigger('change');
        }
    });
</script>
@endsection
