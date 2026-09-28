<?php

namespace App\Policies;

use App\Models\ExamParticipant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ExamParticipantPolicy — atur akses ke data peserta
 *
 * Aturan:
 * - Siswa: hanya bisa mengakses baris exam_participants miliknya sendiri
 * - Guru: hanya lihat peserta ujian miliknya
 * - Admin/Kepsek: Read-only
 */
class ExamParticipantPolicy
{
    use HandlesAuthorization;

    /** Siswa hanya akses baris miliknya. Guru akses peserta ujiannya. Admin/Kepsek read-only. */
    public function view(User $user, ExamParticipant $participant): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        if ($user->hasRole('Guru')) {
            // Guru hanya lihat peserta dari ujian miliknya
            return $participant->exam->teacher_id === $user->id;
        }

        if ($user->hasRole('Siswa')) {
            // Siswa hanya lihat miliknya sendiri
            return $participant->student->user_id === $user->id;
        }

        return false;
    }

    /**
     * Apakah user bisa mengerjakan ujian ini?
     * Hanya Siswa pemilik baris ini yang boleh.
     */
    public function takeExam(User $user, ExamParticipant $participant): bool
    {
        return $user->hasRole('Siswa')
            && $participant->student->user_id === $user->id;
    }
}
