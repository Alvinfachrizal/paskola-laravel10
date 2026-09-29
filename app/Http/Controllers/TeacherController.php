<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $query = Teacher::where('school_id', $schoolId)
            ->with('user')
            ->orderBy('name');

        // Search: nama, NIP, atau email user
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('subject_specialty', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('email', 'like', "%{$search}%"));
            });
        }

        // Filter status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Stats tetap dari total keseluruhan (tidak terfilter)
        $totalTeachers = Teacher::where('school_id', $schoolId)->count();

        $teachers = $query->paginate(15)->withQueryString();

        return view('admin.teachers.index', compact('teachers', 'totalTeachers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $subjects = \App\Models\Subject::where('school_id', $schoolId)->orderBy('name')->get();
        return view('admin.teachers.create', compact('subjects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip'               => 'nullable|string|max:50',
            'name'              => 'required|string|max:255',
            'email'             => 'required|string|email|max:255|unique:users',
            'password'          => ['required', Rules\Password::defaults()],
            'gender'            => 'required|in:L,P',
            'phone'             => 'nullable|string|max:20',
            'address_ktp'       => 'required|string',
            'address_domicile'  => 'nullable|string',
            'subject_specialty' => 'nullable|string|max:255',
            'status'            => 'required|in:active,inactive,retired',
            'doc_ijazah_sd'     => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
            'doc_ijazah_smp'    => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
            'doc_ijazah_sma'    => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
            'doc_ijazah_s1'     => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
            'doc_npwp'          => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
        ]);

        // Jika centang "Alamat domisili sama dengan KTP", salin nilai KTP
        if ($request->boolean('same_address')) {
            $validated['address_domicile'] = $validated['address_ktp'];
        }
        
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

            $docData = [];
            $docs = ['doc_ijazah_sd', 'doc_ijazah_smp', 'doc_ijazah_sma', 'doc_ijazah_s1', 'doc_npwp'];
            foreach ($docs as $doc) {
                if ($request->hasFile($doc)) {
                    $docData[$doc] = $request->file($doc)->store('teachers/documents', 'public');
                } else {
                    $docData[$doc] = null;
                }
            }

            // Create Teacher
            Teacher::create(array_merge([
                'user_id'          => $user->id,
                'school_id'        => $schoolId,
                'nip'              => $validated['nip'],
                'name'             => $validated['name'],
                'gender'           => $validated['gender'],
                'phone'            => $validated['phone'],
                'address_ktp'      => $validated['address_ktp'] ?? null,
                'address_domicile' => $validated['address_domicile'] ?? null,
                'subject_specialty' => $validated['subject_specialty'],
                'status'           => $validated['status'],
            ], $docData));

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
    public function edit(Request $request, Teacher $teacher)
    {
        $schoolId = $request->user()->school_id;
        $subjects = \App\Models\Subject::where('school_id', $schoolId)->orderBy('name')->get();
        return view('admin.teachers.edit', compact('teacher', 'subjects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'nip'               => 'nullable|string|max:50',
            'name'              => 'required|string|max:255',
            'email'             => 'required|string|email|max:255|unique:users,email,'.$teacher->user_id,
            'password'          => ['nullable', Rules\Password::defaults()],
            'gender'            => 'required|in:L,P',
            'phone'             => 'nullable|string|max:20',
            'address_ktp'       => 'required|string',
            'address_domicile'  => 'nullable|string',
            'subject_specialty' => 'nullable|string|max:255',
            'status'            => 'required|in:active,inactive,retired',
            'doc_ijazah_sd'     => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
            'doc_ijazah_smp'    => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
            'doc_ijazah_sma'    => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
            'doc_ijazah_s1'     => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
            'doc_npwp'          => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
        ]);

        // Jika centang "Alamat domisili sama dengan KTP", salin nilai KTP
        if ($request->boolean('same_address')) {
            $validated['address_domicile'] = $validated['address_ktp'];
        }

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

            $docData = [];
            $docs = ['doc_ijazah_sd', 'doc_ijazah_smp', 'doc_ijazah_sma', 'doc_ijazah_s1', 'doc_npwp'];
            foreach ($docs as $doc) {
                if ($request->hasFile($doc)) {
                    // Delete old file if exists
                    if ($teacher->$doc) {
                        Storage::disk('public')->delete($teacher->$doc);
                    }
                    $docData[$doc] = $request->file($doc)->store('teachers/documents', 'public');
                }
            }

            // Update Teacher
            $teacher->update(array_merge([
                'nip'              => $validated['nip'],
                'name'             => $validated['name'],
                'gender'           => $validated['gender'],
                'phone'            => $validated['phone'],
                'address_ktp'      => $validated['address_ktp'] ?? null,
                'address_domicile' => $validated['address_domicile'] ?? null,
                'subject_specialty' => $validated['subject_specialty'],
                'status'           => $validated['status'],
            ], $docData));

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
            
            // Hapus file dokumen jika ada
            $docs = ['doc_ijazah_sd', 'doc_ijazah_smp', 'doc_ijazah_sma', 'doc_ijazah_s1', 'doc_npwp'];
            foreach ($docs as $doc) {
                if ($teacher->$doc) {
                    Storage::disk('public')->delete($teacher->$doc);
                }
            }

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
