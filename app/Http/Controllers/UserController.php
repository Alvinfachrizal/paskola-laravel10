<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $query = User::with('roles')->where('users.school_id', $schoolId);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        // Role Filter
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->orderBy('users.name')->paginate(10)->withQueryString();
        
        $roles = Role::all();

        // Statistics
        $stats = [
            'Admin' => User::role(['Admin', 'Super Admin'])->where('users.school_id', $schoolId)->count(),
            'Kepala' => User::role('Kepala Sekolah')->where('users.school_id', $schoolId)->count(),
            'Guru' => User::role('Guru')->where('users.school_id', $schoolId)->count(),
            'Siswa' => User::role('Siswa')->where('users.school_id', $schoolId)->count(),
            'OrangTua' => User::role('Ortu')->where('users.school_id', $schoolId)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
            'phone' => 'nullable|string|max:20',
            'nik' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'school_id' => $request->user()->school_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'nik' => $request->nik,
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        if ($user->school_id !== $request->user()->school_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|exists:roles,name',
            'password' => 'nullable|string|min:8',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // is_active might be passed as 1 or missing if unchecked
        $user->is_active = $request->has('is_active');
        $user->save();

        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->school_id !== $request->user()->school_id) {
            abort(403);
        }

        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return redirect()->route('admin.users.index')->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    public function impersonate(Request $request, User $user)
    {
        if ($user->school_id !== $request->user()->school_id) {
            abort(403);
        }

        // Don't impersonate self
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak bisa login sebagai diri sendiri.');
        }

        // Store current user ID in session
        $request->session()->put('impersonated_by', $request->user()->id);
        
        // Login as the target user
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Berhasil login sebagai ' . $user->name);
    }

    public function stopImpersonate(Request $request)
    {
        if (!$request->session()->has('impersonated_by')) {
            return redirect()->route('dashboard');
        }

        $originalUserId = $request->session()->get('impersonated_by');
        $request->session()->forget('impersonated_by');

        Auth::loginUsingId($originalUserId);

        return redirect()->route('admin.users.index')->with('success', 'Kembali ke akun Admin.');
    }
}
