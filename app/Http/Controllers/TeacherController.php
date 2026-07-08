<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $teachers = Teacher::where('school_id', $schoolId)->orderBy('name')->get();
        return view('admin.teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.teachers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'gender' => 'required|in:L,P',
            'phone' => 'nullable|string|max:20',
            'subject_specialty' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,retired',
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
                'role' => 'Guru',
                'is_active' => $validated['status'] === 'active',
            ]);
            $user->assignRole('Guru');

            // Create Teacher
            Teacher::create([
                'user_id' => $user->id,
                'school_id' => $schoolId,
                'nip' => $validated['nip'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'phone' => $validated['phone'],
                'subject_specialty' => $validated['subject_specialty'],
                'status' => $validated['status'],
            ]);

            DB::commit();
            return redirect()->route('admin.teachers.index')->with('success', 'Data Guru berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        return redirect()->route('admin.teachers.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher)
    {
        return view('admin.teachers.edit', compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'nip' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$teacher->user_id,
            'password' => ['nullable', Rules\Password::defaults()],
            'gender' => 'required|in:L,P',
            'phone' => 'nullable|string|max:20',
            'subject_specialty' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,retired',
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
            $teacher->user->update($userData);

            // Update Teacher
            $teacher->update([
                'nip' => $validated['nip'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'phone' => $validated['phone'],
                'subject_specialty' => $validated['subject_specialty'],
                'status' => $validated['status'],
            ]);

            DB::commit();
            return redirect()->route('admin.teachers.index')->with('success', 'Data Guru berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        DB::beginTransaction();
        try {
            $user = $teacher->user;
            $teacher->delete();
            if ($user) {
                $user->delete();
            }
            DB::commit();
            return redirect()->route('admin.teachers.index')->with('success', 'Data Guru berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
