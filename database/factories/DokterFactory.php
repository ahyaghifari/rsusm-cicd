<?php

namespace Database\Factories;

use App\Models\Dokter;
use App\Models\RumahSakit;
use App\Models\Spesialis;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DokterFactory extends Factory
{
    protected $model = Dokter::class;

    public function definition(): array
    {
        $nama = 'dr. ' . fake()->unique()->name();
        return [
            'rumah_sakit_id' => RumahSakit::factory(),
            'spesialis_id'   => Spesialis::factory(),
            'nama'           => $nama,
            'slug'           => Str::slug($nama),
            'aktif'          => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(['aktif' => false]);
    }
}
