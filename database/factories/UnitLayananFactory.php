<?php

namespace Database\Factories;

use App\Models\RumahSakit;
use App\Models\UnitLayanan;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitLayananFactory extends Factory
{
    protected $model = UnitLayanan::class;

    public function definition(): array
    {
        return [
            'rumah_sakit_id' => RumahSakit::factory(),
            'nama'           => fake()->unique()->words(3, true),
            'aktif'          => true,
        ];
    }
}
