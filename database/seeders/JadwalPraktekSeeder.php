<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\JadwalPraktek;
use Illuminate\Database\Seeder;

class JadwalPraktekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dokters = Dokter::all();
        $days = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU'];
        
        $startTimes = ['08:00:00', '09:00:00', '13:00:00', '14:00:00'];
        $endTimes = ['12:00:00', '13:00:00', '16:00:00', '17:00:00'];

        foreach ($dokters as $dokter) {
            foreach ($days as $day) {
                // Determine schedule properties randomly
                $isLibur = false;
                $isSesuaiPerjanjian = false;
                $waktuMulai = '08:00:00';
                $waktuSelesai = '12:00:00';

                if ($day === 'MINGGU') {
                    // High probability of being a day off on Sunday
                    $isLibur = rand(1, 10) <= 8; // 80% chance
                } else {
                    $isLibur = rand(1, 10) <= 1; // 10% chance for other days
                }

                if (!$isLibur) {
                    $isSesuaiPerjanjian = rand(1, 10) <= 2; // 20% chance
                }

                if (!$isLibur && !$isSesuaiPerjanjian) {
                    // Standard schedule
                    $waktuMulai = $startTimes[array_rand($startTimes)];
                    
                    // Make sure waktu_selesai is after waktu_mulai
                    if ($waktuMulai === '08:00:00' || $waktuMulai === '09:00:00') {
                        $waktuSelesai = rand(0, 1) === 0 ? '12:00:00' : '13:00:00';
                    } else {
                        $waktuSelesai = rand(0, 1) === 0 ? '16:00:00' : '17:00:00';
                    }
                } else {
                    // If libur or sesuai_perjanjian, times can be null or default
                    $waktuMulai = '00:00:00';
                    $waktuSelesai = null;
                }

                JadwalPraktek::create([
                    'dokter_id' => $dokter->id,
                    'hari' => $day,
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $waktuSelesai,
                    'sesuai_perjanjian' => $isSesuaiPerjanjian,
                    'libur' => $isLibur,
                ]);
            }
        }
    }
}
