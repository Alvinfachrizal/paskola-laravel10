<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SchoolClass extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'school_classes';

    protected $fillable = [
        'school_id',
        'school_year_id',
        'major_id',
        'name',
        'grade',
        'homeroom_teacher_id',
        'room_number',
        'max_students',
        'is_active',
    ];

    protected $casts = [
        'grade' => 'integer',
        'max_students' => 'integer',
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_classes', 'class_id', 'student_id')
                    ->withPivot('school_year_id', 'roll_number', 'is_active')
                    ->withTimestamps();
    }
}
