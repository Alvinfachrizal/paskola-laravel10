<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy untuk Announcement.
 *
 * - Admin / Kepala Sekolah: CRUD penuh semua pengumuman
 * - Guru: hanya bisa buat & kelola pengumuman miliknya sendiri (target kelas)
 * - Siswa / Ortu: read-only (hanya yang published & ditujukan ke mereka)
 */
class AnnouncementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // semua role bisa akses halaman list
    }

    public function view(User $user, Announcement $announcement): bool
    {
        // Admin & Kepsek: lihat semua termasuk draft
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        // Guru: lihat miliknya sendiri (draft) + yang published
        if ($user->hasRole('Guru')) {
            return $announcement->author_id === $user->id || $announcement->isPublished();
        }

        // Siswa & Ortu: hanya yang published
        return $announcement->isPublished();
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah', 'Guru']);
    }

    public function update(User $user, Announcement $announcement): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        // Guru hanya bisa edit miliknya sendiri & belum dipublish
        return $user->hasRole('Guru')
            && $announcement->author_id === $user->id
            && $announcement->isDraft();
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        return $user->hasRole('Guru') && $announcement->author_id === $user->id;
    }

    public function publish(User $user, Announcement $announcement): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        // Guru bisa publish pengumuman miliknya
        return $user->hasRole('Guru') && $announcement->author_id === $user->id;
    }
}

