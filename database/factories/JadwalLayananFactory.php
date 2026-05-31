<?php

namespace Database\Factories;

use App\Enums\Hari;
use App\Enums\StatusLayanan;
use App\Models\JadwalLayanan;
use App\Models\PoliKlinik;
use Illuminate\Database\Eloquent\Factories\Factory;

class JadwalLayananFactory extends Factory
{
    protected $model = JadwalLayanan::class;

    public function definition(): array
    {
        return [
            'poliklinik_id'  => PoliKlinik::factory(),
            'hari'           => fake()->randomElement(Hari::cases())->value,
            'nama_dokter'    => 'dr. ' . fake()->name(),
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '12:00',
            'status_layanan' => StatusLayanan::BUKA->value,
        ];
    }

    public function libur(): static
    {
        return $this->state(['status_layanan' => StatusLayanan::LIBUR->value]);
    }
}
