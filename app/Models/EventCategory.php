<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCategory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_id',
        'name',
        'is_holiday',
        'color',
    ];

    protected $casts = [
        'is_holiday' => 'boolean',
    ];

    // Satu kategori bisa punya banyak event
    public function events(): HasMany
    {
        return $this->hasMany(AcademicEvent::class, 'category_id');
    }
}
