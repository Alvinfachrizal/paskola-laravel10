@extends('layouts.app-bootstrap')

@section('header')
    <h2 class="h4 mb-0 text-dark fw-bold">{{ __('Edit Mata Pelajaran') }}</h2>
@endsection

@section('header_action')
    <a href="{{ route('admin.subjects.index') }}" class="btn btn-light text-secondary border d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('admin.subjects.update', $subject->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="code" class="form-label">Kode Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $subject->code) }}" required placeholder="Contoh: MAT-01">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $subject->name) }}" required placeholder="Contoh: Matematika Lanjut">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Tipe/Kelompok (Opsional)</label>
                            <input type="text" class="form-control @error('type') is-invalid @enderror" id="type" name="type" value="{{ old('type', $subject->type) }}" placeholder="Contoh: Muatan Nasional / A / B">
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="weekly_hours" class="form-label">Jam per Minggu (Opsional)</label>
                            <input type="number" min="1" class="form-control @error('weekly_hours') is-invalid @enderror" id="weekly_hours" name="weekly_hours" value="{{ old('weekly_hours', $subject->weekly_hours) }}" placeholder="Contoh: 4">
                            @error('weekly_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Keterangan singkat tentang mata pelajaran ini">{{ old('description', $subject->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $subject->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Update Mata Pelajaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
