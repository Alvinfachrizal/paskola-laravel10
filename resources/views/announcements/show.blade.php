@extends('layouts.app-bootstrap')

@section('title', $announcement->title)

@section('styles')
<style>
    .announcement-content {
        font-size: 0.9375rem;
        line-height: 1.8;
        color: var(--text-main);
    }
    .announcement-content p { margin-bottom: 1rem; }
    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #f1f5f9;
        border-radius: 20px;
        padding: 0.3rem 0.9rem;
        font-size: 0.8rem;
        color: var(--text-muted);
    }
    .meta-chip i { font-size: 0.875rem; }
    .back-btn { font-size: 0.875rem; }
</style>
@endsection

@section('header')
    <h2><i class="bi bi-megaphone me-2 text-primary"></i>Detail Pengumuman</h2>
@endsection

@section('header_action')
    <a href="{{ route('announcements.index') }}" class="btn btn-light back-btn">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">

        {{-- Status Banner (Draft / Expired) --}}
        @if($announcement->isDraft())
            <div class="alert alert-warning rounded-4 d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-pencil-square"></i>
                <span>Ini adalah <strong>draft</strong>. Belum dipublikasikan.</span>
                @can('publish', $announcement)
                    <form action="{{ route('announcements.publish', $announcement->id) }}" method="POST" class="ms-auto">
                        @csrf
                        <button class="btn btn-sm btn-warning">
                            <i class="bi bi-send me-1"></i>Publish Sekarang
                        </button>
                    </form>
                @endcan
            </div>
        @elseif($announcement->isExpired())
            <div class="alert alert-danger rounded-4 mb-4">
                <i class="bi bi-clock-history me-1"></i> Pengumuman ini sudah <strong>expired</strong> pada {{ $announcement->expires_at->format('d M Y') }}.
            </div>
        @endif

        {{-- Card Utama --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-md-5">

                {{-- Badges --}}
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if($announcement->is_pinned)
                        <span class="badge bg-warning text-dark"><i class="bi bi-pin-fill me-1"></i>Disematkan</span>
                    @endif
                    @if($announcement->priority === 'urgent')
                        <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Penting</span>
                    @endif
                    @if(!empty($announcement->target_roles))
                        <span class="badge bg-info text-dark">
                            <i class="bi bi-people me-1"></i>{{ implode(', ', $announcement->target_roles) }}
                        </span>
                    @endif
                </div>

                {{-- Judul --}}
                <h1 class="fw-bold mb-4" style="font-size:1.5rem;line-height:1.3;">
                    {{ $announcement->title }}
                </h1>

                {{-- Meta --}}
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="meta-chip">
                        <i class="bi bi-person"></i> {{ $announcement->author->name }}
                    </span>
                    <span class="meta-chip">
                        <i class="bi bi-calendar3"></i>
                        {{ $announcement->published_at?->format('d M Y, H:i') ?? 'Belum dipublish' }}
                    </span>
                    @if($announcement->expires_at)
                        <span class="meta-chip">
                            <i class="bi bi-hourglass-split"></i>
                            Berakhir {{ $announcement->expires_at->format('d M Y') }}
                        </span>
                    @endif
                    <span class="meta-chip">
                        <i class="bi bi-eye"></i> {{ $readCount }} dibaca
                    </span>
                </div>

                <hr class="my-4">

                {{-- Konten --}}
                <div class="announcement-content">
                    {!! nl2br(e($announcement->content)) !!}
                </div>
            </div>
        </div>

        {{-- Action Buttons (Author/Admin) --}}
        @can('update', $announcement)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="text-muted small me-2"><i class="bi bi-gear me-1"></i>Kelola:</span>
                    <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    @if($announcement->isDraft())
                        <form action="{{ route('announcements.publish', $announcement->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-success">
                                <i class="bi bi-send me-1"></i>Publish & Kirim Notif
                            </button>
                        </form>
                    @else
                        <form action="{{ route('announcements.unpublish', $announcement->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pause me-1"></i>Jadikan Draft
                            </button>
                        </form>
                    @endif
                    @can('delete', $announcement)
                        <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" class="ms-auto">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus pengumuman ini?')">
                                <i class="bi bi-trash me-1"></i>Hapus
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
        @endcan

    </div>
</div>
@endsection

