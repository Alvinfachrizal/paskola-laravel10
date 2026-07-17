<?php

namespace App\Policies;

use App\Models\StudentGrade;
use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy untuk tabel student_grades.
 *
 * Aturan dari prompt:
 * - Guru mapel: CRUD nilai HANYA untuk subject_id yang diampu DAN
 *   student_id yang ada di kelas yang diajar guru tersebut.
 * - Wali kelas: Read-only untuk kelas yang diwalikan.
 * - Admin/Kepsek: Full access.
 * - Siswa/Ortu: Tidak bisa mengakses student_grades sama sekali.
 */
class StudentGradePolicy
{
    use HandlesAuthorization;

    /** Guru mapel bisa input nilai HANYA untuk mapel yang diampu */
    public function create(User $user, string $subjectId, string $studentId): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        if ($user->hasRole('Guru')) {
            return $this->teacherCanAccessSubjectAndStudent($user, $subjectId, $studentId);
        }

        return false;
    }

    /** Update nilai: hanya guru yang mengampu + siswa ada di kelasnya */
    public function update(User $user, StudentGrade $grade): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        if ($user->hasRole('Guru')) {
            return $this->teacherCanAccessSubjectAndStudent(
                $user,
                $grade->subject_id,
                $grade->student_id
            );
        }

        return false;
    }

    /** Wali kelas bisa read semua nilai kelas yang diwalikan */
    public function view(User $user, StudentGrade $grade): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        if ($user->hasRole('Guru')) {
            // Guru bisa lihat nilai yang dia ajar ATAU kelas yang dia walikan
            return $this->teacherCanAccessSubjectAndStudent($user, $grade->subject_id, $grade->student_id)
                || $this->isHomeRoomTeacherFor($user, $grade->student_id);
        }

        return false;
    }

    // ─── Private Helpers ───────────────────────────────────────────────────

    /**
     * Cek apakah guru mengampu subject_id tersebut DAN
     * student_id tersebut ada di kelas yang diajarnya.
     */
    private function teacherCanAccessSubjectAndStudent(
        User $user, string $subjectId, string $studentId
    ): bool {
        // Guru mengampu mapel ini di kelas mana saja?
        $teacherClassIds = \App\Models\LmsAssignment::where('teacher_id', $user->id)
            ->where('subject_id', $subjectId)
            ->pluck('class_id')
            ->unique();

        if ($teacherClassIds->isEmpty()) return false;

        // Apakah student tersebut ada di salah satu kelas itu?
        return \App\Models\StudentClass::whereIn('class_id', $teacherClassIds)
            ->where('student_id', $studentId)
            ->exists();
    }

    /** Cek apakah guru adalah wali kelas untuk kelas si student */
    private function isHomeRoomTeacherFor(User $user, string $studentId): bool
    {
        $studentClassId = \App\Models\StudentClass::where('student_id', $studentId)
            ->value('class_id');

        if (!$studentClassId) return false;

        return SchoolClass::where('id', $studentClassId)
            ->where('homeroom_teacher_id', $user->id)
            ->exists();
    }
}
