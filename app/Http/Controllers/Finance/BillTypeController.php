<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BillType;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillTypeController extends Controller
{
    public function index()
    {
        $school = School::first();
        $billTypes = BillType::where('school_id', $school->id)
            ->orderBy('is_recurring', 'desc')  // Recurring tampil duluan
            ->orderBy('name')
            ->get();

        return view('finance.bill-types.index', compact('billTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'description'     => 'nullable|string|max:500',
            'is_recurring'    => 'sometimes|boolean',
            'is_initial_bill' => 'sometimes|boolean',
            'default_amount'  => 'required|numeric|min:0',
            'is_active'       => 'sometimes|boolean',
        ]);

        $school = School::first();

        // Cek nama duplikat di sekolah yang sama
        $exists = BillType::where('school_id', $school->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Jenis tagihan dengan nama ini sudah ada.'])->withInput();
        }

        BillType::create([
            'school_id'       => $school->id,
            'name'            => $validated['name'],
            'description'     => $validated['description'] ?? null,
            'is_recurring'    => $request->boolean('is_recurring'),
            'is_initial_bill' => $request->boolean('is_initial_bill'),
            'default_amount'  => $validated['default_amount'],
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return back()->with('success', "Jenis tagihan \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, BillType $billType)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'description'     => 'nullable|string|max:500',
            'is_recurring'    => 'sometimes|boolean',
            'is_initial_bill' => 'sometimes|boolean',
            'default_amount'  => 'required|numeric|min:0',
            'is_active'       => 'sometimes|boolean',
        ]);

        // Cek duplikat nama (kecuali dirinya sendiri)
        $exists = BillType::where('school_id', $billType->school_id)
            ->where('name', $validated['name'])
            ->where('id', '!=', $billType->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Jenis tagihan dengan nama ini sudah ada.'])->withInput();
        }

        $billType->update([
            'name'            => $validated['name'],
            'description'     => $validated['description'] ?? null,
            'is_recurring'    => $request->boolean('is_recurring'),
            'is_initial_bill' => $request->boolean('is_initial_bill'),
            'default_amount'  => $validated['default_amount'],
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return back()->with('success', "Jenis tagihan \"{$billType->name}\" berhasil diperbarui.");
    }

    public function destroy(BillType $billType)
    {
        // Cegah hapus jika masih ada tagihan siswa yang menggunakan
        if ($billType->studentBills()->exists()) {
            return back()->withErrors([
                'delete' => "Tidak bisa dihapus — masih ada {$billType->studentBills()->count()} tagihan siswa yang menggunakan jenis ini."
            ]);
        }

        $name = $billType->name;
        $billType->delete();

        return back()->with('success', "Jenis tagihan \"{$name}\" berhasil dihapus.");
    }

    /**
     * Toggle aktif/nonaktif jenis tagihan tanpa menghapusnya.
     */
    public function toggleActive(BillType $billType)
    {
        $billType->update(['is_active' => !$billType->is_active]);
        $status = $billType->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Jenis tagihan \"{$billType->name}\" berhasil {$status}.");
    }
}
