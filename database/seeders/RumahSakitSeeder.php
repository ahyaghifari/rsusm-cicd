<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RumahSakit;

class RumahSakitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RumahSakit::create([
            'nama' => 'RSU Syifa Medika Banjarbaru',
            'slug' => 'banjarbaru',
            'lokasi' => 'Banjarbaru',
            'alamat' => 'Jl. RO Ulin No.93, Loktabat Selatan, Kec. Banjarbaru Selatan, Kota Banjar Baru, Kalimantan Selatan 70712',
            'no_emergency' => '0811 504 2424',
            'no_hotline' => '0511 5910 889',
            'gambar' => null,
            'logo' => null,
            'aktif' => true,
        ]);

        RumahSakit::create([
            'nama' => 'RSU Syifa Medika Barabai',
            'slug' => 'barabai',
            'lokasi' => 'Barabai',
            'alamat' => 'Jl Lingkar Walangsi Kapar KM. 5.2, Barabai, Kalimantan Selatan, Indonesia',
            'no_emergency' => '-',
            'no_hotline' => '-',
            'gambar' => null,
            'logo' => null,
            'aktif' => true,
        ]);
    }
}
