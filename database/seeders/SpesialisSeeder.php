<?php

namespace Database\Seeders;

use App\Models\Spesialis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SpesialisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Spesialis Anak',
            'Spesialis Penyakit Dalam',
            'Spesialis Bedah',
            'Spesialis Orthopaedi & Traumatologi',
            'Spesialis Kebidanan & Kandungan',
            'Spesialis Saraf',
            'Spesialis Paru',
            'Spesialis THT',
            'Spesialis Jantung',
            'Spesialis Kulit & Kelamin',
            'Spesialis Urologi',
            'Spesialis Mata',
            'Spesialis Kejiwaan (Psikiater)',
            'Spesialis Bedah Saraf',
            'Spesialis Rehabilitasi Medik / Fisik & Rehabilitasi',
            'Spesialis Gizi Klinik',
            'Layanan Konsultasi Psikologi',
            'Dokter Gigi Umum',
            'Spesialis Dokter Gigi Anak',
            'Spesialis Konservasi Gigi',
            'Spesialis Gigi Prostodonsi',
            'Spesialis Gigi Ortodonsi',
            'Spesialis Gigi Bedah Mulut & Maksilofasial',
        ];

        $rumahSakitIds = [1, 2];

        foreach ($rumahSakitIds as $rsId) {
            foreach ($data as $nama) {
                Spesialis::updateOrCreate(
                    [
                        'nama' => $nama,
                        'rumah_sakit_id' => $rsId,
                    ],
                    [
                        'slug' => Str::slug($nama),
                        'aktif' => true,
                    ]
                );
            }
        }
    }
}
