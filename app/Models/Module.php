<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = [
        'key', 'name', 'description', 'icon',
        'is_core', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_core'   => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Modul-modul lain yang bergantung pada modul ini.
     * (Modul mana yang PERLU modul ini aktif)
     */
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            Module::class,
            'module_dependencies',
            'depends_on_module_id',  // kolom FK ke modul ini (yang dibutuhkan)
            'module_id'              // kolom FK ke modul yang bergantung
        );
    }

    /**
     * Modul-modul yang dibutuhkan oleh modul ini.
     * (Modul apa saja yang harus aktif agar modul ini bisa berjalan)
     */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(
            Module::class,
            'module_dependencies',
            'module_id',             // kolom FK ke modul ini (yang bergantung)
            'depends_on_module_id'   // kolom FK ke modul yang dibutuhkan
        );
    }
}
