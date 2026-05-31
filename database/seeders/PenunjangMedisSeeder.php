<?php

namespace Database\Seeders;

use App\Models\PenunjangMedis;
use App\Models\RumahSakit;
use Illuminate\Database\Seeder;

class PenunjangMedisSeeder extends Seeder
{
    public function run(): void
    {
        $rumahSakit = RumahSakit::where('slug', 'banjarbaru')->first();
        $rumahSakitId = $rumahSakit ? $rumahSakit->id : 1;

        $data = [
            [
                'rumah_sakit_id' => $rumahSakitId,
                'nama' => 'Instalasi Radiologi',
                'gambar' => null,
                'deskripsi' => 'Layanan pemeriksaan radiologi dengan peralatan canggih untuk mendukung diagnosis dokter, mencakup: General X-Ray (Digital Radiography), USG (Ultrasonografi), Dental X-Ray, Panoramic Dental X-ray, CT-SCAN (80 slice).',
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => $rumahSakitId,
                'nama' => 'Instalasi Laboratorium (Patologi Klinik)',
                'gambar' => null,
                'deskripsi' => 'Unit pelayanan yang membantu penegakan diagnosis dan monitoring penyakit. Pelayanan Laboratorium RSU Syifa Medika Banjarbaru buka 24 jam termasuk hari minggu, dengan layanan mencakup: Urinalisa, Analisis Feses, Immunologi & Serologi, Hematologi, Fungsi Hati, Ginjal, dan Lemak, Panel Jantung, Diabetes, Mikrobiologi, Infeksi, Alergi & Parasitologi, Analisa Sperma, Tuberkulosis.',
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => $rumahSakitId,
                'nama' => 'Instalasi Farmasi',
                'gambar' => null,
                'deskripsi' => 'Melayani distribusi obat kepada pasien rawat jalan, rawat inap, dan IGD selama 24 jam, dengan konsultasi farmasi klinik mengenai informasi dan penggunaan obat.',
                'aktif' => true,
            ],
        ];

        foreach ($data as $item) {
            PenunjangMedis::create($item);
        }
    }
}
