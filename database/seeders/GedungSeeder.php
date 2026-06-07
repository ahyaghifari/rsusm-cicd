<?php

namespace Database\Seeders;

use App\Models\Gedung;
use Illuminate\Database\Seeder;

class GedungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Gedung::create([
            'rumah_sakit_id' => 1,
            'nama' => 'Shofa',
            'alias' => 'Gedung A',
        ]);

        Gedung::create([
            'rumah_sakit_id' => 1,
            'nama' => 'Marwah',
            'alias' => 'Gedung B',
        ]);

        Gedung::create([
            'rumah_sakit_id' => 1,
            'nama' => 'Muzdalifah',
            'alias' => 'Gedung C',
        ]);
    }
}
