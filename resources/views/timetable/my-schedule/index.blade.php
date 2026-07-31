@extends('layouts.app-bootstrap')

@section('title', 'Jadwal Pelajaran Mingguan')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-1 fw-bold">
            <i class="bi bi-calendar-week text-primary me-2"></i>{{ $titlePrefix }}
        </h2>
        <p class="text-muted mb-0">{{ $subtitlePrefix }} | Semester: <strong>{{ $activeSemester->name }}</strong></p>
    </div>
    
    <!-- Navigasi Minggu -->
    <div class="d-flex align-items-center bg-white border rounded-pill px-2 py-1 shadow-sm">
        <a href="{{ route('timetable.my-schedule', ['date' => $prevWeek]) }}" class="btn btn-sm btn-light rounded-circle" title="Minggu Sebelumnya">
            <i class="bi bi-chevron-left"></i>
        </a>
        <span class="fw-semibold px-3 text-dark" style="font-size: 0.9rem;">
            {{ $weekTitle }}
        </span>
        <a href="{{ route('timetable.my-schedule', ['date' => $nextWeek]) }}" class="btn btn-sm btn-light rounded-circle" title="Minggu Selanjutnya">
            <i class="bi bi-chevron-right"></i>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-body p-0">
        
        @php
            $hariLokal = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
            
            // Siapkan peta libur per hari 
            $holidayMap = [];
            $nonHolidayMap = [];
            
            foreach($activeDays as $day) {
                // Konversi urutan hari ke tanggal spesifik minggu ini
                // $startOfWeek adalah hari Senin (index 1).
                // Maka hari N adalah $startOfWeek + (N - 1) hari.
                $dateForDay = $startOfWeek->copy()->addDays($day->day_of_week - 1);
                $dateStr = $dateForDay->toDateString();
                
                $holidayMap[$day->day_of_week] = false;
                $nonHolidayMap[$day->day_of_week] = [];
                
                foreach($events as $event) {
                    $eventStart = $event->start_date->toDateString();
                    $eventEnd = $event->end_date->toDateString();
                    
                    if ($dateStr >= $eventStart && $dateStr <= $eventEnd) {
                        if ($event->category->is_holiday) {
                            $holidayMap[$day->day_of_week] = $event->title;
                        } else {
                            $nonHolidayMap[$day->day_of_week][] = $event->title;
                        }
                    }
                }
            }
        @endphp

        <div class="table-responsive">
            <table class="table table-bordered mb-0" style="min-width: 900px;">
                <thead class="bg-light text-center align-middle">
                    <tr>
                        <th width="8%" class="py-3 text-muted">Jam Ke-</th>
                        @foreach($activeDays as $day)
                            @php
                                $dateForDay = $startOfWeek->copy()->addDays($day->day_of_week - 1);
                                $isToday = $dateForDay->isToday();
                            @endphp
                            <th width="{{ 92 / count($activeDays) }}%" class="py-3 {{ $isToday ? 'bg-primary bg-opacity-10 text-primary border-bottom border-primary border-3' : '' }}">
                                <div class="fs-6 fw-bold">{{ $hariLokal[$day->day_of_week] }}</div>
                                <div class="fw-normal" style="font-size: 0.75rem;">{{ $dateForDay->format('d/m/Y') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @if($maxPeriod == 0)
                        <tr>
                            <td colspan="{{ count($activeDays) + 1 }}" class="text-center py-5 text-muted">
                                Tidak ada data jam pelajaran (Time Slots) yang terdaftar.
                            </td>
                        </tr>
                    @endif
                    
                    @for($p = 1; $p <= $maxPeriod; $p++)
                        <tr>
                            <!-- Kolom Jam Ke- -->
                            <td class="text-center align-middle bg-light fw-bold text-muted border-end-0">
                                {{ $p }}
                            </td>

                            <!-- Loop Kolom Hari -->
                            @foreach($activeDays as $day)
                                @php $dayOfWeek = $day->day_of_week; @endphp
                                
                                @if($holidayMap[$dayOfWeek])
                                    <!-- JIKA LIBUR: Render Rowspan HANYA di Jam Pertama -->
                                    @if($p == 1)
                                        <td rowspan="{{ $maxPeriod }}" class="text-center align-middle bg-danger bg-opacity-10 border-danger-subtle px-3">
                                            <div class="d-flex flex-column align-items-center justify-content-center h-100" style="min-height: 200px;">
                                                <i class="bi bi-calendar-x text-danger opacity-75 mb-3" style="font-size: 3rem;"></i>
                                                <div class="text-danger fw-bold fs-5 mb-1 text-uppercase tracking-wide">LIBUR</div>
                                                <div class="text-danger fw-medium">{{ $holidayMap[$dayOfWeek] }}</div>
                                            </div>
                                        </td>
                                    @endif
                                @else
                                    <!-- JIKA BUKAN LIBUR -->
                                    <td class="p-2" style="background-color: #fafbfc;">
                                        
                                        <!-- Banner Event Non-Libur (Tampil di jam pertama) -->
                                        @if($p == 1 && count($nonHolidayMap[$dayOfWeek]) > 0)
                                            @foreach($nonHolidayMap[$dayOfWeek] as $nhEvent)
                                                <div class="bg-info bg-opacity-25 text-info-emphasis border border-info-subtle rounded-2 px-2 py-1 mb-2 text-wrap" style="font-size: 0.75rem; font-weight: 500;">
                                                    <i class="bi bi-info-circle-fill me-1"></i> {{ $nhEvent }}
                                                </div>
                                            @endforeach
                                        @endif
                                        
                                        <!-- Cek Jadwal -->
                                        @php
                                            $currentSlot = $timeSlotsGrid[$dayOfWeek][$p] ?? null;
                                            $schedule = $currentSlot ? ($schedulesByTimeSlot[$currentSlot->id] ?? null) : null;
                                        @endphp

                                        @if($schedule)
                                            <!-- Ada Jadwal -->
                                            <div class="card bg-white border-0 shadow-sm h-100 rounded-3 overflow-hidden" style="border-left: 4px solid var(--bs-primary) !important;">
                                                <div class="card-body p-2 d-flex flex-column">
                                                    <div class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.85rem;" title="{{ $schedule->subject->name }}">
                                                        {{ $schedule->subject->name }}
                                                    </div>
                                                    
                                                    <div class="text-primary mb-2 fw-medium" style="font-size: 0.7rem;">
                                                        <i class="bi bi-clock me-1"></i>
                                                        {{ \Carbon\Carbon::parse($currentSlot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($currentSlot->end_time)->format('H:i') }}
                                                    </div>
                                                    
                                                    <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 0.7rem;">
                                                        <!-- Toggle tampilan berdasar role -->
                                                        @if(Auth::user()->role === 'Guru')
                                                            <span class="badge bg-secondary fw-normal text-truncate" style="max-width: 60px;">{{ $schedule->schoolClass->name }}</span>
                                                        @else
                                                            <span class="text-truncate text-muted d-inline-block" style="max-width: 70px;" title="{{ $schedule->teacher->user->name ?? '' }}">
                                                                <i class="bi bi-person me-1"></i>{{ $schedule->teacher->user->name ?? '' }}
                                                            </span>
                                                        @endif
                                                        
                                                        <span class="text-muted fw-medium text-end text-truncate" style="max-width: 50px;" title="{{ $schedule->room->name ?? '' }}">
                                                            <i class="bi bi-geo-alt"></i> {{ $schedule->room->name ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            @if($currentSlot)
                                                <!-- Slot ada tapi kosong -->
                                                <div class="h-100 w-100 d-flex flex-column align-items-center justify-content-center text-muted opacity-25 p-2 rounded-3" style="border: 1px dashed #cbd5e1; min-height: 80px;">
                                                    <div style="font-size: 0.7rem;">
                                                        {{ \Carbon\Carbon::parse($currentSlot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($currentSlot->end_time)->format('H:i') }}
                                                    </div>
                                                    <i class="bi bi-dash mt-1"></i>
                                                </div>
                                            @else
                                                <!-- Tidak ada slot di jam ini -->
                                                <div class="w-100 h-100 rounded" style="background-color: transparent; min-height: 80px;"></div>
                                            @endif
                                        @endif
                                        
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
