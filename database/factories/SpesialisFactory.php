<?php

namespace Database\Factories;

use App\Models\RumahSakit;
use App\Models\Spesialis;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SpesialisFactory extends Factory
{
    protected $model = Spesialis::class;

    public function definition(): array
    {
        $nama = 'Spesialis ' . fake()->unique()->word();
        return [
            'rumah_sakit_id' => RumahSakit::factory(),
            'nama'           => $nama,
            'slug'           => Str::slug($nama),
            'aktif'          => true,
        ];
    }
}
