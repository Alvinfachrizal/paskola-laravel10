@extends('layouts.app-bootstrap')

@section('title', 'Manajemen Modul')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-1 fw-bold">
            <i class="bi bi-gear text-primary me-2"></i>Manajemen Modul
        </h2>
        <p class="text-muted mb-0">Aktifkan atau nonaktifkan modul-modul besar dalam sistem sesuai kebutuhan sekolah</p>
    </div>
</div>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="alert alert-info border-0 rounded-4 shadow-sm mb-4">
    <i class="bi bi-info-circle-fill me-2"></i>
    <strong>Informasi:</strong> Menonaktifkan modul akan <strong>memblokir akses ke seluruh halaman dan URL</strong> modul tersebut untuk semua pengguna. 
    Data yang sudah ada <strong>tetap aman</strong> dan tidak akan dihapus. Modul inti sistem tidak dapat dinonaktifkan.
</div>

<div class="row g-4">
    @foreach($modules as $module)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 {{ !$module->is_active ? 'opacity-75' : '' }}">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 p-2 d-flex align-items-center justify-content-center {{ $module->is_active ? 'bg-primary bg-opacity-10 text-primary' : 'bg-secondary bg-opacity-10 text-secondary' }}" style="width:48px;height:48px;">
                                <i class="bi {{ $module->icon }} fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">{{ $module->name }}</h6>
                                @if($module->is_core)
                                    <span class="badge bg-danger rounded-pill small">Modul Inti</span>
                                @else
                                    <span class="badge {{ $module->is_active ? 'bg-success' : 'bg-secondary' }} rounded-pill small">
                                        {{ $module->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Toggle Switch --}}
                        @if($module->is_core)
                            {{-- Core: tombol terkunci, tidak bisa ditekan --}}
                            <div class="form-check form-switch ms-2 pt-1">
                                <input class="form-check-input" type="checkbox" disabled checked
                                    style="width:2.5em;height:1.4em;cursor:not-allowed;opacity:0.5;">
                            </div>
                        @else
                            <form action="{{ route('settings.modules.toggle', $module) }}" method="POST">
                                @csrf
                                <div class="form-check form-switch ms-2 pt-1">
                                    <input class="form-check-input toggle-module" type="checkbox"
                                        id="toggle-{{ $module->key }}"
                                        style="width:2.5em;height:1.4em;cursor:pointer;"
                                        {{ $module->is_active ? 'checked' : '' }}
                                        data-module-name="{{ $module->name }}"
                                        data-is-active="{{ $module->is_active ? '1' : '0' }}"
                                        onchange="this.form.submit();">
                                </div>
                            </form>
                        @endif
                    </div>

                    <p class="text-muted small mb-3">{{ $module->description }}</p>

                    {{-- Info dependency --}}
                    @if($module->dependencies->isNotEmpty())
                        <div class="mb-2">
                            <span class="text-muted small"><i class="bi bi-link-45deg me-1"></i><strong>Membutuhkan:</strong></span>
                            @foreach($module->dependencies as $dep)
                                <span class="badge {{ $dep->is_active ? 'bg-success' : 'bg-danger' }} bg-opacity-75 rounded-pill ms-1 small">
                                    {{ $dep->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if($module->dependents->where('is_active', true)->isNotEmpty())
                        <div>
                            <span class="text-muted small"><i class="bi bi-diagram-2 me-1"></i><strong>Dibutuhkan oleh:</strong></span>
                            @foreach($module->dependents->where('is_active', true) as $dep)
                                <span class="badge bg-warning text-dark rounded-pill ms-1 small">{{ $dep->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if($module->is_core)
                        <div class="mt-2">
                            <span class="text-muted small"><i class="bi bi-lock-fill me-1 text-danger"></i>Modul inti — tidak dapat dinonaktifkan</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

@endsection

@section('scripts')
<script>
// Konfirmasi sebelum menonaktifkan modul
document.querySelectorAll('.toggle-module').forEach(function(toggle) {
    toggle.addEventListener('change', function(e) {
        const isCurrentlyActive = this.dataset.isActive === '1';
        if (isCurrentlyActive) {
            const moduleName = this.dataset.moduleName;
            const confirmed = confirm(
                `⚠️ Anda akan MENONAKTIFKAN modul "${moduleName}".\n\n` +
                `Seluruh akses ke modul ini akan diblokir untuk semua pengguna.\n` +
                `Data yang ada tetap aman.\n\nLanjutkan?`
            );
            if (!confirmed) {
                e.preventDefault();
                this.checked = true; // Kembalikan ke posisi semula
            }
        }
    });
});
</script>
@endsection
