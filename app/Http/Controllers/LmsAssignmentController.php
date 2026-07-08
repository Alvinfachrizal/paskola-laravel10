<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LmsAssignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LmsAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = LmsAssignment::with(['teacher', 'schoolClass', 'subject', 'submissions'])->latest();

        if ($user->hasRole('Guru')) {
            $query->where('teacher_id', $user->id);
        } elseif ($user->hasRole('Siswa')) {
            if ($user->student && $user->student->classes->count() > 0) {
                $classIds = $user->student->classes->pluck('id');
                $query->whereIn('class_id', $classIds);
            }
        }

        // Apply filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $assignments = $query->paginate(12);
        
        $classes = SchoolClass::all();
        $subjects = Subject::all();

        return view('admin.lms.assignments.index', compact('assignments', 'classes', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'description' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,jpg,jpeg,png|max:10240',
            'due_date' => 'required|date',
            'max_score' => 'nullable|integer|min:0|max:100',
        ]);

        $fileUrl = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('lms/assignments', 'public');
            $fileUrl = Storage::url($path);
        }

        LmsAssignment::create([
            'school_id' => Auth::user()->school_id ?? '00000000-0000-0000-0000-000000000000',
            'teacher_id' => Auth::id(),
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'file_url' => $fileUrl,
            'due_date' => $request->due_date,
            'max_score' => $request->max_score ?? 100,
        ]);

        return redirect()->route('admin.lms-assignments.index')->with('success', 'Tugas berhasil dibuat!');
    }

    public function destroy(LmsAssignment $lmsAssignment)
    {
        if ($lmsAssignment->teacher_id !== Auth::id() && !Auth::user()->hasRole(['Super Admin', 'Admin'])) {
            return abort(403);
        }

        $lmsAssignment->delete();
        return redirect()->route('admin.lms-assignments.index')->with('success', 'Tugas berhasil dihapus!');
    }
}
