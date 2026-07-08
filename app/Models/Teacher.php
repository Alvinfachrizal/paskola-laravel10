<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Teacher extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'school_id',
        'nip',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'address',
        'phone',
        'photo_url',
        'last_education',
        'subject_specialty',
        'employment_type',
        'join_date',
        'status',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'join_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function homeroomClasses()
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }
}
