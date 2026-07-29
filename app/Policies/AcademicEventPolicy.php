<?php

namespace App\Policies;

use App\Models\AcademicEvent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy untuk academic_events.
 *
 * - Admin/Kepsek: CRUD penuh (semua event, semua kelas, kategori apapun)
 * - Guru: hanya bisa CRUD event yang dia buat sendiri, untuk kelas apapun yang aktif
 *         (dibatasi lebih ketat setelah modul Jadwal Pelajaran selesai)
 *         TIDAK BOLEH pilih kategori is_holiday=true (validasi di Form Request)
 * - Siswa/Ortu: read-only
 */
class AcademicEventPolicy
{
    use HandlesAuthorization;

    /** Semua role yang login bisa melihat daftar event */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Semua role yang login bisa melihat detail event */
    public function view(User $user, AcademicEvent $event): bool
    {
        return true;
    }

    /** Admin/Kepsek/Guru bisa membuat event */
    public function create(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah', 'Guru']);
    }

    /**
     * Admin/Kepsek bisa update semua event.
     * Guru hanya bisa update event yang dia buat sendiri.
     */
    public function update(User $user, AcademicEvent $event): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        if ($user->hasRole('Guru')) {
            return $event->created_by === $user->id;
        }

        return false;
    }

    /**
     * Admin/Kepsek bisa hapus semua event.
     * Guru hanya bisa hapus event yang dia buat sendiri.
     */
    public function delete(User $user, AcademicEvent $event): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        if ($user->hasRole('Guru')) {
            return $event->created_by === $user->id;
        }

        return false;
    }
}
