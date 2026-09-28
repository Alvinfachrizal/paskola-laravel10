@extends('layouts.app-bootstrap')
@section('title', 'Mengerjakan Ujian — ' . $participant->exam->title)

@section('content')

{{-- Header Ujian (fullwidth, no sidebar saat mengerjakan) --}}
<div class="d-flex justify-content-between align-items-center mb-3 gap-3 bg-white border-bottom px-4 py-3 position-sticky top-0" style="z-index:100;">
    <div>
        <h5 class="fw-bold mb-0">{{ $participant->exam->title }}</h5>
        <span class="text-muted small">{{ $participant->exam->subject->name ?? '' }}</span>
    </div>
    <div class="d-flex align-items-center gap-3">
        {{-- Countdown Timer --}}
        <div class="text-center">
            <div class="fw-bold fs-4" id="timer" style="min-width:90px;font-variant-numeric:tabular-nums;">
                --:--
            </div>
            <div class="text-muted" style="font-size:0.7rem;">SISA WAKTU</div>
        </div>
        {{-- Submit --}}
        <button type="button" class="btn btn-danger rounded-3 fw-semibold px-4" onclick="confirmSubmit()">
            <i class="bi bi-send-check me-1"></i>Selesai
        </button>
    </div>
</div>

<div class="row g-4">
    {{-- Soal --}}
    <div class="col-lg-8">
        <div id="questionsContainer">
            @foreach($questions as $idx => $q)
            <div class="card border-0 shadow-sm rounded-4 mb-4 question-card" id="q-{{ $q['id'] }}"
                data-question-id="{{ $q['id'] }}">
                <div class="card-body p-4">
                    <div class="d-flex gap-3 align-items-start">
                        <span class="badge bg-primary rounded-circle fw-bold flex-shrink-0"
                            style="width:32px;height:32px;line-height:32px;text-align:center;font-size:0.9rem;">
                            {{ $idx + 1 }}
                        </span>
                        <div class="flex-grow-1">
                            <p class="fw-semibold mb-3" style="font-size:1rem;line-height:1.6;">{{ $q['question_text'] }}</p>
                            @if($q['image_path'])
                            <img src="{{ Storage::url($q['image_path']) }}" alt="Gambar soal"
                                class="img-fluid rounded-3 mb-3" style="max-height:200px;object-fit:contain;">
                            @endif

                            {{-- Pilihan Jawaban --}}
                            <div class="d-flex flex-column gap-2">
                                @foreach($q['options'] as $opt)
                                <label class="option-label d-flex align-items-center gap-3 border rounded-3 px-4 py-3 cursor-pointer"
                                    style="cursor:pointer;transition:all 0.15s;"
                                    for="opt_{{ $opt['id'] }}">
                                    <input type="radio"
                                        class="form-check-input flex-shrink-0 option-radio"
                                        id="opt_{{ $opt['id'] }}"
                                        name="answer_{{ $q['id'] }}"
                                        value="{{ $opt['id'] }}"
                                        data-question="{{ $q['id'] }}"
                                        {{ isset($answeredMap[$q['id']]) && $answeredMap[$q['id']] === $opt['id'] ? 'checked' : '' }}
                                        style="width:18px;height:18px;">
                                    <div>
                                        <span class="fw-bold me-2">{{ $opt['position_label'] }}</span>
                                        @if($opt['option_text']){{ $opt['option_text'] }}@endif
                                        @if($opt['image_path'])<img src="{{ Storage::url($opt['image_path']) }}" style="max-height:60px;border-radius:4px;display:block;margin-top:4px;">@endif
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Navigator Soal (sidebar kanan) --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 position-sticky" style="top:100px;">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Navigator Soal</h6>
                <div class="d-flex flex-wrap gap-2 mb-3" id="questionNav">
                    @foreach($questions as $idx => $q)
                    <button type="button"
                        class="btn btn-sm rounded-3 nav-btn {{ isset($answeredMap[$q['id']]) ? 'btn-primary' : 'btn-outline-secondary' }}"
                        data-qid="{{ $q['id'] }}"
                        onclick="scrollToQuestion('{{ $q['id'] }}')"
                        style="width:38px;height:38px;font-size:0.85rem;font-weight:600;">
                        {{ $idx + 1 }}
                    </button>
                    @endforeach
                </div>
                <div class="d-flex gap-2 align-items-center mb-3">
                    <span class="badge bg-primary rounded-pill px-2">●</span>
                    <span class="small text-muted">Sudah dijawab</span>
                    <span class="badge bg-outline-secondary border rounded-pill px-2 ms-2">●</span>
                    <span class="small text-muted">Belum</span>
                </div>
                <div class="border-top pt-3">
                    <p class="small text-muted mb-1">Dijawab: <strong id="answeredCountDisplay">{{ count($answeredMap) }}</strong> / {{ count($questions) }}</p>
                    <button type="button" class="btn btn-danger rounded-3 w-100 fw-semibold mt-2" onclick="confirmSubmit()">
                        <i class="bi bi-send-check me-1"></i>Kumpulkan Jawaban
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Submit --}}
<div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                    style="width:64px;height:64px;background:#fef9e7;">
                    <i class="bi bi-send-check-fill text-warning fs-2"></i>
                </div>
                <h5 class="fw-bold mb-2">Kumpulkan Jawaban?</h5>
                <p class="text-muted small mb-1">Anda telah menjawab <strong id="submitAnsweredCount">0</strong> dari <strong>{{ count($questions) }}</strong> soal.</p>
                <p class="text-muted small mb-4">Setelah dikumpulkan, jawaban tidak bisa diubah.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Kembali</button>
                    <button type="button" class="btn btn-danger rounded-3 px-4 fw-semibold" id="confirmSubmitBtn" onclick="doSubmit()">
                        Ya, Kumpulkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Waktu Habis --}}
<div class="modal fade" id="timeupModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <i class="bi bi-alarm-fill text-danger display-3 d-block mb-3"></i>
                <h5 class="fw-bold mb-2">Waktu Habis!</h5>
                <p class="text-muted small mb-4">Ujian Anda otomatis dikumpulkan. Jawaban yang sudah disimpan akan dinilai.</p>
                <a id="timeupResultBtn" href="#" class="btn btn-primary rounded-3 fw-semibold px-4">
                    Lihat Hasil
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.option-label:has(input:checked) {
    background: #eff6ff;
    border-color: #3b82f6 !important;
}
.option-label:hover { background: #f8fafc; }
.question-card { scroll-margin-top: 90px; }
</style>
@endpush

@push('scripts')
<script>
const PARTICIPANT_ID = '{{ $participant->id }}';
const RESULT_URL = '{{ route('exam.session.result', $participant) }}';
const ANSWER_URL = '{{ route('exam.session.answer', $participant) }}';
const SUBMIT_URL = '{{ route('exam.session.submit', $participant) }}';
const TIME_URL   = '{{ route('exam.session.time', $participant) }}';
const CSRF_TOKEN = '{{ csrf_token() }}';

let remainingSeconds = {{ $remainingSeconds }};
let answeredMap = {!! json_encode($answeredMap) !!};
let timerInterval = null;
let timeupFired = false;

// ── Timer ────────────────────────────────────────────────────────────────────
function startTimer() {
    updateTimerDisplay();
    timerInterval = setInterval(() => {
        remainingSeconds--;
        updateTimerDisplay();
        if (remainingSeconds <= 0 && !timeupFired) {
            clearInterval(timerInterval);
            timeupFired = true;
            triggerTimeup();
        }
    }, 1000);
}

function updateTimerDisplay() {
    const el = document.getElementById('timer');
    if (!el) return;
    const m = Math.floor(Math.max(0, remainingSeconds) / 60);
    const s = Math.max(0, remainingSeconds) % 60;
    el.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    el.style.color = remainingSeconds <= 60 ? '#dc3545' : '';
}

// ── Sync waktu dari server setiap 30 detik ────────────────────────────────────
function startTimerSync() {
    setInterval(async () => {
        try {
            const res = await fetch(TIME_URL);
            const data = await res.json();
            if (!data.allowed && !timeupFired) {
                clearInterval(timerInterval);
                timeupFired = true;
                triggerTimeup();
            } else if (data.allowed) {
                const serverRemaining = data.remaining_seconds;
                // Koreksi jika drift > 5 detik
                if (Math.abs(serverRemaining - remainingSeconds) > 5) {
                    remainingSeconds = serverRemaining;
                }
            }
        } catch(e) { /* ignore network error */ }
    }, 30000);
}

// ── Simpan jawaban (autosave) ─────────────────────────────────────────────────
async function saveAnswer(questionId, optionId) {
    try {
        const res = await fetch(ANSWER_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ question_id: questionId, option_id: optionId }),
        });

        const data = await res.json();

        if (!data.ok) {
            if (!timeupFired && data.error && data.error.includes('berakhir')) {
                timeupFired = true;
                clearInterval(timerInterval);
                triggerTimeup();
            }
            return;
        }

        // Update remaining seconds dari server
        if (data.remaining_seconds !== undefined) {
            remainingSeconds = data.remaining_seconds;
        }

        // Update navigator
        answeredMap[questionId] = optionId;
        updateNavBtn(questionId, true);
        updateAnsweredCount();
    } catch(e) { /* ignore */ }
}

// ── Event listener pilihan jawaban ───────────────────────────────────────────
document.querySelectorAll('.option-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        const qId = this.dataset.question;
        const optId = this.value;
        updateNavBtn(qId, true);
        saveAnswer(qId, optId);
    });
});

// ── Navigator soal ────────────────────────────────────────────────────────────
function updateNavBtn(qId, answered) {
    const btn = document.querySelector(`.nav-btn[data-qid="${qId}"]`);
    if (btn) {
        btn.classList.toggle('btn-primary', answered);
        btn.classList.toggle('btn-outline-secondary', !answered);
    }
}

function scrollToQuestion(qId) {
    const el = document.getElementById('q-' + qId);
    if (el) el.scrollIntoView({ behavior: 'smooth' });
}

function updateAnsweredCount() {
    const count = Object.keys(answeredMap).length;
    const el = document.getElementById('answeredCountDisplay');
    if (el) el.textContent = count;
}

// ── Submit ────────────────────────────────────────────────────────────────────
function confirmSubmit() {
    const count = Object.keys(answeredMap).length;
    document.getElementById('submitAnsweredCount').textContent = count;
    new bootstrap.Modal(document.getElementById('submitModal')).show();
}

async function doSubmit() {
    document.getElementById('confirmSubmitBtn').disabled = true;
    document.getElementById('confirmSubmitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengumpulkan...';

    try {
        const res = await fetch(SUBMIT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({}),
        });
        window.location.href = RESULT_URL;
    } catch(e) {
        window.location.href = RESULT_URL;
    }
}

// ── Waktu habis ───────────────────────────────────────────────────────────────
function triggerTimeup() {
    document.getElementById('timeupResultBtn').href = RESULT_URL;
    new bootstrap.Modal(document.getElementById('timeupModal')).show();
}

// ── Init ──────────────────────────────────────────────────────────────────────
startTimer();
startTimerSync();
updateAnsweredCount();
</script>
@endpush
