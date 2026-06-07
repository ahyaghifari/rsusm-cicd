<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
         User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'superadmin',
            'rumah_sakit_id' => null
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
            RumahSakitSeeder::class,
            SpesialisSeeder::class,
            DokterSeeder::class,
            GedungSeeder::class,
            RawatInapSeeder::class,
            FasilitasRawatInapSeeder::class,
            FasilitasPendukungSeeder::class,
            PenunjangMedisSeeder::class,
            PartnerSeeder::class,
            KontakSeeder::class,
            LinkLayananSeeder::class,
            PoliKlinikSeeder::class,
            JadwalPraktekSeeder::class,
            HalamanSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Admin BJB',
            'email' => 'ahyaghifari288@gmail.com',
            'role' => 'admin',
            'rumah_sakit_id' => 1
        ]);
    }
}
