<?php

namespace Database\Seeders;

use App\Models\FasilitasRawatInap;
use Illuminate\Database\Seeder;

class FasilitasRawatInapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fasilitasPerKamar = [
            1 => ['1 Bed Pasien Elektrik', '1 Sofa Bed Penunggu', 'AC', 'Smart TV', 'Kulkas', 'Dispender', 'Wifi', 'Kamar Mandi Dalam', 'Water Heater', 'Lemari Penyimpanan', 'Meja Makan Pasien'],
            2 => ['1 Bed Pasien', 'Sofa Penunggu', 'AC', 'TV', 'Kulkas', 'Wifi', 'Kamar Mandi Dalam', 'Lemari Penyimpanan'],
            3 => ['1 Bed Pasien', 'AC', 'TV', 'Kulkas', 'Sofa Penunggu', 'Wifi', 'Kamar Mandi Dalam'],
            4 => ['2 Bed Pasien', 'AC', 'TV', 'Kamar Mandi Dalam', 'Lemari Penyimpanan'],
            5 => ['2 Bed Pasien', 'AC', 'TV', 'Kamar Mandi Dalam'],
            6 => ['2 Bed Pasien', 'AC', 'Kamar Mandi Dalam'],
            7 => ['1 Bed Pasien', 'Sofa Penunggu', 'AC', 'TV', 'Kulkas', 'Wifi', 'Kamar Mandi Dalam', 'Area Khusus OBGYN'],
            8 => ['1 Bed Pasien', 'AC', 'TV', 'Sofa Penunggu', 'Kamar Mandi Dalam'],
            9 => ['1 Bed Pasien', 'AC', 'TV', 'Kamar Mandi Dalam'],
            10 => ['2 Bed Pasien', 'Kipas Angin', 'Kamar Mandi Dalam'],
            11 => ['3 Bed Pasien', 'Kipas Angin', 'Kamar Mandi Bersama'],
        ];

        foreach ($fasilitasPerKamar as $rawatInapId => $fasilitas) {
            foreach ($fasilitas as $nama) {
                FasilitasRawatInap::create([
                    'rawat_inap_id' => $rawatInapId,
                    'nama' => $nama,
                    'aktif' => true,
                ]);
            }
        }
    }
}
