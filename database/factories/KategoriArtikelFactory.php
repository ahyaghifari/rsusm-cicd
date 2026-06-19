<?php

namespace Database\Factories;

use App\Models\KategoriArtikel;
use App\Models\RumahSakit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KategoriArtikelFactory extends Factory
{
    protected $model = KategoriArtikel::class;

    public function definition(): array
    {
        $nama = fake()->unique()->words(2, true);

        return [
            'rumah_sakit_id' => RumahSakit::factory(),
            'nama'           => $nama,
            'slug'           => Str::slug($nama),
        ];
    }
}
