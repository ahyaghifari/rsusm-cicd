<?php

namespace Database\Factories;

use App\Enums\StatusLayanan;
use App\Models\JadwalLayananHarian;
use App\Models\PoliKlinik;
use Illuminate\Database\Eloquent\Factories\Factory;

class JadwalLayananHarianFactory extends Factory
{
    protected $model = JadwalLayananHarian::class;

    public function definition(): array
    {
        return [
            'poliklinik_id'  => PoliKlinik::factory(),
            'tanggal'        => today(),
            'nama_dokter'    => 'dr. ' . fake()->name(),
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '12:00',
            'status_layanan' => StatusLayanan::BUKA->value,
        ];
    }
}
