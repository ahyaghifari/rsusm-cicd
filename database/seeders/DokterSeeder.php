<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\Spesialis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DokterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        $hospitalIds = [1, 2];

        foreach ($hospitalIds as $hospitalId) {
            // Get all specialists for this hospital
            $specialistIds = Spesialis::where('rumah_sakit_id', $hospitalId)->pluck('id')->toArray();

            if (empty($specialistIds)) {
                continue;
            }

            for ($i = 0; $i < 5; $i++) {
                $isDentist = $faker->boolean(20);
                $prefix = $isDentist ? 'drg. ' : 'dr. ';
                $name = $prefix . $faker->name();
                $slug = Str::slug($name) . '-' . $hospitalId . '-' . $i;
                
                $spesialisId = $faker->randomElement($specialistIds);
                $specialist = Spesialis::find($spesialisId);

                Dokter::create([
                    'nama' => $name,
                    'slug' => $slug,
                    'foto' => 'https://i.pravatar.cc/300?img=' . $faker->numberBetween(1, 70),
                    'deskripsi' => $faker->paragraph(2),
                    'aktif' => true,
                    'pendidikan' => "<ul><li>S1 Kedokteran, Universitas Indonesia</li><li>Spesialis " . str_replace('Spesialis ', '', $specialist->nama) . ", Universitas Gadjah Mada</li></ul>",
                    'pelatihan' => "<ul><li>Pelatihan Kegawatdaruratan Medis Terpadu</li><li>Simposium Nasional Terapi Modern " . str_replace('Spesialis ', '', $specialist->nama) . "</li></ul>",
                    'rumah_sakit_id' => $hospitalId,
                    'spesialis_id' => $spesialisId,
                ]);
            }
        }
    }
}
