<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\Schedule;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'semester_id' => Semester::factory(),
            'time_slot_id' => TimeSlot::factory(),
            'room_id' => Room::factory(),
            'class_id' => SchoolClass::factory(),
            'subject_id' => Subject::factory(),
            // Assuming Teacher uses User model or Teacher model. Since user uses Spatie roles, we reference User where role is Guru
            'teacher_id' => User::factory(), 
        ];
    }
}
