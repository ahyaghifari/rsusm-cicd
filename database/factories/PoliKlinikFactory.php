<?php

namespace Database\Factories;

use App\Models\PoliKlinik;
use App\Models\UnitLayanan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PoliKlinikFactory extends Factory
{
    protected $model = PoliKlinik::class;

    public function definition(): array
    {
        $nama = 'Poli ' . fake()->unique()->word();
        return [
            'unit_layanan_id' => UnitLayanan::factory(),
            'nama'            => $nama,
            'slug'            => Str::slug($nama),
            'deskripsi'       => fake()->sentence(),
            'aktif'           => true,
        ];
    }
}
