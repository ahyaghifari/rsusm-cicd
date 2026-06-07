<?php

namespace Database\Seeders;

use App\Models\Kontak;
use App\Models\RumahSakit;
use Illuminate\Database\Seeder;

class KontakSeeder extends Seeder
{
    public function run(): void
    {
        $rumahSakit = RumahSakit::where('slug', 'banjarbaru')->first();
        $rumahSakitId = $rumahSakit ? $rumahSakit->id : 1;

        $operasional = [
            [
                'label' => 'Ambulans 24 Jam',
                'value' => '0811 504 2424',
                'link' => 'https://api.whatsapp.com/send/?phone=628115042424&text=Halo+Admin+IGD+RSU+Syifa+Medika+Banjarbaru+Saya+memerlukan+Ambulance+Sekarang',
            ],
            [
                'label' => 'Operator',
                'value' => '0511 5910 889',
                'link' => null,
            ],
            [
                'label' => 'Pendaftaran Poliklinik',
                'value' => '0821 5342 4447',
                'link' => 'https://api.whatsapp.com/send/?phone=6282153424447&text=Halo',
            ],
            [
                'label' => 'Pendaftaran MCU',
                'value' => '0821 5551 8563',
                'link' => 'https://api.whatsapp.com/send/?phone=6282155518563&text=Halo',
            ],
            [
                'label' => 'Poli Eksekutif / Vaksin / Fertilitas',
                'value' => '0821 5461 8061',
                'link' => 'https://api.whatsapp.com/send/?phone=6282154618061&text=Halo',
            ],
            [
                'label' => 'Homecare',
                'value' => '0821 5421 2947',
                'link' => 'https://api.whatsapp.com/send/?phone=6282154212947&text=Halo',
            ],
            [
                'label' => 'Email',
                'value' => 'info.rsusyifamedika@gmail.com',
                'link' => null,
            ],
        ];

        foreach ($operasional as $data) {
            Kontak::create([
                'rumah_sakit_id' => $rumahSakitId,
                'label' => $data['label'],
                'value' => $data['value'],
                'gambar' => null,
                'logo' => null,
                'link' => $data['link'],
                'kategori' => 'OPERASIONAL',
                'aktif' => true,
            ]);
        }
    }
}
