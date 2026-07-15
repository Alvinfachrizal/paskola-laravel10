<?php

namespace Database\Factories;

use App\Models\PpdbWave;
use Illuminate\Database\Eloquent\Factories\Factory;

class PpdbWaveFactory extends Factory
{
    protected $model = PpdbWave::class;

    public function definition(): array
    {
        return [
            'name'             => 'Gelombang ' . $this->faker->numberBetween(1, 3),
            'quota'            => $this->faker->numberBetween(30, 100),
            'start_date'       => now()->subDays(10)->format('Y-m-d'),
            'end_date'         => now()->addDays(20)->format('Y-m-d'),
            'registration_fee' => 0,
            'is_active'        => true,
            'description'      => 'Pendaftaran peserta didik baru tahun ajaran ' . date('Y') . '/' . (date('Y') + 1),
        ];
    }

    /** State: gelombang berbayar */
    public function withFee(int $fee = 150000): static
    {
        return $this->state(fn() => ['registration_fee' => $fee]);
    }

    /** State: gelombang sudah ditutup */
    public function closed(): static
    {
        return $this->state(fn() => [
            'end_date'  => now()->subDays(5)->format('Y-m-d'),
            'is_active' => false,
        ]);
    }
}
