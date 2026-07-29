<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        // Ambil atau buat school & school_year yang dibutuhkan sebagai FK
        $school = School::first() ?? School::create([
            'name'    => 'SMA Test',
            'address' => 'Jl. Test',
        ]);

        $schoolYear = SchoolYear::first() ?? SchoolYear::create([
            'school_id'     => $school->id,
            'academic_year' => '2024/2025',
            'semester'      => 'ganjil',
            'start_date'    => '2024-07-15',
            'end_date'      => '2024-12-20',
            'is_active'     => true,
        ]);

        return [
            'school_id'      => $school->id,
            'school_year_id' => $schoolYear->id,
            'name'           => 'Kelas ' . $this->faker->unique()->bothify('X?-?'),
            'grade'          => $this->faker->numberBetween(10, 12),
            'is_active'      => true,
            'max_students'   => 36,
        ];
    }
}
