<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $students = Student::where('school_id', $schoolId)->orderBy('name')->get();
        return view('admin.students.index', compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nisn' => 'nullable|string|max:50',
            'nis' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'gender' => 'required|in:L,P',
            'phone' => 'nullable|string|max:20',
            'entry_year' => 'nullable|integer',
            'status' => 'required|in:active,inactive,graduated,dropped_out',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
        ]);
        
        $schoolId = $request->user()->school_id;

        DB::beginTransaction();
        try {
            // Create User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'school_id' => $schoolId,
                'phone' => $validated['phone'],
                'role' => 'Siswa',
                'is_active' => $validated['status'] === 'active',
            ]);
            $user->assignRole('Siswa');

            // Create Student
            Student::create([
                'user_id' => $user->id,
                'school_id' => $schoolId,
                'nisn' => $validated['nisn'],
                'nis' => $validated['nis'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'phone' => $validated['phone'],
                'entry_year' => $validated['entry_year'],
                'status' => $validated['status'],
                'parent_name' => $validated['parent_name'],
                'parent_phone' => $validated['parent_phone'],
            ]);

            DB::commit();
            return redirect()->route('admin.students.index')->with('success', 'Data Siswa berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        return redirect()->route('admin.students.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'nisn' => 'nullable|string|max:50',
            'nis' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$student->user_id,
            'password' => ['nullable', Rules\Password::defaults()],
            'gender' => 'required|in:L,P',
            'phone' => 'nullable|string|max:20',
            'entry_year' => 'nullable|integer',
            'status' => 'required|in:active,inactive,graduated,dropped_out',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // Update User
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'is_active' => $validated['status'] === 'active',
            ];
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }
            $student->user->update($userData);

            // Update Student
            $student->update([
                'nisn' => $validated['nisn'],
                'nis' => $validated['nis'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'phone' => $validated['phone'],
                'entry_year' => $validated['entry_year'],
                'status' => $validated['status'],
                'parent_name' => $validated['parent_name'],
                'parent_phone' => $validated['parent_phone'],
            ]);

            DB::commit();
            return redirect()->route('admin.students.index')->with('success', 'Data Siswa berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        DB::beginTransaction();
        try {
            $user = $student->user;
            $student->delete();
            if ($user) {
                $user->delete();
            }
            DB::commit();
            return redirect()->route('admin.students.index')->with('success', 'Data Siswa berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
