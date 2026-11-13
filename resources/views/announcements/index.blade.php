@extends('layouts.app-bootstrap')

@section('title', 'Pengumuman')

@section('styles')
<style>
    .announcement-card {
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background: #fff;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .announcement-card:hover {
        box-shadow: 0 4px 16px rgba(13,110,253,0.08);
        transform: translateY(-1px);
    }
    .announcement-card.unread::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        background: var(--primary-color);
        border-radius: 4px 0 0 4px;
    }
    .announcement-card.pinned {
        border-color: #fbbf24;
        background: #fffbeb;
    }
    .announcement-card.urgent::before {
        background: #ef4444;
    }
    .priority-badge { font-size: 0.7rem; }
    .filter-tabs .nav-link {
        color: var(--text-muted);
        border-radius: 8px;
        padding: 0.4rem 1rem;
        font-size: 0.875rem;
    }
    .filter-tabs .nav-link.active {
        background: var(--primary-bg);
        color: var(--primary-color);
        font-weight: 600;
    }
    .notif-toggle {
        border-radius: 20px;
        font-size: 0.8rem;
        padding: 0.35rem 1rem;
    }
</style>
@endsection

@section('header')
    <h2><i class="bi bi-megaphone me-2 text-primary"></i>Pengumuman</h2>
    <p>Informasi & pengumuman sekolah</p>
@endsection

@section('header_action')
    @can('create', \App\Models\Announcement::class)
        <a href="{{ route('announcements.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Buat Pengumuman
        </a>
    @endcan
@endsection

@section('content')
{{-- Tombol Aktifkan Notifikasi --}}
<div id="push-banner" class="alert alert-info d-flex align-items-center justify-content-between rounded-4 mb-4 d-none" role="alert">
    <div>
        <i class="bi bi-bell-fill me-2"></i>
        <strong>Aktifkan notifikasi</strong> untuk mendapat pengumuman langsung di HP Anda.
    </div>
    <button id="btn-enable-push" class="btn btn-sm btn-primary notif-toggle">
        <i class="bi bi-bell me-1"></i> Aktifkan
    </button>
</div>

{{-- Filter (Admin & Guru) --}}
@if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah', 'Guru']))
<div class="mb-4">
    <ul class="nav filter-tabs gap-1">
        <li class="nav-item">
            <a href="{{ route('announcements.index') }}" class="nav-link {{ !request('status') ? 'active' : '' }}">
                Semua
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('announcements.index', ['status' => 'published']) }}" class="nav-link {{ request('status') === 'published' ? 'active' : '' }}">
                <i class="bi bi-check-circle me-1"></i>Aktif
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('announcements.index', ['status' => 'draft']) }}" class="nav-link {{ request('status') === 'draft' ? 'active' : '' }}">
                <i class="bi bi-pencil me-1"></i>Draft
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('announcements.index', ['status' => 'expired']) }}" class="nav-link {{ request('status') === 'expired' ? 'active' : '' }}">
                <i class="bi bi-clock-history me-1"></i>Expired
            </a>
        </li>
    </ul>
</div>
@endif

{{-- List Pengumuman --}}
@forelse($announcements as $ann)
    @php
        $isUnread = !in_array($ann->id, $readIds ?? []) && $ann->author_id !== auth()->id();
        $cardClass = collect([
            'announcement-card mb-3',
            $isUnread   ? 'unread' : '',
            $ann->is_pinned ? 'pinned' : '',
            $ann->priority === 'urgent' ? 'urgent' : '',
        ])->filter()->join(' ');
    @endphp
    <div class="{{ $cardClass }}">
        <a href="{{ route('announcements.show', $ann->id) }}" class="text-decoration-none">
            <div class="p-4">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
                    <div class="flex-grow-1">
                        {{-- Badges --}}
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            @if($ann->is_pinned)
                                <span class="badge bg-warning text-dark priority-badge">
                                    <i class="bi bi-pin-fill me-1"></i>Disematkan
                                </span>
                            @endif
                            @if($ann->priority === 'urgent')
                                <span class="badge bg-danger priority-badge">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Penting
                                </span>
                            @endif
                            <span class="badge bg-{{ $ann->status_color }}-subtle text-{{ $ann->status_color }} priority-badge">
                                {{ $ann->status_label }}
                            </span>
                            @if($isUnread)
                                <span class="badge bg-primary priority-badge">Baru</span>
                            @endif
                        </div>

                        <h6 class="fw-bold text-dark mb-1 {{ $isUnread ? '' : 'text-muted fw-normal' }}">
                            {{ $ann->title }}
                        </h6>
                        <p class="text-muted mb-0" style="font-size:0.8125rem;">
                            {{ Str::limit(strip_tags($ann->content), 100) }}
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-3">
                    <div class="d-flex align-items-center gap-3 text-muted" style="font-size:0.75rem;">
                        <span><i class="bi bi-person me-1"></i>{{ $ann->author->name }}</span>
                        <span><i class="bi bi-clock me-1"></i>{{ $ann->created_at->diffForHumans() }}</span>
                        @if($ann->target_roles)
                            <span class="d-none d-md-inline">
                                <i class="bi bi-people me-1"></i>{{ implode(', ', $ann->target_roles) }}
                            </span>
                        @endif
                    </div>

                    @can('update', $ann)
                    <div class="d-flex gap-2" onclick="event.preventDefault();">
                        @if($ann->isDraft())
                            <form action="{{ route('announcements.publish', $ann->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Publish pengumuman ini?')">
                                    <i class="bi bi-send me-1"></i>Publish
                                </button>
                            </form>
                        @else
                            <form action="{{ route('announcements.unpublish', $ann->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pause me-1"></i>Unpublish
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('announcements.edit', $ann->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('announcements.destroy', $ann->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus pengumuman ini?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                    @endcan
                </div>
            </div>
        </a>
    </div>
@empty
    <div class="text-center py-5">
        <div style="font-size:3rem;" class="mb-3">📢</div>
        <h6 class="text-muted">Belum ada pengumuman</h6>
        <p class="text-muted small">Pengumuman yang ditujukan kepada Anda akan muncul di sini.</p>
        @can('create', \App\Models\Announcement::class)
            <a href="{{ route('announcements.create') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-lg me-1"></i>Buat Pengumuman Pertama
            </a>
        @endcan
    </div>
@endforelse

{{-- Pagination --}}
<div class="mt-4">
    {{ $announcements->links() }}
</div>
@endsection

@push('scripts')
<script>
// ─── Web Push Registration ────────────────────────────────────────────────────
const VAPID_PUBLIC_KEY = '{{ config("webpush.vapid.public_key") }}';

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
}

async function registerServiceWorker() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    try {
        const reg = await navigator.serviceWorker.register('/sw.js');
        console.log('SW registered:', reg.scope);

        // Cek apakah sudah subscribe
        const existing = await reg.pushManager.getSubscription();
        if (existing) {
            document.getElementById('push-banner')?.classList.add('d-none');
            return;
        }

        // Tunjukkan banner jika belum subscribe & permission belum denied
        if (Notification.permission !== 'denied') {
            document.getElementById('push-banner')?.classList.remove('d-none');
        }
    } catch (err) {
        console.warn('SW registration failed:', err);
    }
}

async function subscribePush() {
    try {
        const reg = await navigator.serviceWorker.ready;

        const subscription = await reg.pushManager.subscribe({
            userVisibleOnly:      true,
            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
        });

        const key   = subscription.getKey('p256dh');
        const token = subscription.getKey('auth');

        await fetch('{{ route("push.subscribe") }}', {
            method: 'POST',
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                endpoint:   subscription.endpoint,
                public_key: btoa(String.fromCharCode(...new Uint8Array(key))),
                auth_token: btoa(String.fromCharCode(...new Uint8Array(token))),
            }),
        });

        document.getElementById('push-banner')?.classList.add('d-none');
        showToast('Notifikasi berhasil diaktifkan! 🎉', 'success');
    } catch (err) {
        console.error('Push subscribe error:', err);
        if (err.name === 'NotAllowedError') {
            showToast('Izin notifikasi ditolak.', 'warning');
        }
    }
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed bottom-0 end-0 m-4 shadow-sm rounded-4`;
    toast.style.cssText = 'z-index:9999;min-width:280px;animation:fadeInUp .3s ease';
    toast.innerHTML = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

// ─── Unread Count Badge ────────────────────────────────────────────────────────
function updateUnreadBadge() {
    fetch('{{ route("announcements.unread-count") }}')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notif-badge');
            if (!badge) return;
            if (data.count > 0) {
                badge.textContent = data.count > 99 ? '99+' : data.count;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        })
        .catch(() => {});
}

document.addEventListener('DOMContentLoaded', () => {
    registerServiceWorker();
    updateUnreadBadge();

    document.getElementById('btn-enable-push')?.addEventListener('click', subscribePush);

    // Poll setiap 60 detik
    setInterval(updateUnreadBadge, 60000);
});
</script>
@endpush

