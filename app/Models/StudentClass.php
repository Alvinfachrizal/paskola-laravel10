<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StudentClass extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'student_classes';

    protected $fillable = [
        'student_id',
        'class_id',
        'school_year_id',
        'roll_number',
        'is_active',
    ];

    protected $casts = [
        'roll_number' => 'integer',
        'is_active' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
