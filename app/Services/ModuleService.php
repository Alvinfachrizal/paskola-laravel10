<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Facades\Cache;

class ModuleService
{
    // Cache key untuk status semua modul
    const CACHE_KEY = 'modules_status';
    const CACHE_TTL = 3600; // 1 jam

    /**
     * Ambil status semua modul dari cache (atau dari DB jika cache kosong).
     * Hasilnya berupa array: ['key' => is_active, ...]
     * Contoh: ['ppdb' => true, 'lms' => false, ...]
     */
    public static function getStatus(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Module::pluck('is_active', 'key')->toArray();
        });
    }

    /**
     * Cek apakah sebuah modul sedang aktif.
     */
    public static function isActive(string $key): bool
    {
        $status = self::getStatus();
        // Kalau key tidak ada di DB, anggap aktif (safe default)
        return $status[$key] ?? true;
    }

    /**
     * Invalidate cache setelah ada perubahan status.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Validasi apakah modul aman untuk dinonaktifkan.
     * Mengembalikan array ['can' => bool, 'reason' => string|null]
     */
    public static function canDeactivate(Module $module): array
    {
        // 1. Modul core tidak bisa dinonaktifkan sama sekali
        if ($module->is_core) {
            return [
                'can'    => false,
                'reason' => "Modul \"{$module->name}\" adalah modul inti (core) sistem dan tidak dapat dinonaktifkan.",
            ];
        }

        // 2. Cek apakah ada modul lain yang AKTIF dan bergantung pada modul ini
        $activeDependents = $module->dependents()
            ->where('is_active', true)
            ->get();

        if ($activeDependents->isNotEmpty()) {
            $names = $activeDependents->pluck('name')->join(', ');
            return [
                'can'    => false,
                'reason' => "Tidak dapat menonaktifkan \"{$module->name}\" karena modul berikut masih aktif dan membutuhkannya: {$names}. Nonaktifkan modul tersebut terlebih dahulu.",
            ];
        }

        return ['can' => true, 'reason' => null];
    }

    /**
     * Toggle status modul (aktif ↔ nonaktif).
     * Mengembalikan ['success' => bool, 'message' => string]
     */
    public static function toggle(Module $module): array
    {
        // Jika ingin MENONAKTIFKAN (saat ini aktif), jalankan validasi
        if ($module->is_active) {
            $check = self::canDeactivate($module);
            if (!$check['can']) {
                return ['success' => false, 'message' => $check['reason']];
            }
        }

        $module->update(['is_active' => !$module->is_active]);
        self::clearCache();

        $state = $module->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return ['success' => true, 'message' => "Modul \"{$module->name}\" berhasil {$state}."];
    }
}
