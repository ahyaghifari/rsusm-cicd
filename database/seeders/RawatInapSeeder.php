<?php

namespace Database\Seeders;

use App\Models\RawatInap;
use Illuminate\Database\Seeder;

class RawatInapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'rumah_sakit_id' => 1,
                'gedung_id' => 2, // Marwah
                'nama' => 'Paviliun Firdaus - VVIP Ar-Raudhah',
                'kelas' => 'VVIP',
                'harga' => 1199619,
                'kapasitas' => 1,
                'sort_order' => 1,
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => 1,
                'gedung_id' => 2, // Marwah
                'nama' => 'Paviliun Firdaus - VIP Al-Karim',
                'kelas' => 'VIP',
                'harga' => 863999,
                'kapasitas' => 1,
                'sort_order' => 2,
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => 1,
                'gedung_id' => 2, // Marwah
                'nama' => 'VIP Al-Hakim',
                'kelas' => 'VIP',
                'harga' => 949817,
                'kapasitas' => 1,
                'sort_order' => 3,
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => 1,
                'gedung_id' => 2, // Marwah
                'nama' => 'Kelas I As-Salam',
                'kelas' => 'Kelas I',
                'harga' => 713076,
                'kapasitas' => 2,
                'sort_order' => 4,
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => 1,
                'gedung_id' => 3, // Muzdalifah
                'nama' => 'Kelas I An-Nur',
                'kelas' => 'Kelas I',
                'harga' => 713076,
                'kapasitas' => 2,
                'sort_order' => 5,
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => 1,
                'gedung_id' => 3, // Muzdalifah
                'nama' => 'Kelas I An-Nur (204, 210-215)',
                'kelas' => 'Kelas I',
                'harga' => 692307,
                'kapasitas' => 2,
                'sort_order' => 6,
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => 1,
                'gedung_id' => 1, // Shofa
                'nama' => 'VIP Plus An-Nisa (OBGYN)',
                'kelas' => 'VIP Plus',
                'harga' => 949817,
                'kapasitas' => 1,
                'sort_order' => 7,
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => 1,
                'gedung_id' => 1, // Shofa
                'nama' => 'VIP Ar-Rahman',
                'kelas' => 'VIP',
                'harga' => 830768,
                'kapasitas' => 1,
                'sort_order' => 8,
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => 1,
                'gedung_id' => 1, // Shofa
                'nama' => 'Kelas I Al-Kautsar',
                'kelas' => 'Kelas I',
                'harga' => 692307,
                'kapasitas' => 1,
                'sort_order' => 9,
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => 1,
                'gedung_id' => 1, // Shofa
                'nama' => 'Kelas II Al-Furqon',
                'kelas' => 'Kelas II',
                'harga' => 415384,
                'kapasitas' => 2,
                'sort_order' => 10,
                'aktif' => true,
            ],
            [
                'rumah_sakit_id' => 1,
                'gedung_id' => 1, // Shofa
                'nama' => 'Kelas III Al-Fath',
                'kelas' => 'Kelas III',
                'harga' => 242307,
                'kapasitas' => 3,
                'sort_order' => 11,
                'aktif' => true,
            ],
        ];

        foreach ($data as $item) {
            RawatInap::create($item);
        }
    }
}
