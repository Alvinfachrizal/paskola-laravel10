<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Student extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'school_id',
        'nisn',
        'nis',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'address',
        'phone',
        'photo_url',
        'entry_year',
        'status',
        'parent_name',
        'parent_phone',
        'parent_job',
        'parent_user_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'entry_year' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parentUser()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'student_classes', 'student_id', 'class_id')
                    ->withPivot('school_year_id', 'roll_number', 'is_active')
                    ->withTimestamps();
    }
}
