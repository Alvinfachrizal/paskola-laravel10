<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LmsMaterial;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LmsMaterialController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = LmsMaterial::with(['teacher', 'schoolClass', 'subject'])->latest();

        if ($user->hasRole('Guru')) {
            $query->where('teacher_id', $user->id);
        } elseif ($user->hasRole('Siswa')) {
            if ($user->student && $user->student->classes->count() > 0) {
                $classIds = $user->student->classes->pluck('id');
                $query->whereIn('class_id', $classIds);
            }
        }

        $materials = $query->paginate(12);

        // Ambil data dropdown untuk modal (hanya untuk guru/admin)
        $classes = SchoolClass::all();
        $subjects = Subject::all();

        return view('admin.lms.materials.index', compact('materials', 'classes', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'description' => 'nullable|string',
            'type' => 'required|in:document,video,link',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip|max:10240',
            'link_url' => 'nullable|url',
        ]);

        $fileUrl = $request->link_url;

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('lms/materials', 'public');
            $fileUrl = Storage::url($path);
        }

        LmsMaterial::create([
            'school_id' => Auth::user()->school_id ?? '00000000-0000-0000-0000-000000000000', // Update with actual logic if multi-tenant
            'teacher_id' => Auth::id(),
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'file_url' => $fileUrl,
        ]);

        return redirect()->route('admin.lms-materials.index')->with('success', 'Materi berhasil diunggah!');
    }

    public function destroy(LmsMaterial $lmsMaterial)
    {
        if ($lmsMaterial->teacher_id !== Auth::id() && !Auth::user()->hasRole(['Super Admin', 'Admin'])) {
            return abort(403);
        }

        $lmsMaterial->delete();
        return redirect()->route('admin.lms-materials.index')->with('success', 'Materi berhasil dihapus!');
    }
}
