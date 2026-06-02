<?php

namespace Database\Factories;

use App\Enums\Hari;
use App\Models\JadwalPraktek;
use App\Models\PoliKlinik;
use Illuminate\Database\Eloquent\Factories\Factory;

class JadwalPraktekFactory extends Factory
{
    protected $model = JadwalPraktek::class;

    public function definition(): array
    {
        return [
            'poliklinik_id'     => PoliKlinik::factory(),
            'hari'              => fake()->randomElement(Hari::cases())->value,
            'dokter_id'         => null,
            'nama_dokter'       => fake()->name(),
            'waktu_mulai'       => '08:00',
            'waktu_selesai'     => '12:00',
            'sesuai_perjanjian' => false,
            'catatan'           => null,
        ];
    }
}
