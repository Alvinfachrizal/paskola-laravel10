<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeSlotFactory extends Factory
{
    protected $model = TimeSlot::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'day_of_week' => $this->faker->numberBetween(1, 5),
            'shift' => 'Pagi',
            'period_number' => 1,
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
        ];
    }
}
