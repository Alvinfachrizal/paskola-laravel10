<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Major;
use App\Models\Teacher;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $classes = SchoolClass::with(['schoolYear', 'major', 'homeroomTeacher'])
            ->where('school_id', $schoolId)
            ->orderBy('grade')
            ->orderBy('name')
            ->get();
            
        $schoolYears = SchoolYear::where('school_id', $schoolId)->orderBy('start_date', 'desc')->get();
        $majors = Major::where('school_id', $schoolId)->orderBy('name')->get();
        
        return view('admin.classes.index', compact('classes', 'schoolYears', 'majors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $schoolYears = SchoolYear::where('school_id', $schoolId)->orderBy('start_date', 'desc')->get();
        $majors = Major::where('school_id', $schoolId)->orderBy('name')->get();
        $teachers = Teacher::where('school_id', $schoolId)->orderBy('name')->get();
        
        return view('admin.classes.create', compact('schoolYears', 'majors', 'teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_year_id' => 'required|uuid|exists:school_years,id',
            'major_id' => 'nullable|uuid|exists:majors,id',
            'homeroom_teacher_id' => 'nullable|uuid|exists:teachers,id',
            'name' => 'required|string|max:255',
            'grade' => 'required|integer|min:1',
            'room_number' => 'nullable|string|max:50',
            'max_students' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);
        
        $validated['school_id'] = $request->user()->school_id;
        $validated['is_active'] = $request->has('is_active');

        SchoolClass::create($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolClass $class)
    {
        return redirect()->route('admin.classes.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $class = SchoolClass::findOrFail($id);
        $schoolId = $request->user()->school_id;
        $schoolYears = SchoolYear::where('school_id', $schoolId)->orderBy('start_date', 'desc')->get();
        $majors = Major::where('school_id', $schoolId)->orderBy('name')->get();
        $teachers = Teacher::where('school_id', $schoolId)->orderBy('name')->get();
        
        return view('admin.classes.edit', compact('class', 'schoolYears', 'majors', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $class = SchoolClass::findOrFail($id);
        $validated = $request->validate([
            'school_year_id' => 'required|uuid|exists:school_years,id',
            'major_id' => 'nullable|uuid|exists:majors,id',
            'homeroom_teacher_id' => 'nullable|uuid|exists:teachers,id',
            'name' => 'required|string|max:255',
            'grade' => 'required|integer|min:1',
            'room_number' => 'nullable|string|max:50',
            'max_students' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $class->update($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $class = SchoolClass::findOrFail($id);
        $class->delete();
        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
