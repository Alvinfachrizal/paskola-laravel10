<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Subject extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_id',
        'code',
        'name',
        'type',
        'weekly_hours',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'weekly_hours' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
