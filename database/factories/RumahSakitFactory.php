<?php

namespace Database\Factories;

use App\Models\RumahSakit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RumahSakitFactory extends Factory
{
    protected $model = RumahSakit::class;

    public function definition(): array
    {
        $nama = 'RS ' . fake()->unique()->company();
        return [
            'nama'    => $nama,
            'slug'    => Str::slug($nama),
            'lokasi'  => fake()->city(),
            'alamat'  => fake()->address(),
            'aktif'   => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(['aktif' => false]);
    }
}
