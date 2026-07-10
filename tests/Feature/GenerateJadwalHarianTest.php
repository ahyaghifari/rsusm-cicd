<?php

namespace Tests\Feature;

use App\Enums\Hari;
use App\Models\JadwalHarian;
use App\Models\JadwalPraktek;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateJadwalHarianTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_semua_baris_untuk_poliklinik_dengan_dua_dokter_hari_sama(): void
    {
        $rs   = RumahSakit::factory()->create(['aktif' => true]);
        $poli = PoliKlinik::factory()->create(['rumah_sakit_id' => $rs->id, 'aktif' => true]);

        $hariValue = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'][today()->dayOfWeek];

        JadwalPraktek::create([
            'poliklinik_id' => $poli->id,
            'hari'          => $hariValue,
            'nama_dokter'   => 'dr. Pagi',
            'waktu_mulai'   => '08:00',
            'waktu_selesai' => '12:00',
            'is_executive'  => false,
        ]);

        JadwalPraktek::create([
            'poliklinik_id' => $poli->id,
            'hari'          => $hariValue,
            'nama_dokter'   => 'dr. Siang',
            'waktu_mulai'   => '13:00',
            'waktu_selesai' => '17:00',
            'is_executive'  => false,
        ]);

        $this->artisan('jadwal:generate-harian', ['tanggal' => today()->format('Y-m-d')])
            ->assertSuccessful();

        $this->assertDatabaseHas('jadwal_harian', ['poliklinik_id' => $poli->id, 'nama_dokter' => 'dr. Pagi']);
        $this->assertDatabaseHas('jadwal_harian', ['poliklinik_id' => $poli->id, 'nama_dokter' => 'dr. Siang']);
        $this->assertEquals(2, JadwalHarian::whereDate('tanggal', today())->where('poliklinik_id', $poli->id)->count());
    }

    public function test_generate_skip_poliklinik_yang_sudah_punya_jadwal_harian(): void
    {
        $rs   = RumahSakit::factory()->create(['aktif' => true]);
        $poli = PoliKlinik::factory()->create(['rumah_sakit_id' => $rs->id, 'aktif' => true]);
        $hariValue = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'][today()->dayOfWeek];

        JadwalPraktek::create([
            'poliklinik_id' => $poli->id,
            'hari'          => $hariValue,
            'nama_dokter'   => 'dr. Template',
            'waktu_mulai'   => '08:00',
            'waktu_selesai' => '12:00',
            'is_executive'  => false,
        ]);

        JadwalHarian::create([
            'poliklinik_id'  => $poli->id,
            'tanggal'        => today()->format('Y-m-d'),
            'nama_dokter'    => 'dr. Manual',
            'jam_mulai'      => '09:00',
            'status_layanan' => 'BUKA',
            'sumber'         => 'MANUAL',
        ]);

        $this->artisan('jadwal:generate-harian', ['tanggal' => today()->format('Y-m-d')])
            ->assertSuccessful();

        $this->assertEquals(1, JadwalHarian::whereDate('tanggal', today())->where('poliklinik_id', $poli->id)->count());
        $this->assertDatabaseMissing('jadwal_harian', ['poliklinik_id' => $poli->id, 'nama_dokter' => 'dr. Template']);
    }

    public function test_generate_membawa_sesuai_perjanjian_dari_jadwal_praktek(): void
    {
        $rs   = RumahSakit::factory()->create(['aktif' => true]);
        $poli = PoliKlinik::factory()->create(['rumah_sakit_id' => $rs->id, 'aktif' => true]);
        $hariValue = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'][today()->dayOfWeek];

        JadwalPraktek::create([
            'poliklinik_id'     => $poli->id,
            'hari'              => $hariValue,
            'nama_dokter'       => 'dr. Perjanjian',
            'waktu_mulai'       => null,
            'waktu_selesai'     => null,
            'sesuai_perjanjian' => true,
            'is_executive'      => false,
        ]);

        $this->artisan('jadwal:generate-harian', ['tanggal' => today()->format('Y-m-d')])
            ->assertSuccessful();

        $this->assertDatabaseHas('jadwal_harian', [
            'poliklinik_id'     => $poli->id,
            'nama_dokter'       => 'dr. Perjanjian',
            'sesuai_perjanjian' => true,
        ]);
    }
}
