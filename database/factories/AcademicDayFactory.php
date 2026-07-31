<?php

namespace Database\Factories;

use App\Models\AcademicDay;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicDayFactory extends Factory
{
    protected $model = AcademicDay::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'day_of_week' => $this->faker->numberBetween(1, 7),
            'is_active' => true,
        ];
    }
}
