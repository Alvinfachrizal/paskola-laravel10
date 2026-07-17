<?php

namespace App\Policies;

use App\Enums\ReportCardStatus;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy untuk tabel report_cards.
 *
 * Aturan dari prompt:
 * - Wali kelas: read semua rapor kelas + bisa ubah status jadi perlu_verifikasi atau terverifikasi.
 *   TIDAK BOLEH ubah score langsung.
 * - Admin/Kepsek: full akses + bisa publish.
 * - Siswa/Ortu: read-only, HANYA rapor milik siswa sendiri yang sudah published.
 * - Guru mapel: hanya bisa lihat rapor untuk mapel yang diampu.
 */
class ReportCardPolicy
{
    use HandlesAuthorization;

    /** Siapa yang boleh melihat rapor? */
    public function view(User $user, ReportCard $reportCard): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        // Wali kelas: kelas yang diwalikan
        if ($user->hasRole('Guru') && $this->isHomeRoomTeacherFor($user, $reportCard->student_id)) {
            return true;
        }

        // Siswa: HANYA rapor milik sendiri yang published
        if ($user->hasRole('Siswa')) {
            $studentId = $user->student?->id;
            return $studentId === $reportCard->student_id
                && $reportCard->status === ReportCardStatus::Published;
        }

        // Ortu: hanya rapor anak mereka yang published
        if ($user->hasRole('Orang Tua')) {
            $childStudentId = $user->student?->id; // asumsi relasi ortu→siswa
            return $childStudentId === $reportCard->student_id
                && $reportCard->status === ReportCardStatus::Published;
        }

        return false;
    }

    /** Wali kelas dan admin bisa update status rapor */
    public function updateStatus(User $user, ReportCard $reportCard): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            return true;
        }

        // Wali kelas hanya bisa update rapor kelas yang diwalikan
        if ($user->hasRole('Guru')) {
            return $this->isHomeRoomTeacherFor($user, $reportCard->student_id);
        }

        return false;
    }

    /** Hanya admin/kepsek yang bisa publish rapor */
    public function publish(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah']);
    }

    // ─── Private Helper ────────────────────────────────────────────────────

    private function isHomeRoomTeacherFor(User $user, string $studentId): bool
    {
        $classId = StudentClass::where('student_id', $studentId)->value('class_id');
        if (!$classId) return false;

        return SchoolClass::where('id', $classId)
            ->where('homeroom_teacher_id', $user->id)
            ->exists();
    }
}
