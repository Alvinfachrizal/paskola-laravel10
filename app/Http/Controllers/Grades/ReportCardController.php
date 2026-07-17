<?php

namespace App\Http\Controllers\Grades;

use App\Enums\GradeComponentType;
use App\Enums\ReportCardStatus;
use App\Http\Controllers\Controller;
use App\Models\GradeWeight;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller: Rapor & Verifikasi Wali Kelas.
 *
 * - Guru mapel: lihat rapor mapel yang diampu
 * - Wali kelas: verifikasi/kirim balik rapor kelas yang diwalikan
 * - Admin/Kepsek: publish rapor final
 * - Siswa/Ortu: lihat rapor sendiri yang sudah published
 */
class ReportCardController extends Controller
{
    /**
     * Dashboard rapor: guru lihat per mapel, wali kelas lihat per kelas.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $semesters = Semester::orderByDesc('academic_year')->orderByDesc('term')->get();
        $activeSemester = $semesters->firstWhere('is_active', true) ?? $semesters->first();
        $semesterId = $request->get('semester_id', $activeSemester?->id);

        // Siswa/Ortu: langsung tampilkan rapor mereka sendiri
        if ($user->hasAnyRole(['Siswa', 'Orang Tua'])) {
            return $this->studentView($user, $semesterId, $semesters);
        }

        // Wali kelas: tampilkan semua siswa di kelas yang diwalikan
        $myClass = SchoolClass::where('homeroom_teacher_id', $user->id)->first();

        // Guru mapel (bukan wali kelas): filter per mapel yang diampu
        $classId   = $request->get('class_id');
        $subjectId = $request->get('subject_id');
        $statusFilter = $request->get('status');

        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            $classes  = SchoolClass::all();
            $subjects = Subject::where('is_active', true)->get();
        } else {
            $teacherAssignments = \App\Models\LmsAssignment::where('teacher_id', $user->id)->get();
            $classes  = SchoolClass::whereIn('id', $teacherAssignments->pluck('class_id')->unique())->get();
            $subjects = Subject::whereIn('id', $teacherAssignments->pluck('subject_id')->unique())->get();

            // Jika wali kelas, tambahkan kelas yang diwalikan ke pilihan
            if ($myClass && !$classes->contains('id', $myClass->id)) {
                $classes->push($myClass);
            }
        }

        // Default ke kelas yang diwalikan jika ada
        $classId = $classId ?? $myClass?->id ?? $classes->first()?->id;

        $reportCards = collect();
        $students    = collect();

        if ($classId && $semesterId) {
            $studentIds = StudentClass::where('class_id', $classId)->pluck('student_id');
            $students   = Student::whereIn('id', $studentIds)->with('user')->get();

            $query = ReportCard::whereIn('student_id', $studentIds)
                ->where('semester_id', $semesterId)
                ->with(['subject', 'student.user', 'verifiedBy']);

            if ($subjectId) $query->where('subject_id', $subjectId);
            if ($statusFilter) $query->where('status', $statusFilter);

            $reportCards = $query->get()->groupBy('student_id');
        }

        $statusOptions = ReportCardStatus::options();

        return view('grades.report-cards.index', compact(
            'semesters', 'semesterId', 'classes', 'subjects', 'classId', 'subjectId',
            'reportCards', 'students', 'myClass', 'statusOptions', 'statusFilter'
        ));
    }

    /**
     * Wali kelas: update status rapor (perlu_verifikasi atau terverifikasi).
     */
    public function updateStatus(Request $request, ReportCard $reportCard)
    {
        $this->authorize('updateStatus', $reportCard);

        $validated = $request->validate([
            'status' => 'required|in:perlu_verifikasi,terverifikasi',
        ]);

        $update = ['status' => $validated['status']];

        if ($validated['status'] === 'terverifikasi') {
            $update['verified_by'] = Auth::id();
            $update['verified_at'] = now();
        }

        $reportCard->update($update);

        $label = ReportCardStatus::from($validated['status'])->label();
        return back()->with('success', "Status rapor diubah ke \"{$label}\".");
    }

    /**
     * Admin/Kepsek: publish semua rapor terverifikasi untuk satu kelas + semester.
     */
    public function publishBatch(Request $request)
    {
        $this->authorize('publish', ReportCard::class);

        $validated = $request->validate([
            'class_id'    => 'required|exists:school_classes,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $studentIds = StudentClass::where('class_id', $validated['class_id'])->pluck('student_id');

        $count = ReportCard::whereIn('student_id', $studentIds)
            ->where('semester_id', $validated['semester_id'])
            ->where('status', ReportCardStatus::Terverifikasi->value)
            ->update(['status' => ReportCardStatus::Published->value]);

        return back()->with('success', "{$count} rapor berhasil dipublish.");
    }

    /**
     * Siswa/Ortu: tampilkan rapor sendiri.
     */
    private function studentView($user, $semesterId, $semesters)
    {
        $studentId = $user->student?->id;

        $reportCards = ReportCard::where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->where('status', ReportCardStatus::Published->value)
            ->with(['subject', 'semester'])
            ->get();

        return view('grades.report-cards.student', compact('reportCards', 'semesters', 'semesterId'));
    }
}
