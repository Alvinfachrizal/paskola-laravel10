<?php

namespace App\Http\Controllers\Grades;

use App\Enums\GradeComponentType;
use App\Http\Controllers\Controller;
use App\Models\GradeChangeLog;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentGrade;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controller: Input Nilai Guru Mapel.
 *
 * Guru hanya bisa input nilai untuk mapel yang dia ajar
 * dan siswa yang ada di kelasnya.
 */
class StudentGradeController extends Controller
{
    /**
     * Halaman input nilai per kelas per mapel per semester.
     * Guru memilih kelas + mapel + semester, lalu tampil tabel semua siswa.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil semester aktif sebagai default
        $semesters = Semester::orderByDesc('academic_year')->orderByDesc('term')->get();
        $activeSemester = $semesters->firstWhere('is_active', true) ?? $semesters->first();
        $semesterId = $request->get('semester_id', $activeSemester?->id);

        // Admin/Kepsek lihat semua kelas. Guru hanya kelas yang dia ajar.
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            $classes  = SchoolClass::with('schoolYear')->get();
            $subjects = Subject::where('is_active', true)->get();
        } else {
            // Guru: kelas + mapel yang pernah dia masukkan tugas di LMS
            $teacherAssignments = \App\Models\LmsAssignment::where('teacher_id', $user->id)->get();
            $classIds   = $teacherAssignments->pluck('class_id')->unique();
            $subjectIds = $teacherAssignments->pluck('subject_id')->unique();
            $classes    = SchoolClass::whereIn('id', $classIds)->get();
            $subjects   = Subject::whereIn('id', $subjectIds)->where('is_active', true)->get();
        }

        $classId   = $request->get('class_id', $classes->first()?->id);
        $subjectId = $request->get('subject_id', $subjects->first()?->id);

        // Ambil siswa di kelas terpilih
        $students = collect();
        $grades   = collect();
        $weights  = collect();

        if ($classId && $subjectId && $semesterId) {
            $studentIds = StudentClass::where('class_id', $classId)->pluck('student_id');
            $students   = Student::whereIn('id', $studentIds)->with('user')->get();

            // Ambil semua nilai untuk kelas + mapel + semester ini
            $grades = StudentGrade::whereIn('student_id', $studentIds)
                ->where('subject_id', $subjectId)
                ->where('semester_id', $semesterId)
                ->get()
                ->groupBy('student_id');

            $weights = \App\Models\GradeWeight::where('subject_id', $subjectId)
                ->where('semester_id', $semesterId)
                ->get();
        }

        $components = GradeComponentType::cases();

        return view('grades.input', compact(
            'semesters', 'semesterId', 'classes', 'subjects',
            'classId', 'subjectId', 'students', 'grades', 'weights', 'components'
        ));
    }

    /**
     * Simpan nilai satu siswa (AJAX-friendly, satu baris per request).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'subject_id'     => 'required|exists:subjects,id',
            'semester_id'    => 'required|exists:semesters,id',
            'component_type' => 'required|in:' . implode(',', array_column(GradeComponentType::cases(), 'value')),
            'score'          => 'required|numeric|min:0|max:100',
            'notes'          => 'nullable|string|max:500',
            'reason'         => 'nullable|string|max:500', // alasan jika revisi
        ]);

        // Policy check: guru hanya bisa input untuk mapel+siswa yang diampu
        $this->authorize('create', [StudentGrade::class, $validated['subject_id'], $validated['student_id']]);

        DB::transaction(function () use ($validated) {
            $existing = StudentGrade::where('student_id', $validated['student_id'])
                ->where('subject_id', $validated['subject_id'])
                ->where('semester_id', $validated['semester_id'])
                ->where('component_type', $validated['component_type'])
                ->first();

            $oldScore = $existing?->score;

            // Upsert nilai
            $grade = StudentGrade::updateOrCreate(
                [
                    'student_id'     => $validated['student_id'],
                    'subject_id'     => $validated['subject_id'],
                    'semester_id'    => $validated['semester_id'],
                    'component_type' => $validated['component_type'],
                ],
                [
                    'score'        => $validated['score'],
                    'source'       => 'manual',
                    'inputted_by'  => Auth::id(),
                    'notes'        => $validated['notes'] ?? null,
                ]
            );

            // Catat log perubahan jika nilai sudah pernah ada sebelumnya
            if ($oldScore !== null && (float)$oldScore !== (float)$validated['score']) {
                GradeChangeLog::create([
                    'student_grade_id' => $grade->id,
                    'changed_by'       => Auth::id(),
                    'old_score'        => $oldScore,
                    'new_score'        => $validated['score'],
                    'reason'           => $validated['reason'] ?? null,
                ]);
            }

            // Hitung ulang rapor otomatis setiap nilai berubah
            ReportCard::recalculate(
                $validated['student_id'],
                $validated['subject_id'],
                $validated['semester_id']
            );
        });

        return back()->with('success', 'Nilai berhasil disimpan dan rapor dihitung ulang.');
    }
}
