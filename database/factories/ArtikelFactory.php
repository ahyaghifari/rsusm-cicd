<?php

namespace Database\Factories;

use App\Models\Artikel;
use App\Models\RumahSakit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArtikelFactory extends Factory
{
    protected $model = Artikel::class;

    public function definition(): array
    {
        $judul = fake()->unique()->sentence();

        return [
            'rumah_sakit_id'      => RumahSakit::factory(),
            'kategori_artikel_id' => null,
            'judul'               => $judul,
            'slug'                => Str::slug($judul),
            'ringkasan'           => fake()->sentence(),
            'konten'              => '<p>' . fake()->paragraph() . '</p>',
            'gambar'              => null,
            'penulis'             => fake()->name(),
            'tanggal_publish'     => now(),
            'unggulan'            => false,
            'aktif'               => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(['aktif' => false]);
    }

    public function unggulan(): static
    {
        return $this->state(['unggulan' => true]);
    }
}
