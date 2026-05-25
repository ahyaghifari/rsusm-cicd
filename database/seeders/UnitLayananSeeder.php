<?php

namespace Database\Seeders;

use App\Models\UnitLayanan;
use Illuminate\Database\Seeder;

class UnitLayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataUnit = [
            [
                'rumah_sakit_id' => 1,
                'nama' => 'Rawat Jalan',
                'deskripsi' => null,
                'gambar' => null,
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => 1,
                'nama' => 'Aurora Executive Clinic',
                'deskripsi' => null,
                'gambar' => null,
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => 2,
                'nama' => 'Rawat Jalan',
                'deskripsi' => null,
                'gambar' => null,
                'aktif' => true,
            ],
        ];

        foreach ($dataUnit as $unit) {
            UnitLayanan::create($unit);
        }
    }
}