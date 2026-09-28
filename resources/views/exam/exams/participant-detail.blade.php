@extends('layouts.app-bootstrap')
@section('title', 'Detail Jawaban — ' . ($participant->student->name ?? ''))

@section('header')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h2 class="h3 mb-1 fw-bold">Detail Jawaban</h2>
        <p class="text-muted mb-0 small">{{ $participant->student->name ?? '' }} • {{ $exam->title }}</p>
    </div>
    <a href="{{ route('exam.exams.results', $exam) }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>
@endsection

@section('content')

{{-- Ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3">
            <div class="text-muted small mb-1">Nilai</div>
            <div class="fw-bold fs-3 {{ $participant->score >= 75 ? 'text-success' : 'text-danger' }}">
                {{ $participant->score !== null ? number_format($participant->score, 1) : '—' }}
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3">
            <div class="text-muted small mb-1">Jawaban Benar</div>
            <div class="fw-bold fs-3 text-success">
                {{ $answers->filter(fn($a) => $a->isCorrect())->count() }} / {{ $questions->count() }}
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3">
            <div class="text-muted small mb-1">Status</div>
            <div class="mt-1"><span class="badge rounded-pill {{ $participant->status->badgeClass() }}">{{ $participant->status->label() }}</span></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3">
            <div class="text-muted small mb-1">Durasi Dikerjakan</div>
            <div class="fw-bold">
                @if($participant->started_at && $participant->finished_at)
                {{ $participant->started_at->diffInMinutes($participant->finished_at) }} mnt
                @else —
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Detail per soal --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        @foreach($questions as $idx => $question)
        @php
            $answer = $answers->get($question->id);
            $isCorrect = $answer && $answer->isCorrect();
            $isAnswered = $answer && $answer->option_id;
        @endphp
        <div class="border-bottom p-4">
            <div class="d-flex gap-3 align-items-start">
                <div class="flex-shrink-0 mt-1">
                    @if(!$isAnswered)
                    <span class="badge bg-secondary rounded-circle" style="width:28px;height:28px;line-height:28px;text-align:center;">—</span>
                    @elseif($isCorrect)
                    <span class="badge bg-success rounded-circle" style="width:28px;height:28px;line-height:28px;text-align:center;"><i class="bi bi-check-lg"></i></span>
                    @else
                    <span class="badge bg-danger rounded-circle" style="width:28px;height:28px;line-height:28px;text-align:center;"><i class="bi bi-x-lg"></i></span>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <p class="fw-semibold mb-2 small">{{ $idx + 1 }}. {{ $question->question_text }}</p>
                        <span class="text-muted small">{{ number_format($question->pivot->points, 0) }} poin</span>
                    </div>
                    @if($question->image_path)
                    <img src="{{ Storage::url($question->image_path) }}" alt="Gambar soal" class="mb-2" style="max-height:120px;border-radius:8px;">
                    @endif
                    <div class="d-flex flex-column gap-1 mt-2">
                        @foreach($question->options->sortBy('position') as $opt)
                        @php
                            $isSelected = $answer && $answer->option_id === $opt->id;
                            $optClass = 'border rounded-3 px-3 py-2 small d-flex align-items-center gap-2 ';
                            if ($isSelected && $opt->is_correct) $optClass .= 'bg-success bg-opacity-10 border-success text-success fw-bold';
                            elseif ($isSelected && !$opt->is_correct) $optClass .= 'bg-danger bg-opacity-10 border-danger text-danger fw-bold';
                            elseif ($opt->is_correct) $optClass .= 'bg-success bg-opacity-10 border-success text-success';
                            else $optClass .= 'bg-light text-secondary';
                        @endphp
                        <div class="{{ $optClass }}">
                            <span class="badge {{ $opt->is_correct ? 'bg-success' : 'bg-secondary' }} rounded-pill" style="font-size:0.7rem;">{{ $opt->positionLabel() }}</span>
                            @if($opt->option_text){{ $opt->option_text }}@endif
                            @if($opt->image_path)<img src="{{ Storage::url($opt->image_path) }}" style="max-height:40px;border-radius:4px;">@endif
                            @if($isSelected)<span class="ms-auto"><i class="bi bi-cursor-fill"></i> Dipilih</span>@endif
                            @if($opt->is_correct && !$isSelected)<span class="ms-auto small">(Jawaban benar)</span>@endif
                        </div>
                        @endforeach
                    </div>
                    @if(!$isAnswered)
                    <p class="text-muted small mt-2 fst-italic"><i class="bi bi-dash-circle me-1"></i>Tidak dijawab</p>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
