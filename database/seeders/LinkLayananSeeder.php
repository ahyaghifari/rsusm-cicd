<?php

namespace Database\Seeders;

use App\Models\LinkLayanan;
use App\Models\RumahSakit;
use Illuminate\Database\Seeder;

class LinkLayananSeeder extends Seeder
{
    public function run(): void
    {
        $rumahSakit = RumahSakit::where('slug', 'banjarbaru')->first();
        $rumahSakitId = $rumahSakit ? $rumahSakit->id : 1;

        $data = [
            [
                'label' => 'Ketersediaan Ruang Rawat',
                'value' => 'Ketersediaan Ruang Rawat',
                'deskripsi_singkat' => 'Cek ketersediaan kamar rawat inap secara realtime',
                'link' => 'https://simgos.rsusyifamedika.co.id/apps/BedOnline/',
            ],
            [
                'label' => 'Jadwal Praktek Dokter',
                'value' => 'Jadwal Praktek Dokter',
                'deskripsi_singkat' => 'Lihat jadwal praktik seluruh dokter spesialis',
                'link' => 'https://simgos.rsusyifamedika.co.id/apps/JadwalOnline/',
            ],
            [
                'label' => 'Pantauan Antrian',
                'value' => 'Pantauan Antrian',
                'deskripsi_singkat' => 'Pantau antrian poliklinik secara langsung',
                'link' => 'https://simgos.rsusyifamedika.co.id/apps/AntrianOnline/',
            ],
        ];

        foreach ($data as $item) {
            LinkLayanan::create([
                'rumah_sakit_id' => $rumahSakitId,
                'label' => $item['label'],
                'value' => $item['value'],
                'gambar' => null,
                'deskripsi_singkat' => $item['deskripsi_singkat'],
                'link' => $item['link'],
                'aktif' => true,
            ]);
        }
    }
}
