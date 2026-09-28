<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ExamPolicy — atur akses ke ujian
 *
 * Aturan:
 * - Guru: CRUD ujian HANYA untuk mapel + kelas yang dia ajar. Lihat hasil & approve hanya ujian miliknya.
 * - Admin/Kepsek: Read-only untuk semua ujian dan hasil
 * - Siswa: Hanya lihat ujian yang kelasnya terdaftar (dicek di controller)
 * - Ortu/Wali Kelas: Tidak ada akses di modul ini
 */
class ExamPolicy
{
    use HandlesAuthorization;

    /** Siapa yang bisa melihat daftar ujian */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah', 'Guru', 'Siswa']);
    }

    /** Admin/Kepsek lihat semua. Guru hanya ujian miliknya. Siswa hanya ujian kelasnya (dicek controller). */
    public function view(User $user, Exam $exam): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        if ($user->hasRole('Guru')) {
            return $exam->teacher_id === $user->id;
        }

        if ($user->hasRole('Siswa')) {
            return true; // controller yang memfilter kelas siswa
        }

        return false;
    }

    /** Hanya Guru yang bisa membuat ujian */
    public function create(User $user): bool
    {
        return $user->hasRole('Guru');
    }

    /** Guru hanya bisa edit ujian miliknya. Ujian yang sudah published tidak bisa diubah jadwal/soalnya. */
    public function update(User $user, Exam $exam): bool
    {
        if (!$user->hasRole('Guru')) {
            return false;
        }

        return $exam->teacher_id === $user->id;
    }

    /** Guru hanya bisa hapus ujian miliknya yang masih draft */
    public function delete(User $user, Exam $exam): bool
    {
        if (!$user->hasRole('Guru')) {
            return false;
        }

        if ($exam->teacher_id !== $user->id) {
            return false;
        }

        // Hanya ujian draft yang boleh dihapus
        return $exam->status->value === 'draft';
    }

    /** Publish ujian: hanya Guru pemilik ujian */
    public function publish(User $user, Exam $exam): bool
    {
        return $user->hasRole('Guru') && $exam->teacher_id === $user->id;
    }

    /** Lihat daftar hasil & detail jawaban: Guru pemilik + Admin/Kepsek */
    public function viewResults(User $user, Exam $exam): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        return $user->hasRole('Guru') && $exam->teacher_id === $user->id;
    }

    /** Approve nilai: hanya Guru pemilik ujian */
    public function approve(User $user, Exam $exam): bool
    {
        return $user->hasRole('Guru') && $exam->teacher_id === $user->id;
    }

    /** Buka ujian susulan: hanya Guru pemilik ujian */
    public function openMakeup(User $user, Exam $exam): bool
    {
        return $user->hasRole('Guru') && $exam->teacher_id === $user->id;
    }

    /** Sinkronkan peserta: hanya Guru pemilik ujian */
    public function syncParticipants(User $user, Exam $exam): bool
    {
        return $user->hasRole('Guru') && $exam->teacher_id === $user->id;
    }
}
