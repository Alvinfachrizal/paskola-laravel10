<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsSubmission extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'file_url',
        'file_path',
        'text_content',
        'notes',
        'submitted_at',
        'score',
        'feedback',
        'graded_at',
        'status',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at'    => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(LmsAssignment::class, 'assignment_id');
    }

    /** student_id FK ke users.id */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
