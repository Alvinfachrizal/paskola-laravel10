<?php

namespace App\Http\Controllers\Announcements;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\SchoolClass;
use App\Services\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AnnouncementController extends Controller
{
    public function __construct(private WebPushService $pushService) {}

    // ─── Index ───────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = Announcement::with('author')
                    ->where('school_id', $user->school_id)
                    ->orderByDesc('is_pinned')
                    ->orderByDesc('created_at');

        // Admin & Kepsek → lihat semua (draft + published)
        // Guru → lihat miliknya + yang published
        // Siswa & Ortu → hanya yang published & ditujukan ke mereka
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            // Tampilkan semua
        } elseif ($user->hasRole('Guru')) {
            $query->where(function ($q) use ($user) {
                $q->where('author_id', $user->id)
                  ->orWhere(fn($q2) => $q2->published()->forRole('Guru'));
            });
        } else {
            $role      = $user->role; // Siswa / Orang Tua
            $classId   = $user->student?->currentClass?->school_class_id;
            $query->published()->forRole($role);
            if ($classId) {
                $query->forClass($classId);
            }
        }

        // Filter status (admin/guru)
        if ($request->has('status') && $user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah', 'Guru'])) {
            match($request->status) {
                'draft'     => $query->whereNull('published_at'),
                'published' => $query->published(),
                'expired'   => $query->whereNotNull('expires_at')->where('expires_at', '<=', Carbon::now()),
                default     => null,
            };
        }

        $announcements = $query->paginate(15)->withQueryString();

        // Tambahkan flag read untuk non-author
        $readIds = AnnouncementRead::where('user_id', $user->id)
            ->pluck('announcement_id')
            ->toArray();

        return view('announcements.index', compact('announcements', 'readIds'));
    }

    // ─── Create ──────────────────────────────────────────────────────────────

    public function create()
    {
        $this->authorize('create', Announcement::class);

        $user    = Auth::user();
        $classes = SchoolClass::where('school_id', $user->school_id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

        $roles = $this->getTargetableRoles($user);

        return view('announcements.create', compact('classes', 'roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Announcement::class);

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'target_roles'     => 'nullable|array',
            'target_roles.*'   => 'string',
            'target_class_ids' => 'nullable|array',
            'target_class_ids.*' => 'uuid|exists:school_classes,id',
            'is_pinned'        => 'boolean',
            'expires_at'       => 'nullable|date|after:now',
            'priority'         => 'in:normal,urgent',
            'publish_now'      => 'boolean',
        ]);

        $user = Auth::user();

        $announcement = Announcement::create([
            'school_id'        => $user->school_id,
            'author_id'        => $user->id,
            'title'            => $data['title'],
            'content'          => $data['content'],
            'target_roles'     => $data['target_roles'] ?? null,
            'target_class_ids' => $data['target_class_ids'] ?? null,
            'is_pinned'        => $request->boolean('is_pinned'),
            'expires_at'       => $data['expires_at'] ?? null,
            'priority'         => $data['priority'] ?? 'normal',
            'published_at'     => $request->boolean('publish_now') ? Carbon::now() : null,
        ]);

        // Kirim Web Push jika langsung publish
        if ($announcement->isPublished()) {
            dispatch(fn() => $this->pushService->sendToAnnouncement($announcement))
                ->afterResponse();
        }

        return redirect()->route('announcements.index')
            ->with('success', $announcement->isPublished()
                ? 'Pengumuman berhasil dipublikasikan!'
                : 'Pengumuman disimpan sebagai draft.');
    }

    // ─── Show ────────────────────────────────────────────────────────────────

    public function show(Announcement $announcement)
    {
        $this->authorize('view', $announcement);

        $user = Auth::user();

        // Auto mark as read
        if ($announcement->isPublished() && $announcement->author_id !== $user->id) {
            AnnouncementRead::firstOrCreate([
                'announcement_id' => $announcement->id,
                'user_id'         => $user->id,
            ], ['read_at' => Carbon::now()]);
        }

        $readCount = $announcement->reads()->count();

        return view('announcements.show', compact('announcement', 'readCount'));
    }

    // ─── Edit ────────────────────────────────────────────────────────────────

    public function edit(Announcement $announcement)
    {
        $this->authorize('update', $announcement);

        $user    = Auth::user();
        $classes = SchoolClass::where('school_id', $user->school_id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

        $roles = $this->getTargetableRoles($user);

        return view('announcements.edit', compact('announcement', 'classes', 'roles'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorize('update', $announcement);

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'target_roles'     => 'nullable|array',
            'target_roles.*'   => 'string',
            'target_class_ids' => 'nullable|array',
            'target_class_ids.*' => 'uuid|exists:school_classes,id',
            'is_pinned'        => 'boolean',
            'expires_at'       => 'nullable|date',
            'priority'         => 'in:normal,urgent',
        ]);

        $announcement->update([
            'title'            => $data['title'],
            'content'          => $data['content'],
            'target_roles'     => $data['target_roles'] ?? null,
            'target_class_ids' => $data['target_class_ids'] ?? null,
            'is_pinned'        => $request->boolean('is_pinned'),
            'expires_at'       => $data['expires_at'] ?? null,
            'priority'         => $data['priority'] ?? 'normal',
        ]);

        return redirect()->route('announcements.show', $announcement)
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    // ─── Publish ─────────────────────────────────────────────────────────────

    public function publish(Announcement $announcement)
    {
        $this->authorize('publish', $announcement);

        if ($announcement->isPublished()) {
            return back()->with('error', 'Pengumuman sudah dipublikasikan.');
        }

        $announcement->update(['published_at' => Carbon::now()]);

        // Kirim Web Push ke semua target
        dispatch(fn() => $this->pushService->sendToAnnouncement($announcement))
            ->afterResponse();

        return redirect()->route('announcements.show', $announcement)
            ->with('success', 'Pengumuman berhasil dipublikasikan dan notifikasi dikirim!');
    }

    // ─── Unpublish ───────────────────────────────────────────────────────────

    public function unpublish(Announcement $announcement)
    {
        $this->authorize('publish', $announcement);

        $announcement->update(['published_at' => null]);

        return back()->with('success', 'Pengumuman dikembalikan ke draft.');
    }

    // ─── Delete ──────────────────────────────────────────────────────────────

    public function destroy(Announcement $announcement)
    {
        $this->authorize('delete', $announcement);

        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }

    // ─── API: Unread count (untuk badge navbar) ──────────────────────────────

    public function unreadCount()
    {
        $user    = Auth::user();
        $role    = $user->role;
        $classId = $user->student?->currentClass?->school_class_id;

        $query = Announcement::published()
            ->forRole($role)
            ->where('school_id', $user->school_id)
            ->whereDoesntHave('reads', fn($q) => $q->where('user_id', $user->id));

        if ($classId) {
            $query->forClass($classId);
        }

        return response()->json(['count' => $query->count()]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function getTargetableRoles(object $user): array
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return ['Guru', 'Siswa', 'Orang Tua', 'Kepala Sekolah'];
        }

        // Guru hanya bisa target siswa & ortu di kelasnya
        return ['Siswa', 'Orang Tua'];
    }
}

