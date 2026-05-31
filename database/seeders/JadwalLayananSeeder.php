<?php

namespace Database\Seeders;

use App\Enums\Hari;
use App\Enums\StatusLayanan;
use App\Models\JadwalLayanan;
use Illuminate\Database\Seeder;

class JadwalLayananSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        JadwalLayanan::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // [poliklinik_id => [[hari, nama_dokter, mulai, selesai, catatan?], ...]]
        $data = [
            // Klinik Spesialis Anak
            1 => [
                [Hari::SENIN,   'dr. Choirul Anam, M.Biomed, Sp.A, Subsp.E.T.I.A.(K)',  '08:00', '12:00', null],
                [Hari::SENIN,   'dr. Fitriyanti, Sp.A',                                   '13:00', '17:00', null],
                [Hari::SELASA,  'dr. Fitriyanti, Sp.A',                                   '08:00', '12:00', null],
                [Hari::RABU,    'dr. Choirul Anam, M.Biomed, Sp.A, Subsp.E.T.I.A.(K)',  '08:00', '12:00', null],
                [Hari::RABU,    'dr. Fitriyanti, Sp.A',                                   '13:00', '17:00', null],
                [Hari::KAMIS,   'dr. Fitriyanti, Sp.A',                                   '08:00', '12:00', null],
                [Hari::JUMAT,   'dr. Choirul Anam, M.Biomed, Sp.A, Subsp.E.T.I.A.(K)',  '08:00', '11:00', null],
                [Hari::SABTU,   'dr. Choirul Anam, M.Biomed, Sp.A, Subsp.E.T.I.A.(K)',  '08:00', '12:00', null],
                [Hari::SABTU,   'dr. Fitriyanti, Sp.A',                                   '13:00', '17:00', null],
            ],
            // Klinik Spesialis Penyakit Dalam
            2 => [
                [Hari::SENIN,   'dr. Achmad Dainuri, Sp.PD',         '08:00', '12:00', null],
                [Hari::SENIN,   'dr. Dekritiana Dian Pratiwi, Sp.PD', '13:00', '17:00', null],
                [Hari::SELASA,  'dr. Dekritiana Dian Pratiwi, Sp.PD', '08:00', '12:00', null],
                [Hari::SELASA,  'dr. Achmad Dainuri, Sp.PD',          '13:00', '17:00', null],
                [Hari::RABU,    'dr. Achmad Dainuri, Sp.PD',          '08:00', '12:00', null],
                [Hari::RABU,    'dr. Dekritiana Dian Pratiwi, Sp.PD', '13:00', '17:00', null],
                [Hari::KAMIS,   'dr. Dekritiana Dian Pratiwi, Sp.PD', '08:00', '12:00', null],
                [Hari::KAMIS,   'dr. Achmad Dainuri, Sp.PD',          '13:00', '17:00', null],
                [Hari::JUMAT,   'dr. Achmad Dainuri, Sp.PD',          '08:00', '11:00', null],
                [Hari::SABTU,   'dr. Dekritiana Dian Pratiwi, Sp.PD', '08:00', '12:00', null],
            ],
            // Klinik Spesialis Bedah
            3 => [
                [Hari::SENIN,   'dr. Ahmad Zainuri, Sp.B',      '08:00', '12:00', null],
                [Hari::SENIN,   'dr. Haris Prabowo, Sp.B',      '13:00', '17:00', null],
                [Hari::RABU,    'dr. Ahmad Zainuri, Sp.B',      '08:00', '12:00', null],
                [Hari::RABU,    'dr. Haris Prabowo, Sp.B',      '13:00', '17:00', null],
                [Hari::JUMAT,   'dr. Ahmad Zainuri, Sp.B',      '08:00', '11:00', null],
                [Hari::SABTU,   'dr. Haris Prabowo, Sp.B',      '08:00', '12:00', null],
            ],
            // Klinik Spesialis Orthopaedi
            4 => [
                [Hari::SELASA,  'dr. Budi Santoso, Sp.OT',      '08:00', '12:00', null],
                [Hari::SELASA,  'dr. Gunawan Saputra, Sp.OT',   '13:00', '17:00', null],
                [Hari::KAMIS,   'dr. Budi Santoso, Sp.OT',      '08:00', '12:00', null],
                [Hari::KAMIS,   'dr. Gunawan Saputra, Sp.OT',   '13:00', '17:00', null],
                [Hari::SABTU,   'dr. Budi Santoso, Sp.OT',      '08:00', '12:00', null],
            ],
            // Klinik Spesialis Kebidanan & Kandungan
            5 => [
                [Hari::SENIN,   'dr. Ratna Dewi, Sp.OG',        '08:00', '12:00', null],
                [Hari::SENIN,   'dr. Sari Melati, Sp.OG',       '13:00', '17:00', null],
                [Hari::SELASA,  'dr. Sari Melati, Sp.OG',       '08:00', '12:00', null],
                [Hari::RABU,    'dr. Ratna Dewi, Sp.OG',        '08:00', '12:00', null],
                [Hari::RABU,    'dr. Sari Melati, Sp.OG',       '13:00', '17:00', null],
                [Hari::KAMIS,   'dr. Ratna Dewi, Sp.OG',        '08:00', '12:00', null],
                [Hari::KAMIS,   'dr. Sari Melati, Sp.OG',       '13:00', '17:00', null],
                [Hari::JUMAT,   'dr. Ratna Dewi, Sp.OG',        '08:00', '11:00', null],
                [Hari::SABTU,   'dr. Ratna Dewi, Sp.OG',        '08:00', '12:00', null],
                [Hari::SABTU,   'dr. Sari Melati, Sp.OG',       '13:00', '17:00', null],
            ],
            // Klinik Spesialis Saraf
            6 => [
                [Hari::SENIN,   'dr. Hendra Kurniawan, Sp.N','08:00', '12:00', null],
                [Hari::RABU,    'dr. Hendra Kurniawan, Sp.N','08:00', '12:00', null],
                [Hari::JUMAT,   'dr. Hendra Kurniawan, Sp.N','08:00', '11:00', null],
            ],
            // Klinik Spesialis THT
            7 => [
                [Hari::SELASA,  'dr. Iqbal Maulana, Sp.THT-KL', '08:00', '12:00', null],
                [Hari::KAMIS,   'dr. Iqbal Maulana, Sp.THT-KL', '08:00', '12:00', null],
                [Hari::SABTU,   'dr. Iqbal Maulana, Sp.THT-KL', '08:00', '12:00', null],
            ],
            // Klinik Spesialis Paru
            8 => [
                [Hari::SENIN,   'dr. Lestari Wulandari, Sp.P', '08:00', '12:00', null],
                [Hari::RABU,    'dr. Lestari Wulandari, Sp.P', '13:00', '17:00', null],
                [Hari::JUMAT,   'dr. Lestari Wulandari, Sp.P', '08:00', '11:00', null],
            ],
            // Klinik Spesialis Jantung
            9 => [
                [Hari::SELASA,  'dr. Muhammad Ridwan, Sp.JP',  '08:00', '12:00', null],
                [Hari::KAMIS,   'dr. Muhammad Ridwan, Sp.JP',  '08:00', '12:00', null],
                [Hari::SABTU,   'dr. Muhammad Ridwan, Sp.JP',  '08:00', '12:00', null],
            ],
            // Klinik Spesialis Kulit & Kelamin
            10 => [
                [Hari::SENIN,   'dr. Anjas Asmara, Sp.KK',   '08:00', '12:00', null],
                [Hari::RABU,    'dr. Anjas Asmara, Sp.KK',   '08:00', '12:00', null],
                [Hari::JUMAT,   'dr. Anjas Asmara, Sp.KK',   '08:00', '11:00', null],
                [Hari::SABTU,   'dr. Anjas Asmara, Sp.KK',   '08:00', '12:00', null],
            ],
            // Klinik Spesialis Mata
            11 => [
                [Hari::SENIN,   'dr. Nur Hidayah, Sp.M',     '08:00', '12:00', null],
                [Hari::SELASA,  'dr. Nur Hidayah, Sp.M',     '13:00', '17:00', null],
                [Hari::KAMIS,   'dr. Nur Hidayah, Sp.M',     '08:00', '12:00', null],
                [Hari::SABTU,   'dr. Nur Hidayah, Sp.M',     '08:00', '12:00', null],
            ],
            // Klinik Spesialis Jiwa
            12 => [
                [Hari::SELASA,  'dr. Putri Rahma, Sp.KJ',    '09:00', '12:00', null],
                [Hari::KAMIS,   'dr. Putri Rahma, Sp.KJ',    '09:00', '12:00', null],
            ],
            // Klinik Spesialis Rehabilitasi
            13 => [
                [Hari::SENIN,   'dr. Rizky Firmansyah, Sp.KFR', '08:00', '12:00', null],
                [Hari::RABU,    'dr. Rizky Firmansyah, Sp.KFR', '08:00', '12:00', null],
                [Hari::JUMAT,   'dr. Rizky Firmansyah, Sp.KFR', '08:00', '11:00', null],
            ],
            // Klinik Spesialis Bedah Saraf
            14 => [
                [Hari::SELASA,  'dr. Satria Nugraha, Sp.BS',  '08:00', '12:00', null],
                [Hari::KAMIS,   'dr. Satria Nugraha, Sp.BS',  '08:00', '12:00', null],
            ],
            // Klinik Spesialis Urologi
            15 => [
                [Hari::SENIN,   'dr. Taufik Hidayat, Sp.U',  '08:00', '12:00', null],
                [Hari::RABU,    'dr. Taufik Hidayat, Sp.U',  '13:00', '17:00', null],
                [Hari::JUMAT,   'dr. Taufik Hidayat, Sp.U',  '08:00', '11:00', null],
            ],
            // Klinik Layanan Psikologi
            16 => [
                [Hari::SENIN,   'Ade Ayu Harisdiane Putri, M.Psi, Psikolog', '09:00', '12:00', 'Dengan perjanjian'],
                [Hari::RABU,    'Ade Ayu Harisdiane Putri, M.Psi, Psikolog', '09:00', '12:00', 'Dengan perjanjian'],
                [Hari::JUMAT,   'Ade Ayu Harisdiane Putri, M.Psi, Psikolog', '09:00', '11:00', 'Dengan perjanjian'],
            ],
            // Klinik Gigi Umum
            17 => [
                [Hari::SENIN,   'drg. Uswatun Hasanah',      '08:00', '12:00', null],
                [Hari::SELASA,  'drg. Uswatun Hasanah',      '08:00', '12:00', null],
                [Hari::RABU,    'drg. Uswatun Hasanah',      '13:00', '17:00', null],
                [Hari::KAMIS,   'drg. Uswatun Hasanah',      '08:00', '12:00', null],
                [Hari::JUMAT,   'drg. Uswatun Hasanah',      '08:00', '11:00', null],
                [Hari::SABTU,   'drg. Uswatun Hasanah',      '08:00', '12:00', null],
            ],
            // Klinik Gigi Anak
            18 => [
                [Hari::SELASA,  'drg. Vella Maharani, Sp.KGA', '08:00', '12:00', null],
                [Hari::KAMIS,   'drg. Vella Maharani, Sp.KGA', '08:00', '12:00', null],
                [Hari::SABTU,   'drg. Vella Maharani, Sp.KGA', '08:00', '12:00', null],
            ],
            // Klinik Endodonsi
            19 => [
                [Hari::SENIN,   'drg. Wahyu Prasetyo, Sp.KG', '08:00', '12:00', null],
                [Hari::RABU,    'drg. Wahyu Prasetyo, Sp.KG', '08:00', '12:00', null],
                [Hari::JUMAT,   'drg. Wahyu Prasetyo, Sp.KG', '08:00', '11:00', null],
            ],
            // Klinik Gigi Bedah Mulut
            20 => [
                [Hari::SELASA,  'drg. Xena Anggraini, Sp.BM', '08:00', '12:00', null],
                [Hari::KAMIS,   'drg. Xena Anggraini, Sp.BM', '08:00', '12:00', null],
            ],
            // Klinik Gigi Orthodonsi
            21 => [
                [Hari::RABU,    'drg. Yudha Pratama, Sp.Ort', '08:00', '12:00', null],
                [Hari::SABTU,   'drg. Yudha Pratama, Sp.Ort', '08:00', '12:00', null],
            ],
            // Klinik Gigi Prostodonsia
            22 => [
                [Hari::SENIN,   'drg. Zahra Amelia, Sp.Pros', '08:00', '12:00', null],
                [Hari::KAMIS,   'drg. Zahra Amelia, Sp.Pros', '08:00', '12:00', null],
            ],

            // === Unit Layanan 2: Aurora Executive Clinic ===
            // Medical Check Up
            23 => [
                [Hari::SENIN,   'dr. Andika Pratama, Sp.PD',    '08:00', '12:00', 'MCU Komprehensif'],
                [Hari::SENIN,   'dr. Bella Safitri, Sp.PK',     '08:00', '14:00', 'Laboratorium & Analisis'],
                [Hari::SELASA,  'dr. Andika Pratama, Sp.PD',    '08:00', '12:00', 'MCU Komprehensif'],
                [Hari::SELASA,  'dr. Candra Wijaya, Sp.Rad',    '09:00', '14:00', 'Radiologi & Imaging'],
                [Hari::RABU,    'dr. Andika Pratama, Sp.PD',    '08:00', '12:00', 'MCU Komprehensif'],
                [Hari::RABU,    'dr. Bella Safitri, Sp.PK',     '08:00', '14:00', 'Laboratorium & Analisis'],
                [Hari::KAMIS,   'dr. Andika Pratama, Sp.PD',    '08:00', '12:00', 'MCU Komprehensif'],
                [Hari::KAMIS,   'dr. Candra Wijaya, Sp.Rad',    '09:00', '14:00', 'Radiologi & Imaging'],
                [Hari::JUMAT,   'dr. Andika Pratama, Sp.PD',    '08:00', '11:00', 'MCU Komprehensif'],
                [Hari::JUMAT,   'dr. Bella Safitri, Sp.PK',     '08:00', '11:00', 'Laboratorium & Analisis'],
                [Hari::SABTU,   'dr. Andika Pratama, Sp.PD',    '08:00', '12:00', 'Dengan perjanjian'],
                [Hari::SABTU,   'dr. Candra Wijaya, Sp.Rad',    '08:00', '12:00', 'Dengan perjanjian'],
            ],
        ];

        // Dokter ID map: nama_dokter → dokter_id (jika ada di DB)
        $dokterMap = [
            'dr. Choirul Anam, M.Biomed, Sp.A, Subsp.E.T.I.A.(K)' => 5,
            'dr. Achmad Dainuri, Sp.PD'                             => 2,
            'dr. Dekritiana Dian Pratiwi, Sp.PD'                    => 4,
            'dr. Anjas Asmara, Sp.KK'                               => 3,
            'Ade Ayu Harisdiane Putri, M.Psi, Psikolog'             => 1,
        ];

        foreach ($data as $poliklinikId => $jadwals) {
            foreach ($jadwals as [$hari, $namaDokter, $mulai, $selesai, $catatan]) {
                JadwalLayanan::create([
                    'poliklinik_id'  => $poliklinikId,
                    'hari'           => $hari->value,
                    'dokter_id'      => $dokterMap[$namaDokter] ?? null,
                    'nama_dokter'    => $namaDokter,
                    'jam_mulai'      => $mulai,
                    'jam_selesai'    => $selesai,
                    'status_layanan' => StatusLayanan::BUKA->value,
                    'catatan'        => $catatan,
                ]);
            }
        }

        $total = JadwalLayanan::count();
        $this->command->info("JadwalLayanan seeded: {$total} entri untuk " . count($data) . " poliklinik.");
    }
}
