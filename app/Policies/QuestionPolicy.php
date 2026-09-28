<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * QuestionPolicy — atur akses ke bank soal
 *
 * Aturan:
 * - Guru: CRUD soal miliknya sendiri HANYA untuk mapel yang dia ajar
 * - Admin/Kepsek: Read-only (lihat semua soal)
 * - Siswa/Ortu: Tidak ada akses
 */
class QuestionPolicy
{
    use HandlesAuthorization;

    /** Admin/Kepsek bisa lihat semua soal (read-only). Guru hanya miliknya. */
    public function view(User $user, Question $question): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        if ($user->hasRole('Guru')) {
            return $question->teacher_id === $user->id;
        }

        return false;
    }

    /** Index bank soal: Guru dan Admin/Kepsek boleh melihat daftar */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah', 'Guru']);
    }

    /**
     * Guru bisa membuat soal hanya untuk mapel yang dia ajar.
     * Pengecekan mapel dilakukan di controller dengan data subject_id dari request.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Guru');
    }

    /** Guru hanya bisa edit soal miliknya sendiri */
    public function update(User $user, Question $question): bool
    {
        if (!$user->hasRole('Guru')) {
            return false;
        }

        return $question->teacher_id === $user->id;
    }

    /** Guru hanya bisa hapus soal miliknya, dan soal tidak sedang dipakai di ujian aktif */
    public function delete(User $user, Question $question): bool
    {
        if (!$user->hasRole('Guru')) {
            return false;
        }

        if ($question->teacher_id !== $user->id) {
            return false;
        }

        // Soal yang sudah masuk ke exam tidak boleh dihapus
        return !$question->exams()->exists();
    }
}
