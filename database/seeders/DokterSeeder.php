<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\Spesialis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DokterSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil spesialis RS 1 sebagai map [nama => id]
        $sp = Spesialis::where('rumah_sakit_id', 1)->pluck('id', 'nama');

        // 27 dokter RS 1 — diurutkan persis sama dengan JadwalPraktekSeeder
        $dokterRS1 = [
            ['nama' => 'dr. Choirul Anam, M.Biomed, Sp.A, Subsp.E.T.I.A.(K)', 'spesialis' => 'Spesialis Anak'],
            ['nama' => 'dr. Fitriyanti, Sp.A',                                   'spesialis' => 'Spesialis Anak'],
            ['nama' => 'dr. Achmad Dainuri, Sp.PD',                              'spesialis' => 'Spesialis Penyakit Dalam'],
            ['nama' => 'dr. Dekritiana Dian Pratiwi, Sp.PD',                     'spesialis' => 'Spesialis Penyakit Dalam'],
            ['nama' => 'dr. Ahmad Zainuri, Sp.B',                                'spesialis' => 'Spesialis Bedah'],
            ['nama' => 'dr. Haris Prabowo, Sp.B',                                'spesialis' => 'Spesialis Bedah'],
            ['nama' => 'dr. Budi Santoso, Sp.OT',                                'spesialis' => 'Spesialis Orthopaedi & Traumatologi'],
            ['nama' => 'dr. Gunawan Saputra, Sp.OT',                             'spesialis' => 'Spesialis Orthopaedi & Traumatologi'],
            ['nama' => 'dr. Ratna Dewi, Sp.OG',                                  'spesialis' => 'Spesialis Kebidanan & Kandungan'],
            ['nama' => 'dr. Sari Melati, Sp.OG',                                 'spesialis' => 'Spesialis Kebidanan & Kandungan'],
            ['nama' => 'dr. Hendra Kurniawan, Sp.N',                             'spesialis' => 'Spesialis Saraf'],
            ['nama' => 'dr. Iqbal Maulana, Sp.THT-KL',                           'spesialis' => 'Spesialis THT'],
            ['nama' => 'dr. Lestari Wulandari, Sp.P',                            'spesialis' => 'Spesialis Paru'],
            ['nama' => 'dr. Muhammad Ridwan, Sp.JP',                             'spesialis' => 'Spesialis Jantung'],
            ['nama' => 'dr. Anjas Asmara, Sp.KK',                               'spesialis' => 'Spesialis Kulit & Kelamin'],
            ['nama' => 'dr. Nur Hidayah, Sp.M',                                  'spesialis' => 'Spesialis Mata'],
            ['nama' => 'dr. Putri Rahma, Sp.KJ',                                 'spesialis' => 'Spesialis Kejiwaan (Psikiater)'],
            ['nama' => 'dr. Rizky Firmansyah, Sp.KFR',                           'spesialis' => 'Spesialis Rehabilitasi Medik / Fisik & Rehabilitasi'],
            ['nama' => 'dr. Satria Nugraha, Sp.BS',                              'spesialis' => 'Spesialis Bedah Saraf'],
            ['nama' => 'dr. Taufik Hidayat, Sp.U',                               'spesialis' => 'Spesialis Urologi'],
            ['nama' => 'Ade Ayu Harisdiane Putri, M.Psi, Psikolog',              'spesialis' => 'Layanan Konsultasi Psikologi'],
            ['nama' => 'drg. Uswatun Hasanah',                                   'spesialis' => 'Dokter Gigi Umum'],
            ['nama' => 'drg. Vella Maharani, Sp.KGA',                            'spesialis' => 'Spesialis Dokter Gigi Anak'],
            ['nama' => 'drg. Wahyu Prasetyo, Sp.KG',                             'spesialis' => 'Spesialis Konservasi Gigi'],
            ['nama' => 'drg. Xena Anggraini, Sp.BM',                             'spesialis' => 'Spesialis Gigi Bedah Mulut & Maksilofasial'],
            ['nama' => 'drg. Yudha Pratama, Sp.Ort',                             'spesialis' => 'Spesialis Gigi Ortodonsi'],
            ['nama' => 'drg. Zahra Amelia, Sp.Pros',                             'spesialis' => 'Spesialis Gigi Prostodonsi'],
        ];

        foreach ($dokterRS1 as $d) {
            Dokter::create([
                'nama'           => $d['nama'],
                'slug'           => Str::slug($d['nama']),
                'foto'           => null,
                'deskripsi'      => null,
                'aktif'          => true,
                'pendidikan'     => null,
                'pelatihan'      => null,
                'rumah_sakit_id' => 1,
                'spesialis_id'   => $sp[$d['spesialis']] ?? null,
            ]);
        }

        // Dokter RS 2 — faker karena tidak ada jadwal praktek spesifik untuk RS 2
        $faker       = \Faker\Factory::create('id_ID');
        $spRS2       = Spesialis::where('rumah_sakit_id', 2)->pluck('id')->toArray();

        for ($i = 0; $i < 5; $i++) {
            $isDentist = $faker->boolean(20);
            $prefix    = $isDentist ? 'drg. ' : 'dr. ';
            $nama      = $prefix . $faker->name();

            Dokter::create([
                'nama'           => $nama,
                'slug'           => Str::slug($nama) . '-rs2-' . $i,
                'foto'           => null,
                'deskripsi'      => null,
                'aktif'          => true,
                'pendidikan'     => null,
                'pelatihan'      => null,
                'rumah_sakit_id' => 2,
                'spesialis_id'   => $faker->randomElement($spRS2),
            ]);
        }
    }
}
