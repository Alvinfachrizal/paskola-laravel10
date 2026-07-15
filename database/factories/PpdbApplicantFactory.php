<?php

namespace Database\Factories;

use App\Models\PpdbApplicant;
use App\Models\PpdbWave;
use App\Enums\PpdbApplicantStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class PpdbApplicantFactory extends Factory
{
    protected $model = PpdbApplicant::class;

    public function definition(): array
    {
        $gender = $this->faker->randomElement(['laki-laki', 'perempuan']);

        return [
            'wave_id'           => PpdbWave::inRandomOrder()->first()?->id ?? PpdbWave::factory(),
            'registration_code' => PpdbApplicant::generateRegistrationCode(),
            'full_name'         => $this->faker->name($gender === 'laki-laki' ? 'male' : 'female'),
            'nisn'              => $this->faker->numerify('##########'), // 10 digit
            'birth_date'        => $this->faker->dateTimeBetween('-18 years', '-12 years')->format('Y-m-d'),
            'birth_place'       => $this->faker->city(),
            'gender'            => $gender,
            'address'           => $this->faker->address(),
            'parent_name'       => $this->faker->name('male'),
            'parent_phone'      => '08' . $this->faker->numerify('#########'),
            'email'             => $this->faker->safeEmail(),
            'status'            => $this->faker->randomElement(PpdbApplicantStatus::cases())->value,
            'admin_notes'       => null,
        ];
    }

    /** State: pendaftar baru (status pending) */
    public function pending(): static
    {
        return $this->state(fn() => ['status' => PpdbApplicantStatus::Pending->value]);
    }

    /** State: sudah terverifikasi dokumen */
    public function verified(): static
    {
        return $this->state(fn() => ['status' => PpdbApplicantStatus::Verified->value]);
    }

    /** State: perlu revisi dokumen */
    public function needRevision(): static
    {
        return $this->state(fn() => [
            'status'      => PpdbApplicantStatus::NeedRevision->value,
            'admin_notes' => 'Foto tidak jelas, harap upload ulang dengan resolusi lebih baik.',
        ]);
    }

    /** State: lolos seleksi */
    public function selected(): static
    {
        return $this->state(fn() => ['status' => PpdbApplicantStatus::Selected->value]);
    }

    /** State: tidak lolos */
    public function rejected(): static
    {
        return $this->state(fn() => [
            'status'      => PpdbApplicantStatus::Rejected->value,
            'admin_notes' => 'Nilai seleksi di bawah rata-rata dan kuota sudah terpenuhi.',
        ]);
    }

    /** State: sudah daftar ulang */
    public function reRegistered(): static
    {
        return $this->state(fn() => ['status' => PpdbApplicantStatus::ReRegistered->value]);
    }

    /** State: gender laki-laki */
    public function male(): static
    {
        return $this->state(fn() => [
            'gender'    => 'laki-laki',
            'full_name' => $this->faker->name('male'),
        ]);
    }

    /** State: gender perempuan */
    public function female(): static
    {
        return $this->state(fn() => [
            'gender'    => 'perempuan',
            'full_name' => $this->faker->name('female'),
        ]);
    }
}
