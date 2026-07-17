<?php

namespace App\Http\Controllers;

use App\Models\LmsAssignment;
use App\Models\LmsSubmission;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Controller Pengumpulan Tugas (Submission).
 *
 * Role logic:
 * - Siswa  : index → lihat submission milik sendiri
 *            show  → lihat detail milik sendiri
 *            store → kumpulkan jawaban
 * - Guru   : index → lihat semua submission untuk tugas yang diajarnya
 *            show  → lihat detail submission siswa
 *            update → beri nilai / feedback
 * - Admin/Kepsek : akses penuh (lihat saja)
 */
class LmsSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = LmsSubmission::with(['assignment', 'student.user'])->latest();

        if ($user->hasRole('Siswa')) {
            // Siswa hanya lihat submission milik sendiri (student_id = users.id)
            $query->where('student_id', $user->id);
        } elseif ($user->hasRole('Guru')) {
            // Guru dapat melihat semua submission (tidak terbatas pada assignment yang dia buat)
            // Jika diperlukan filter lebih spesifik, dapat ditambahkan nanti.
            // Saat ini kami mengizinkan guru melihat seluruh submission untuk memudahkan monitoring.
        }
        // Admin/Kepsek: lihat semua (no filter)

        // Filter tambahan
        if ($request->filled('assignment_id')) {
            $query->where('assignment_id', $request->assignment_id);
        }

        $submissions = $query->paginate(15)->withQueryString();

        // Untuk filter dropdown
        if ($user->hasRole('Siswa')) {
            $assignments = LmsAssignment::whereHas('submissions', function ($q) use ($user) {
                $q->where('student_id', $user->id);
            })->get();
        } elseif ($user->hasRole('Guru')) {
            $assignments = LmsAssignment::where('teacher_id', $user->id)->get();
        } else {
            $assignments = LmsAssignment::all();
        }

        return view('admin.lms.submissions.index', compact('submissions', 'assignments'));
    }

    public function show(LmsSubmission $lmsSubmission)
    {
        $user = Auth::user();

        // Siswa hanya bisa lihat milik sendiri
        if ($user->hasRole('Siswa')) {
            abort_if($lmsSubmission->student_id !== $user->id, 403, 'Tidak bisa melihat submission orang lain.');
        }

        // Guru dapat melihat semua submission tanpa batasan assignment
        // Jika nanti diperlukan filter lebih spesifik, dapat ditambahkan kembali.
        // Saat ini guru boleh melihat detail submission siswa mana pun.


        $lmsSubmission->load(['assignment.subject', 'student.user']);

        return view('admin.lms.submissions.show', compact('lmsSubmission'));
    }

    /**
     * Siswa mengumpulkan tugas.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user->hasRole('Siswa'), 403, 'Hanya siswa yang bisa mengumpulkan tugas.');
        // student_id di lms_submissions FK ke users.id
        $studentId = $user->id;

        $request->validate([
            'assignment_id' => 'required|exists:lms_assignments,id',
            'notes'         => 'nullable|string|max:1000',
            'file'          => 'nullable|file|mimes:pdf,doc,docx,zip,jpg,jpeg,png|max:20480',
        ]);

        // Pastikan tidak double submit
        $existing = LmsSubmission::where('assignment_id', $request->assignment_id)
            ->where('student_id', $studentId)
            ->first();

        $filePath = $existing?->file_path;
        if ($request->hasFile('file')) {
            // Hapus file lama jika ada
            if ($filePath) Storage::disk('public')->delete($filePath);
            $filePath = $request->file('file')->store('lms/submissions', 'public');
        }

        LmsSubmission::updateOrCreate(
            ['assignment_id' => $request->assignment_id, 'student_id' => $studentId],
            ['notes' => $request->notes, 'file_path' => $filePath, 'submitted_at' => now()]
        );

        return back()->with('success', 'Tugas berhasil dikumpulkan!');
    }

    /**
     * Guru memberi nilai / feedback pada submission.
     */
    public function update(Request $request, LmsSubmission $lmsSubmission)
    {
        $user = Auth::user();

        // Guru dapat melihat semua submission tanpa batasan assignment
        // Jika diperlukan filter lebih spesifik, dapat ditambahkan kembali.
        // Saat ini guru boleh melihat detail submission siswa mana pun.


        $request->validate([
            'score'    => 'nullable|numeric|min:0|max:100',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $lmsSubmission->update([
            'score'    => $request->score,
            'feedback' => $request->feedback,
            'graded_at' => $request->score !== null ? now() : null,
        ]);

        // Jika nilai sudah diset, coba masukkan ke student_grades (sumber: lms)
        if ($request->score !== null) {
            $this->syncToStudentGrades($lmsSubmission, $request->score);
        }

        return back()->with('success', 'Nilai berhasil disimpan.');
    }

    /**
     * Setelah guru memberi nilai di submission,
     * sinkronkan ke student_grades agar kalkulasi rapor otomatis.
     */
    private function syncToStudentGrades(LmsSubmission $submission, float $score): void
    {
        $assignment = $submission->assignment;
        if (!$assignment) return;

        // Cari semester aktif
        $semester = \App\Models\Semester::where('school_id', $assignment->school_id)
            ->where('is_active', true)
            ->first();
        if (!$semester) return;

        // Tentukan component_type berdasarkan assignment type (gunakan 'tugas' sebagai default)
        $componentType = 'tugas';

        \App\Models\StudentGrade::updateOrCreate(
            [
                'student_id'     => $submission->student_id, // ini adalah users.id
                'subject_id'     => $assignment->subject_id,
                'semester_id'    => $semester->id,
                'component_type' => $componentType,
                'assignment_id'  => $assignment->id,
            ],
            [
                'score'       => $score,
                'source'      => 'lms',
                'inputted_by' => Auth::id(),
            ]
        );

        // Hitung ulang rapor otomatis
        \App\Models\ReportCard::recalculate(
            $submission->student_id,
            $assignment->subject_id,
            $semester->id
        );
    }
}
