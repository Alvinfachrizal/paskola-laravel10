@extends('layouts.app-bootstrap')
@section('title', 'Kalender Akademik')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-1 fw-bold">
            <i class="bi bi-calendar3 me-2 text-primary"></i>Kalender Akademik
        </h2>
        <p class="text-muted mb-0 small">{{ \Carbon\Carbon::create($year, $month)->locale('id')->translatedFormat('F Y') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('create', \App\Models\AcademicEvent::class)
        <button class="btn btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambahEvent">
            <i class="bi bi-plus-lg me-1"></i>Tambah Event
        </button>
        @endcan
        @can('viewAny', \App\Models\EventCategory::class)
        <a href="{{ route('calendar.categories.index') }}" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-tags me-1"></i>Kelola Kategori
        </a>
        @endcan
    </div>
</div>
@endsection

@section('styles')
<style>
/* ── Kalender Grid ─────────────────────────────────────── */
.cal-grid-wrap { border-radius: .75rem; overflow: hidden; }

.cal-header-row {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.cal-header-row .cal-day-name {
    padding: .6rem .25rem;
    font-size: .78rem;
    font-weight: 600;
    text-align: center;
    letter-spacing: .03em;
    color: #64748b;
}
.cal-header-row .cal-day-name.sun { color: #ef4444; }

.cal-body-row {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}
.cal-cell {
    min-height: 100px;
    border-right: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    padding: .45rem .4rem;
    position: relative;
    transition: background .15s;
}
.cal-cell:hover { background: #f8fafc; }
.cal-cell.is-empty { background: #fafbfc; }
.cal-cell.is-today { background: #eff6ff; }

.cal-date-num {
    font-size: .78rem;
    font-weight: 600;
    color: #475569;
    width: 24px; height: 24px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
    margin-bottom: .2rem;
}
.cal-cell.is-today .cal-date-num { background: #3b82f6; color: #fff; }
.cal-cell.is-sunday .cal-date-num { color: #ef4444; }

.cal-event-pill {
    border-radius: .2rem;
    padding: .08rem .3rem;
    margin-bottom: .12rem;
    font-size: .67rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
    transition: opacity .15s;
}
.cal-event-pill:hover { opacity: .78; }
.cal-event-more { font-size: .62rem; color: #94a3b8; padding-left: .15rem; }

@media (max-width: 576px) {
    .cal-cell { min-height: 56px; padding: .2rem .12rem; }
    .cal-day-name { font-size: .65rem !important; }
    .cal-event-pill { display: none; }
    .cal-event-more { display: none; }
}
</style>
@endsection

@section('content')

{{-- Flash messages --}}
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

{{-- Navigasi Bulan --}}
<div class="card rounded-3 border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            @php
                $prevMonth = \Carbon\Carbon::create($year, $month)->subMonth();
                $nextMonth = \Carbon\Carbon::create($year, $month)->addMonth();
            @endphp
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('calendar.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month, 'class_id' => $classId]) }}"
                   class="btn btn-sm btn-outline-secondary rounded-3">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <strong class="px-2" style="min-width:160px;text-align:center">
                    {{ \Carbon\Carbon::create($year, $month)->locale('id')->translatedFormat('F Y') }}
                </strong>
                <a href="{{ route('calendar.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month, 'class_id' => $classId]) }}"
                   class="btn btn-sm btn-outline-secondary rounded-3">
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="{{ route('calendar.index') }}" class="btn btn-sm btn-outline-primary rounded-3">Hari ini</a>
            </div>

            @if(auth()->user()->hasRole(['Super Admin','Admin','Kepala Sekolah']))
            <form method="GET" action="{{ route('calendar.index') }}" class="d-flex align-items-center gap-2">
                <input type="hidden" name="year"  value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <select name="class_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()" style="min-width:160px">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $cls)
                    <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>
    </div>
</div>

{{-- Legenda Kategori --}}
@if($categories->count())
<div class="d-flex flex-wrap gap-2 mb-3">
    @foreach($categories as $cat)
    <span class="badge rounded-pill px-3 py-2 d-flex align-items-center gap-1"
          style="background:{{ $cat->color }}20;color:{{ $cat->color }};border:1px solid {{ $cat->color }}50;font-size:.78rem">
        <span class="rounded-circle d-inline-block flex-shrink-0" style="width:8px;height:8px;background:{{ $cat->color }}"></span>
        {{ $cat->name }}
        @if($cat->is_holiday)<i class="bi bi-moon-stars-fill ms-1" style="font-size:.7rem"></i>@endif
    </span>
    @endforeach
</div>
@endif

{{-- Grid Kalender --}}
<div class="card rounded-3 border-0 shadow-sm mb-3">
    <div class="card-body p-0">
        <div class="cal-grid-wrap">
            {{-- Header: 7 nama hari --}}
            <div class="cal-header-row">
                @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $i => $day)
                <div class="cal-day-name {{ $i === 0 ? 'sun' : '' }}">{{ $day }}</div>
                @endforeach
            </div>

            {{-- Body: tanggal dalam grid 7 kolom --}}
            <div class="cal-body-row" style="flex-wrap:wrap">
                @php
                    $startDayOfWeek = $startOfMonth->dayOfWeek; // 0=Sun
                    $daysInMonth    = $endOfMonth->day;
                    $today          = now()->format('Y-m-d');
                @endphp

                {{-- Padding awal --}}
                @for($i = 0; $i < $startDayOfWeek; $i++)
                <div class="cal-cell is-empty"></div>
                @endfor

                {{-- Hari 1 s/d akhir --}}
                @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $curDate   = \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d');
                    $dayEvents = $eventsByDate[$day] ?? [];
                    $isToday   = $curDate === $today;
                    $isSunday  = \Carbon\Carbon::create($year, $month, $day)->dayOfWeek === 0;
                @endphp
                <div class="cal-cell
                    {{ $isToday   ? 'is-today'  : '' }}
                    {{ $isSunday  ? 'is-sunday' : '' }}">
                    <div class="cal-date-num">{{ $day }}</div>

                    @foreach(array_slice($dayEvents, 0, 3) as $event)
                    <div class="cal-event-pill"
                         style="background:{{ $event->category->color }}22;border-left:3px solid {{ $event->category->color }};color:{{ $event->category->color }}"
                         title="{{ $event->title }}{{ $event->schoolClass ? ' (' . $event->schoolClass->name . ')' : '' }}"
                         data-bs-toggle="tooltip">
                        {{ $event->title }}
                    </div>
                    @endforeach
                    @if(count($dayEvents) > 3)
                    <div class="cal-event-more">+{{ count($dayEvents) - 3 }} lagi</div>
                    @endif
                </div>
                @endfor
            </div>
        </div>
    </div>
</div>

{{-- Daftar Event Bulan Ini --}}
@if($events->count())
<div class="card rounded-3 border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-3 pb-1">
        <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>Event Bulan Ini</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Event</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Berlaku Untuk</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $event->title }}</td>
                        <td class="text-muted">
                            {{ $event->start_date->format('d M') }}
                            @if(!$event->start_date->isSameDay($event->end_date))
                                – {{ $event->end_date->format('d M Y') }}
                            @else
                                {{ $event->start_date->format('Y') }}
                            @endif
                        </td>
                        <td>
                            <span class="badge rounded-pill px-2"
                                  style="background:{{ $event->category->color }}20;color:{{ $event->category->color }};border:1px solid {{ $event->category->color }}50">
                                {{ $event->category->name }}
                                @if($event->category->is_holiday)<i class="bi bi-moon-stars-fill ms-1"></i>@endif
                            </span>
                        </td>
                        <td class="text-muted">
                            {{ $event->schoolClass ? $event->schoolClass->name : 'Seluruh Sekolah' }}
                        </td>
                        <td class="pe-3">
                            <div class="d-flex gap-1">
                                @can('update', $event)
                                <button class="btn btn-sm btn-outline-primary rounded-2"
                                        onclick="fillEditModal({{ $event->toJson() }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @endcan
                                @can('delete', $event)
                                <form method="POST" action="{{ route('calendar.events.destroy', $event) }}"
                                      onsubmit="return confirm('Hapus event \'{{ $event->title }}\'?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger rounded-2"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="text-center text-muted py-4">
    <i class="bi bi-calendar-x display-6 d-block mb-2"></i>
    Tidak ada event di bulan ini.
</div>
@endif

{{-- ═══ Modal Tambah Event ═══ --}}
@can('create', \App\Models\AcademicEvent::class)
<div class="modal fade" id="modalTambahEvent" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Tambah Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('calendar.events.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Judul Event <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" required placeholder="Contoh: Ujian Tengah Semester">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select rounded-3" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categoriesForForm as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}{{ $cat->is_holiday ? ' 🌙' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col">
                            <label class="form-label fw-semibold small">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="add_start_date" class="form-control rounded-3" required value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold small">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="add_end_date" class="form-control rounded-3" required value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Berlaku Untuk</label>
                        <select name="class_id" class="form-select rounded-3">
                            @if(auth()->user()->hasRole(['Super Admin','Admin','Kepala Sekolah']))
                            <option value="">Seluruh Sekolah</option>
                            @endif
                            @foreach($classes as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold small">Keterangan (Opsional)</label>
                        <textarea name="description" class="form-control rounded-3" rows="2" placeholder="Deskripsi singkat..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-plus-lg me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Event --}}
<div class="modal fade" id="modalEditEvent" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formEditEvent">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Judul Event <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit_title" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" id="edit_category_id" class="form-select rounded-3" required>
                            @foreach($categoriesForForm as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}{{ $cat->is_holiday ? ' 🌙' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col">
                            <label class="form-label fw-semibold small">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="edit_start_date" class="form-control rounded-3" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold small">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="edit_end_date" class="form-control rounded-3" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Berlaku Untuk</label>
                        <select name="class_id" id="edit_class_id" class="form-select rounded-3">
                            @if(auth()->user()->hasRole(['Super Admin','Admin','Kepala Sekolah']))
                            <option value="">Seluruh Sekolah</option>
                            @endif
                            @foreach($classes as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold small">Keterangan (Opsional)</label>
                        <textarea name="description" id="edit_description" class="form-control rounded-3" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-save me-1"></i>Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection

@section('scripts')
<script>
document.getElementById('add_start_date')?.addEventListener('change', function() {
    const end = document.getElementById('add_end_date');
    if (end.value < this.value) end.value = this.value;
    end.min = this.value;
});

function fillEditModal(event) {
    document.getElementById('formEditEvent').action = '/calendar/events/' + event.id;
    document.getElementById('edit_title').value       = event.title;
    document.getElementById('edit_start_date').value  = event.start_date ? event.start_date.substring(0,10) : '';
    document.getElementById('edit_end_date').value    = event.end_date   ? event.end_date.substring(0,10)   : '';
    document.getElementById('edit_description').value = event.description || '';

    const catSel = document.getElementById('edit_category_id');
    for (let o of catSel.options) o.selected = (o.value === event.category_id);

    const clsSel = document.getElementById('edit_class_id');
    if (clsSel) {
        for (let o of clsSel.options) o.selected = (o.value === (event.class_id || ''));
    }

    new bootstrap.Modal(document.getElementById('modalEditEvent')).show();
}

// Aktifkan semua tooltip
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el, { trigger:'hover' });
});
</script>
@endsection
