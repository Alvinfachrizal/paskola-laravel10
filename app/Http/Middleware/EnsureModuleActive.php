<?php

namespace App\Http\Middleware;

use App\Services\ModuleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleActive
{
    /**
     * Handle an incoming request.
     *
     * Cara pakai di routes:
     *   ->middleware('module.active:ppdb')
     *   ->middleware('module.active:keuangan_sekolah')
     *
     * @param string $moduleKey  Key modul yang harus aktif untuk mengakses route ini
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        if (!ModuleService::isActive($moduleKey)) {
            // Jika request adalah AJAX/API, kembalikan JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Modul ini sedang tidak aktif. Hubungi administrator sekolah.'
                ], 403);
            }

            // Jika request HTML biasa, redirect ke halaman info
            return redirect()->route('module.disabled')->with('disabled_module', $moduleKey);
        }

        return $next($request);
    }
}
