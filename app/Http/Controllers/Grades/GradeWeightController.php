<?php

namespace App\Http\Controllers\Grades;

use App\Enums\GradeComponentType;
use App\Http\Controllers\Controller;
use App\Models\GradeWeight;
use App\Models\ReportCard;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controller: Manajemen Bobot Nilai (Grade Weights).
 * Guru mapel mengatur berapa persen bobot setiap komponen nilai
 * untuk mapel yang diampu, per semester. Total HARUS = 100%.
 */
class GradeWeightController extends Controller
{
    public function index(Request $request)
    {
        $user      = Auth::user();
        $semesters = Semester::orderByDesc('academic_year')->orderByDesc('term')->get();
        $activeSem = $semesters->firstWhere('is_active', true) ?? $semesters->first();
        $semId     = $request->get('semester_id', $activeSem?->id);

        // Admin: semua mapel. Guru: hanya mapel yang diampu
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
            $subjects = Subject::where('is_active', true)->get();
        } else {
            $subjectIds = \App\Models\LmsAssignment::where('teacher_id', $user->id)->pluck('subject_id')->unique();
            $subjects   = Subject::whereIn('id', $subjectIds)->where('is_active', true)->get();
        }

        $subjectId = $request->get('subject_id', $subjects->first()?->id);
        $weights   = collect();
        $totalPct  = 0;

        if ($subjectId && $semId) {
            $weights  = GradeWeight::where('subject_id', $subjectId)->where('semester_id', $semId)->get();
            $totalPct = $weights->sum('weight_percent');
        }

        $components = GradeComponentType::cases();

        return view('grades.weights', compact(
            'semesters', 'semId', 'subjects', 'subjectId', 'weights', 'totalPct', 'components'
        ));
    }

    /**
     * Simpan bobot sekaligus (semua komponen dalam satu form).
     * Validasi: total harus tepat 100%.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id'        => 'required|exists:subjects,id',
            'semester_id'       => 'required|exists:semesters,id',
            'weights'           => 'required|array',
            'weights.*'         => 'required|integer|min:0|max:100',
        ]);

        // Validasi total = 100
        $total = array_sum($validated['weights']);
        if ($total !== 100) {
            return back()
                ->withInput()
                ->withErrors(['weights' => "Total bobot harus tepat 100%. Saat ini: {$total}%."]);
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated['weights'] as $component => $pct) {
                if ($pct == 0) {
                    // Hapus komponen yang bobotnya 0 (tidak dipakai)
                    GradeWeight::where('subject_id', $validated['subject_id'])
                        ->where('semester_id', $validated['semester_id'])
                        ->where('component_type', $component)
                        ->delete();
                    continue;
                }
                GradeWeight::updateOrCreate(
                    [
                        'subject_id'     => $validated['subject_id'],
                        'semester_id'    => $validated['semester_id'],
                        'component_type' => $component,
                    ],
                    ['weight_percent' => $pct]
                );
            }

            // Hitung ulang semua rapor yang terdampak perubahan bobot ini
            $affected = \App\Models\StudentGrade::where('subject_id', $validated['subject_id'])
                ->where('semester_id', $validated['semester_id'])
                ->select('student_id')
                ->distinct()
                ->pluck('student_id');

            foreach ($affected as $studentId) {
                ReportCard::recalculate($studentId, $validated['subject_id'], $validated['semester_id']);
            }
        });

        return back()->with('success', 'Bobot nilai berhasil disimpan. Semua rapor terdampak telah dihitung ulang.');
    }
}
