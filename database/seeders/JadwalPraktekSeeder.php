<?php

namespace Database\Seeders;

use App\Enums\Hari;
use App\Models\JadwalPraktek;
use Illuminate\Database\Seeder;

class JadwalPraktekSeeder extends Seeder
{
    public function run(): void
    {
        JadwalPraktek::truncate();

        // [poliklinik_id => [[hari, nama_dokter, waktu_mulai, waktu_selesai, sesuai_perjanjian, catatan?], ...]]
        $data = [
            // Klinik Spesialis Anak (ID 1)
            1 => [
                [Hari::SENIN,   'dr. Choirul Anam, M.Biomed, Sp.A, Subsp.E.T.I.A.(K)', '08:00', '12:00', false, null],
                [Hari::SENIN,   'dr. Fitriyanti, Sp.A',                                  '13:00', '17:00', false, null],
                [Hari::SELASA,  'dr. Fitriyanti, Sp.A',                                  '08:00', '12:00', false, null],
                [Hari::RABU,    'dr. Choirul Anam, M.Biomed, Sp.A, Subsp.E.T.I.A.(K)', '08:00', '12:00', false, null],
                [Hari::RABU,    'dr. Fitriyanti, Sp.A',                                  '13:00', '17:00', false, null],
                [Hari::KAMIS,   'dr. Fitriyanti, Sp.A',                                  '08:00', '12:00', false, null],
                [Hari::JUMAT,   'dr. Choirul Anam, M.Biomed, Sp.A, Subsp.E.T.I.A.(K)', '08:00', '11:00', false, null],
                [Hari::SABTU,   'dr. Choirul Anam, M.Biomed, Sp.A, Subsp.E.T.I.A.(K)', '08:00', '12:00', false, null],
                [Hari::SABTU,   'dr. Fitriyanti, Sp.A',                                  '13:00', '17:00', false, null],
            ],
            // Klinik Spesialis Penyakit Dalam (ID 2)
            2 => [
                [Hari::SENIN,   'dr. Achmad Dainuri, Sp.PD',          '08:00', '12:00', false, null],
                [Hari::SENIN,   'dr. Dekritiana Dian Pratiwi, Sp.PD', '13:00', '17:00', false, null],
                [Hari::SELASA,  'dr. Dekritiana Dian Pratiwi, Sp.PD', '08:00', '12:00', false, null],
                [Hari::SELASA,  'dr. Achmad Dainuri, Sp.PD',          '13:00', '17:00', false, null],
                [Hari::RABU,    'dr. Achmad Dainuri, Sp.PD',          '08:00', '12:00', false, null],
                [Hari::RABU,    'dr. Dekritiana Dian Pratiwi, Sp.PD', '13:00', '17:00', false, null],
                [Hari::KAMIS,   'dr. Dekritiana Dian Pratiwi, Sp.PD', '08:00', '12:00', false, null],
                [Hari::KAMIS,   'dr. Achmad Dainuri, Sp.PD',          '13:00', '17:00', false, null],
                [Hari::JUMAT,   'dr. Achmad Dainuri, Sp.PD',          '08:00', '11:00', false, null],
                [Hari::SABTU,   'dr. Dekritiana Dian Pratiwi, Sp.PD', '08:00', '12:00', false, null],
            ],
            // Klinik Spesialis Bedah (ID 3)
            3 => [
                [Hari::SENIN,   'dr. Ahmad Zainuri, Sp.B',  '08:00', '12:00', false, null],
                [Hari::SENIN,   'dr. Haris Prabowo, Sp.B',  '13:00', '17:00', false, null],
                [Hari::RABU,    'dr. Ahmad Zainuri, Sp.B',  '08:00', '12:00', false, null],
                [Hari::RABU,    'dr. Haris Prabowo, Sp.B',  '13:00', '17:00', false, null],
                [Hari::JUMAT,   'dr. Ahmad Zainuri, Sp.B',  '08:00', '11:00', false, null],
                [Hari::SABTU,   'dr. Haris Prabowo, Sp.B',  '08:00', '12:00', false, null],
            ],
            // Klinik Spesialis Orthopaedi & Traumatology (ID 4)
            4 => [
                [Hari::SELASA,  'dr. Budi Santoso, Sp.OT',    '08:00', '12:00', false, null],
                [Hari::SELASA,  'dr. Gunawan Saputra, Sp.OT', '13:00', '17:00', false, null],
                [Hari::KAMIS,   'dr. Budi Santoso, Sp.OT',    '08:00', '12:00', false, null],
                [Hari::KAMIS,   'dr. Gunawan Saputra, Sp.OT', '13:00', '17:00', false, null],
                [Hari::SABTU,   'dr. Budi Santoso, Sp.OT',    '08:00', '12:00', false, null],
            ],
            // Klinik Spesialis Kebidanan & Kandungan (ID 5)
            5 => [
                [Hari::SENIN,   'dr. Ratna Dewi, Sp.OG',  '08:00', '12:00', false, null],
                [Hari::SENIN,   'dr. Sari Melati, Sp.OG', '13:00', '17:00', false, null],
                [Hari::SELASA,  'dr. Sari Melati, Sp.OG', '08:00', '12:00', false, null],
                [Hari::RABU,    'dr. Ratna Dewi, Sp.OG',  '08:00', '12:00', false, null],
                [Hari::RABU,    'dr. Sari Melati, Sp.OG', '13:00', '17:00', false, null],
                [Hari::KAMIS,   'dr. Ratna Dewi, Sp.OG',  '08:00', '12:00', false, null],
                [Hari::KAMIS,   'dr. Sari Melati, Sp.OG', '13:00', '17:00', false, null],
                [Hari::JUMAT,   'dr. Ratna Dewi, Sp.OG',  '08:00', '11:00', false, null],
                [Hari::SABTU,   'dr. Ratna Dewi, Sp.OG',  '08:00', '12:00', false, null],
                [Hari::SABTU,   'dr. Sari Melati, Sp.OG', '13:00', '17:00', false, null],
            ],
            // Klinik Spesialis Saraf (ID 6)
            6 => [
                [Hari::SENIN,   'dr. Hendra Kurniawan, Sp.N', '08:00', '12:00', false, null],
                [Hari::RABU,    'dr. Hendra Kurniawan, Sp.N', '08:00', '12:00', false, null],
                [Hari::JUMAT,   'dr. Hendra Kurniawan, Sp.N', '08:00', '11:00', false, null],
            ],
            // Klinik Spesialis THT (ID 7)
            7 => [
                [Hari::SELASA,  'dr. Iqbal Maulana, Sp.THT-KL', '08:00', '12:00', false, null],
                [Hari::KAMIS,   'dr. Iqbal Maulana, Sp.THT-KL', '08:00', '12:00', false, null],
                [Hari::SABTU,   'dr. Iqbal Maulana, Sp.THT-KL', '08:00', '12:00', false, null],
            ],
            // Klinik Spesialis Paru (ID 8)
            8 => [
                [Hari::SENIN,   'dr. Lestari Wulandari, Sp.P', '08:00', '12:00', false, null],
                [Hari::RABU,    'dr. Lestari Wulandari, Sp.P', '13:00', '17:00', false, null],
                [Hari::JUMAT,   'dr. Lestari Wulandari, Sp.P', '08:00', '11:00', false, null],
            ],
            // Klinik Spesialis Jantung (ID 9)
            9 => [
                [Hari::SELASA,  'dr. Muhammad Ridwan, Sp.JP', '08:00', '12:00', false, null],
                [Hari::KAMIS,   'dr. Muhammad Ridwan, Sp.JP', '08:00', '12:00', false, null],
                [Hari::SABTU,   'dr. Muhammad Ridwan, Sp.JP', '08:00', '12:00', false, null],
            ],
            // Klinik Spesialis Kulit & Kelamin (ID 10)
            10 => [
                [Hari::SENIN,   'dr. Anjas Asmara, Sp.KK', '08:00', '12:00', false, null],
                [Hari::RABU,    'dr. Anjas Asmara, Sp.KK', '08:00', '12:00', false, null],
                [Hari::JUMAT,   'dr. Anjas Asmara, Sp.KK', '08:00', '11:00', false, null],
                [Hari::SABTU,   'dr. Anjas Asmara, Sp.KK', '08:00', '12:00', false, null],
            ],
            // Klinik Spesialis Mata (ID 11)
            11 => [
                [Hari::SENIN,   'dr. Nur Hidayah, Sp.M', '08:00', '12:00', false, null],
                [Hari::SELASA,  'dr. Nur Hidayah, Sp.M', '13:00', '17:00', false, null],
                [Hari::KAMIS,   'dr. Nur Hidayah, Sp.M', '08:00', '12:00', false, null],
                [Hari::SABTU,   'dr. Nur Hidayah, Sp.M', '08:00', '12:00', false, null],
            ],
            // Klinik Spesialis Jiwa (ID 12)
            12 => [
                [Hari::SELASA,  'dr. Putri Rahma, Sp.KJ', '09:00', '12:00', false, null],
                [Hari::KAMIS,   'dr. Putri Rahma, Sp.KJ', '09:00', '12:00', false, null],
            ],
            // Klinik Spesialis Rehabilitasi (ID 13)
            13 => [
                [Hari::SENIN,   'dr. Rizky Firmansyah, Sp.KFR', '08:00', '12:00', false, null],
                [Hari::RABU,    'dr. Rizky Firmansyah, Sp.KFR', '08:00', '12:00', false, null],
                [Hari::JUMAT,   'dr. Rizky Firmansyah, Sp.KFR', '08:00', '11:00', false, null],
            ],
            // Klinik Spesialis Bedah Saraf (ID 14)
            14 => [
                [Hari::SELASA,  'dr. Satria Nugraha, Sp.BS', '08:00', '12:00', false, null],
                [Hari::KAMIS,   'dr. Satria Nugraha, Sp.BS', '08:00', '12:00', false, null],
            ],
            // Klinik Spesialis Urologi (ID 15)
            15 => [
                [Hari::SENIN,   'dr. Taufik Hidayat, Sp.U', '08:00', '12:00', false, null],
                [Hari::RABU,    'dr. Taufik Hidayat, Sp.U', '13:00', '17:00', false, null],
                [Hari::JUMAT,   'dr. Taufik Hidayat, Sp.U', '08:00', '11:00', false, null],
            ],
            // Klinik Layanan Psikologi (ID 16)
            16 => [
                [Hari::SENIN,   'Ade Ayu Harisdiane Putri, M.Psi, Psikolog', '09:00', '12:00', true,  null],
                [Hari::RABU,    'Ade Ayu Harisdiane Putri, M.Psi, Psikolog', '09:00', '12:00', true,  null],
                [Hari::JUMAT,   'Ade Ayu Harisdiane Putri, M.Psi, Psikolog', '09:00', '11:00', true,  null],
            ],
            // Klinik Gigi Umum (ID 17)
            17 => [
                [Hari::SENIN,   'drg. Uswatun Hasanah', '08:00', '12:00', false, null],
                [Hari::SELASA,  'drg. Uswatun Hasanah', '08:00', '12:00', false, null],
                [Hari::RABU,    'drg. Uswatun Hasanah', '13:00', '17:00', false, null],
                [Hari::KAMIS,   'drg. Uswatun Hasanah', '08:00', '12:00', false, null],
                [Hari::JUMAT,   'drg. Uswatun Hasanah', '08:00', '11:00', false, null],
                [Hari::SABTU,   'drg. Uswatun Hasanah', '08:00', '12:00', false, null],
            ],
            // Klinik Gigi Anak (ID 18)
            18 => [
                [Hari::SELASA,  'drg. Vella Maharani, Sp.KGA', '08:00', '12:00', false, null],
                [Hari::KAMIS,   'drg. Vella Maharani, Sp.KGA', '08:00', '12:00', false, null],
                [Hari::SABTU,   'drg. Vella Maharani, Sp.KGA', '08:00', '12:00', false, null],
            ],
            // Klinik Endodonsi (ID 19)
            19 => [
                [Hari::SENIN,   'drg. Wahyu Prasetyo, Sp.KG', '08:00', '12:00', false, null],
                [Hari::RABU,    'drg. Wahyu Prasetyo, Sp.KG', '08:00', '12:00', false, null],
                [Hari::JUMAT,   'drg. Wahyu Prasetyo, Sp.KG', '08:00', '11:00', false, null],
            ],
            // Klinik Gigi Bedah Mulut (ID 20)
            20 => [
                [Hari::SELASA,  'drg. Xena Anggraini, Sp.BM', '08:00', '12:00', false, null],
                [Hari::KAMIS,   'drg. Xena Anggraini, Sp.BM', '08:00', '12:00', false, null],
            ],
            // Klinik Gigi Orthodonsi (ID 21)
            21 => [
                [Hari::RABU,    'drg. Yudha Pratama, Sp.Ort', '08:00', '12:00', false, null],
                [Hari::SABTU,   'drg. Yudha Pratama, Sp.Ort', '08:00', '12:00', false, null],
            ],
            // Klinik Gigi Prostodonsia (ID 22)
            22 => [
                [Hari::SENIN,   'drg. Zahra Amelia, Sp.Pros', '08:00', '12:00', false, null],
                [Hari::KAMIS,   'drg. Zahra Amelia, Sp.Pros', '08:00', '12:00', false, null],
            ],
        ];

        // Lookup dokter_id berdasarkan nama (hasil DokterSeeder RS 1)
        $dokterMap = \App\Models\Dokter::where('rumah_sakit_id', 1)->pluck('id', 'nama');

        $total = 0;
        foreach ($data as $poliklinikId => $jadwals) {
            foreach ($jadwals as [$hari, $namaDokter, $mulai, $selesai, $sesuaiPerjanjian, $catatan]) {
                JadwalPraktek::create([
                    'poliklinik_id'     => $poliklinikId,
                    'hari'              => $hari->value,
                    'dokter_id'         => $dokterMap[$namaDokter] ?? null,
                    'nama_dokter'       => $namaDokter,
                    'waktu_mulai'       => $mulai,
                    'waktu_selesai'     => $selesai,
                    'sesuai_perjanjian' => $sesuaiPerjanjian,
                    'catatan'           => $catatan,
                ]);
                $total++;
            }
        }

        // $this->command->info("JadwalPraktek seeded: {$total} entri untuk " . count($data) . " poliklinik.");
    }
}
