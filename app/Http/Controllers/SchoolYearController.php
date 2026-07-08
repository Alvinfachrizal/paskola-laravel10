<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Http\Requests\StoreSchoolYearRequest;
use App\Http\Requests\UpdateSchoolYearRequest;
use Illuminate\Http\Request;

class SchoolYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $schoolYears = SchoolYear::where('school_id', $schoolId)->orderBy('start_date', 'desc')->get();
        return view('admin.school-years.index', compact('schoolYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.school-years.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSchoolYearRequest $request)
    {
        $validated = $request->validated();
        $validated['school_id'] = $request->user()->school_id;
        
        // If this one is set as active, maybe deactivate others? (Business logic decision, optional)
        if (!empty($validated['is_active'])) {
            SchoolYear::where('school_id', $validated['school_id'])->update(['is_active' => false]);
        } else {
            $validated['is_active'] = false;
        }

        SchoolYear::create($validated);

        return redirect()->route('admin.school-years.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolYear $schoolYear)
    {
        // Not implemented for now, mostly using index and edit
        return redirect()->route('admin.school-years.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolYear $schoolYear)
    {
        return view('admin.school-years.edit', compact('schoolYear'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSchoolYearRequest $request, SchoolYear $schoolYear)
    {
        $validated = $request->validated();

        if (!empty($validated['is_active'])) {
            SchoolYear::where('school_id', $schoolYear->school_id)
                ->where('id', '!=', $schoolYear->id)
                ->update(['is_active' => false]);
            $validated['is_active'] = true;
        } else {
            $validated['is_active'] = false;
        }

        $schoolYear->update($validated);

        return redirect()->route('admin.school-years.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchoolYear $schoolYear)
    {
        $schoolYear->delete();
        return redirect()->route('admin.school-years.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
