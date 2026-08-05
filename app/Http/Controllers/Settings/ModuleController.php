<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\ModuleService;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * Tampilkan halaman Settings → Manajemen Modul.
     * Hanya bisa diakses Super Admin.
     */
    public function index()
    {
        $modules = Module::with('dependencies', 'dependents')
            ->orderBy('sort_order')
            ->get();

        return view('settings.modules.index', compact('modules'));
    }

    /**
     * Toggle aktif/nonaktif sebuah modul.
     */
    public function toggle(Module $module)
    {
        $result = ModuleService::toggle($module);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
