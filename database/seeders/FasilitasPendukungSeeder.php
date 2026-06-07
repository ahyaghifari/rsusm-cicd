<?php

namespace Database\Seeders;

use App\Models\FasilitasPendukung;
use App\Models\RumahSakit;
use Illuminate\Database\Seeder;

class FasilitasPendukungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mencari data Rumah Sakit Banjarbaru berdasarkan slug dari seeder sebelumnya
        // Jika tidak ditemukan, default menggunakan ID 1
        $rumahSakit = RumahSakit::where('slug', 'banjarbaru')->first();
        $rumahSakitId = $rumahSakit ? $rumahSakit->id : 1;

        $daftarFasilitas = [
            [
                'rumah_sakit_id' => $rumahSakitId,
                'nama' => 'Layanan Ambulans On Call',
                'gambar' => null,
                'deskripsi' => 'Layanan Ambulans On Call RSU Syifa Medika Banjarbaru melayani selama 24 jam untuk keadaan darurat dan penjemputan pasien.',
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => $rumahSakitId,
                'nama' => 'Instalasi Gizi & Penyelenggaraan Makanan',
                'gambar' => null,
                'deskripsi' => 'Keadaan gizi pasien sangat berpengaruh terhadap penyembuhan penyakit. Ruang lingkup kegiatan pokok pelayanan gizi di RSU Syifa Medika terdiri dari: Asuhan Gizi Pasien Rawat Jalan, Asuhan Gizi Pasien Rawat Inap, dan Penyelenggaraan Makanan yang higienis.',
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => $rumahSakitId,
                'nama' => 'Sistem Informasi Manajemen Rumah Sakit (SIMRS GOS)',
                'gambar' => null,
                'deskripsi' => 'Saat ini RSU Syifa Medika Banjarbaru mengembangkan SIMRS GOS untuk mendukung kelancaran pelayanan dan integrasi data rekam medis terhadap pasien.',
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => $rumahSakitId,
                'nama' => 'Syifa Mart (Mini Market)',
                'gambar' => null,
                'deskripsi' => 'Mini Market RSU Syifa Medika Banjarbaru menjual segala macam barang kebutuhan harian, perlengkapan mandi, serta makanan yang diperlukan oleh pengunjung maupun keluarga pasien.',
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => $rumahSakitId,
                'nama' => 'Musala',
                'gambar' => null,
                'deskripsi' => 'Fasilitas tempat ibadah yang nyaman bagi para pasien, keluarga, serta pengunjung. Tersedia juga beberapa mukena dan alat salat siap pakai bagi yang tidak membawanya.',
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => $rumahSakitId,
                'nama' => 'ATM Center',
                'gambar' => null,
                'deskripsi' => 'Anjungan Tunai Mandiri (ATM) tersedia di lingkungan RSU Syifa Medika Banjarbaru, terletak strategis di depan gedung IGD dengan fasilitas penarikan maupun setor tunai.',
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => $rumahSakitId,
                'nama' => 'Syifa Food Corner & Cafe',
                'gambar' => null,
                'deskripsi' => 'Menyediakan banyak menu pilihan makanan baik ringan maupun berat serta bermacam-macam minuman. Operasional di hari Senin–Sabtu jam 11.00–20.00 dengan suasana yang tenang dan santai.',
                'aktif' => true,
            ],
        ];

        foreach ($daftarFasilitas as $fasilitas) {
            FasilitasPendukung::create($fasilitas);
        }
    }
}