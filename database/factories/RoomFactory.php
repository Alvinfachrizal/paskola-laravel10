<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'name' => 'Ruang ' . $this->faker->unique()->numerify('##'),
            'type' => $this->faker->randomElement(['Tetap', 'Umum']),
        ];
    }
}
