@extends('layouts.app-bootstrap')

@section('header')
    <h2 class="h4 mb-0 text-dark fw-bold">{{ __('Edit Kelas') }}</h2>
@endsection

@section('header_action')
    <a href="{{ route('admin.classes.index') }}" class="btn btn-light text-secondary border d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('admin.classes.update', $class->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="grade" class="form-label">Tingkat Kelas <span class="text-danger">*</span></label>
                            <input type="number" min="1" class="form-control @error('grade') is-invalid @enderror" id="grade" name="grade" value="{{ old('grade', $class->grade) }}" required placeholder="Contoh: 10">
                            @error('grade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $class->name) }}" required placeholder="Contoh: X RPL 1">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="school_year_id" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select class="form-select @error('school_year_id') is-invalid @enderror" id="school_year_id" name="school_year_id" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach ($schoolYears as $sy)
                                <option value="{{ $sy->id }}" {{ old('school_year_id', $class->school_year_id) == $sy->id ? 'selected' : '' }}>
                                    {{ $sy->academic_year }} ({{ ucfirst($sy->semester) }})
                                </option>
                            @endforeach
                        </select>
                        @error('school_year_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="major_id" class="form-label">Jurusan (Opsional)</label>
                        <select class="form-select @error('major_id') is-invalid @enderror" id="major_id" name="major_id">
                            <option value="">-- Pilih Jurusan --</option>
                            @foreach ($majors as $major)
                                <option value="{{ $major->id }}" {{ old('major_id', $class->major_id) == $major->id ? 'selected' : '' }}>
                                    {{ $major->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('major_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="homeroom_teacher_id" class="form-label">Wali Kelas (Opsional)</label>
                        <select class="form-select @error('homeroom_teacher_id') is-invalid @enderror" id="homeroom_teacher_id" name="homeroom_teacher_id">
                            <option value="">-- Pilih Wali Kelas --</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('homeroom_teacher_id', $class->homeroom_teacher_id) == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('homeroom_teacher_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="room_number" class="form-label">Nomor/Nama Ruangan (Opsional)</label>
                            <input type="text" class="form-control @error('room_number') is-invalid @enderror" id="room_number" name="room_number" value="{{ old('room_number', $class->room_number) }}" placeholder="Contoh: R-101">
                            @error('room_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="max_students" class="form-label">Maksimal Siswa (Opsional)</label>
                            <input type="number" min="1" class="form-control @error('max_students') is-invalid @enderror" id="max_students" name="max_students" value="{{ old('max_students', $class->max_students) }}" placeholder="Contoh: 36">
                            @error('max_students')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $class->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Update Kelas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
