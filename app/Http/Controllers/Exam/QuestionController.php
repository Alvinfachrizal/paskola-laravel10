<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * QuestionController — Manajemen Bank Soal
 *
 * Route → Controller → Model → View:
 *   GET  /exam/questions          → index()   → daftar soal milik guru
 *   GET  /exam/questions/create   → create()  → form tambah soal
 *   POST /exam/questions          → store()   → simpan soal baru
 *   GET  /exam/questions/{id}/edit → edit()   → form edit soal
 *   PUT  /exam/questions/{id}     → update()  → update soal
 *   DELETE /exam/questions/{id}   → destroy() → hapus soal
 */
class QuestionController extends Controller
{
    // ── Index: Daftar Soal ──────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('viewAny', Question::class);

        $user = auth()->user();

        $query = Question::with(['subject', 'options'])
            ->where('school_id', $user->school_id)
            ->where('in_bank', true); // hanya tampilkan soal dari bank soal

        // Filter per role
        if ($user->hasRole('Guru')) {
            $query->where('teacher_id', $user->id);
        }

        // Filter mapel
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Cari teks soal
        if ($request->filled('search')) {
            $query->where('question_text', 'like', '%' . $request->search . '%');
        }

        $questions = $query->latest()->paginate(15)->withQueryString();

        // Mapel yang tersedia untuk filter
        $subjects = Subject::where('school_id', $user->school_id)->orderBy('name')->get();

        return view('exam.questions.index', compact('questions', 'subjects'));
    }

    // ── Create: Form Tambah Soal ─────────────────────────────────────────────

    public function create()
    {
        $this->authorize('create', Question::class);

        $user   = auth()->user();
        $subjects = Subject::where('school_id', $user->school_id)->orderBy('name')->get();

        return view('exam.questions.create', compact('subjects'));
    }

    // ── Store: Simpan Soal Baru ──────────────────────────────────────────────

    public function store(Request $request)
    {
        $this->authorize('create', Question::class);

        $validated = $request->validate([
            'subject_id'          => 'required|uuid|exists:subjects,id',
            'question_text'       => 'required|string|max:5000',
            'question_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'in_bank'             => 'boolean',
            'options'             => 'required|array|min:2|max:5',
            'options.*.text'      => 'nullable|string|max:1000',
            'options.*.image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'correct_option'      => 'required|integer|min:0|max:4',
        ], [
            'correct_option.required' => 'Pilih salah satu jawaban yang benar.',
        ]);

        // Validasi: setiap pilihan harus punya teks atau gambar
        foreach ($request->options as $idx => $opt) {
            $hasText  = !empty($opt['text']);
            $hasImage = $request->hasFile("options.{$idx}.image");
            if (!$hasText && !$hasImage) {
                return back()->withErrors(["options.{$idx}" => "Pilihan " . chr(65 + $idx) . " harus memiliki teks atau gambar."])->withInput();
            }
        }

        DB::transaction(function () use ($request, $validated) {
            $user = auth()->user();

            // Upload gambar soal
            $questionImagePath = null;
            if ($request->hasFile('question_image')) {
                $questionImagePath = $this->uploadImage($request->file('question_image'), 'questions');
            }

            $question = Question::create([
                'school_id'     => $user->school_id,
                'teacher_id'    => $user->id,
                'subject_id'    => $validated['subject_id'],
                'question_text' => $validated['question_text'],
                'image_path'    => $questionImagePath,
                'in_bank'       => $request->boolean('in_bank', true),
            ]);

            // Simpan pilihan jawaban
            foreach ($request->options as $idx => $opt) {
                $optImagePath = null;
                if ($request->hasFile("options.{$idx}.image")) {
                    $optImagePath = $this->uploadImage($request->file("options.{$idx}.image"), 'question-options');
                }

                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $opt['text'] ?? null,
                    'image_path'  => $optImagePath,
                    'is_correct'  => ((int)$request->correct_option === (int)$idx),
                    'position'    => $idx + 1,
                ]);
            }
        });

        return redirect()->route('exam.questions.index')
            ->with('success', 'Soal berhasil disimpan ke bank soal.');
    }

    // ── Edit: Form Edit Soal ─────────────────────────────────────────────────

    public function edit(Question $question)
    {
        $this->authorize('update', $question);

        $subjects = Subject::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
        $question->load('options');

        return view('exam.questions.edit', compact('question', 'subjects'));
    }

    // ── Update: Simpan Perubahan ─────────────────────────────────────────────

    public function update(Request $request, Question $question)
    {
        $this->authorize('update', $question);

        $validated = $request->validate([
            'subject_id'          => 'required|uuid|exists:subjects,id',
            'question_text'       => 'required|string|max:5000',
            'question_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'options'             => 'required|array|min:2|max:5',
            'options.*.id'        => 'nullable|uuid',
            'options.*.text'      => 'nullable|string|max:1000',
            'options.*.image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'correct_option'      => 'required|integer|min:0|max:4',
        ], [
            'correct_option.required' => 'Pilih salah satu jawaban yang benar.',
        ]);

        foreach ($request->options as $idx => $opt) {
            $hasText  = !empty($opt['text']);
            $hasImage = $request->hasFile("options.{$idx}.image")
                || (!empty($opt['id']) && QuestionOption::find($opt['id'])?->image_path);
            if (!$hasText && !$hasImage) {
                return back()->withErrors(["options" => "Pilihan " . chr(65 + $idx) . " harus memiliki teks atau gambar."])->withInput();
            }
        }

        DB::transaction(function () use ($request, $validated, $question) {
            // Update gambar soal
            if ($request->hasFile('question_image')) {
                if ($question->image_path) {
                    Storage::disk('public')->delete($question->image_path);
                }
                $question->image_path = $this->uploadImage($request->file('question_image'), 'questions');
            }

            $question->update([
                'subject_id'    => $validated['subject_id'],
                'question_text' => $validated['question_text'],
                'image_path'    => $question->image_path,
            ]);

            // Hapus opsi lama, buat ulang
            $question->options()->delete();

            foreach ($request->options as $idx => $opt) {
                $optImagePath = null;

                // Pertahankan gambar lama jika tidak diupload yang baru
                if (!empty($opt['id']) && !$request->hasFile("options.{$idx}.image")) {
                    $oldOpt = QuestionOption::withTrashed()->find($opt['id']);
                    $optImagePath = $oldOpt?->image_path;
                }

                if ($request->hasFile("options.{$idx}.image")) {
                    $optImagePath = $this->uploadImage($request->file("options.{$idx}.image"), 'question-options');
                }

                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $opt['text'] ?? null,
                    'image_path'  => $optImagePath,
                    'is_correct'  => ((int)$request->correct_option === (int)$idx),
                    'position'    => $idx + 1,
                ]);
            }
        });

        return redirect()->route('exam.questions.index')
            ->with('success', 'Soal berhasil diperbarui.');
    }

    // ── Destroy: Hapus Soal ──────────────────────────────────────────────────

    public function destroy(Question $question)
    {
        $this->authorize('delete', $question);

        // Hapus gambar terkait
        if ($question->image_path) {
            Storage::disk('public')->delete($question->image_path);
        }
        foreach ($question->options as $opt) {
            if ($opt->image_path) {
                Storage::disk('public')->delete($opt->image_path);
            }
        }

        $question->delete();

        return redirect()->route('exam.questions.index')
            ->with('success', 'Soal berhasil dihapus.');
    }

    // ── Private: Upload Helper ───────────────────────────────────────────────

    /**
     * Upload gambar dengan nama file yang diacak agar tidak bisa ditebak.
     */
    private function uploadImage($file, string $folder): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename  = Str::random(32) . '.' . $extension;

        return $file->storeAs($folder, $filename, 'public');
    }
}
