<?php

namespace Tests\Feature\Resources;

use App\Enums\Hari;
use App\Enums\StatusLayanan;
use App\Models\JadwalLayanan;
use App\Models\JadwalLayananHarian;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use App\Models\UnitLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalLayananResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_unauthenticated_redirects(): void
    {
        $this->get('/admin/jadwal-layanans')->assertRedirect('/admin/login');
    }

    public function test_super_admin_can_list(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/jadwal-layanans')
            ->assertOk();
    }

    public function test_can_create_jadwal_layanan(): void
    {
        $poli   = PoliKlinik::factory()->create();
        $jadwal = JadwalLayanan::factory()->create([
            'poliklinik_id'  => $poli->id,
            'hari'           => Hari::SENIN->value,
            'status_layanan' => StatusLayanan::BUKA->value,
        ]);

        $this->assertDatabaseHas('jadwal_layanan', [
            'poliklinik_id'  => $poli->id,
            'hari'           => 'SENIN',
            'status_layanan' => 'BUKA',
        ]);
        $this->assertEquals(Hari::SENIN, $jadwal->hari);
        $this->assertEquals(StatusLayanan::BUKA, $jadwal->status_layanan);
    }

    public function test_deleting_poliklinik_cascades_to_jadwal(): void
    {
        $poli   = PoliKlinik::factory()->create();
        $jadwal = JadwalLayanan::factory()->create(['poliklinik_id' => $poli->id]);

        $poli->delete();

        $this->assertDatabaseMissing('jadwal_layanan', ['id' => $jadwal->id]);
    }

    public function test_can_create_jadwal_layanan_harian(): void
    {
        $poli   = PoliKlinik::factory()->create();
        $jadwal = JadwalLayananHarian::factory()->create([
            'poliklinik_id'  => $poli->id,
            'tanggal'        => '2026-05-28',
            'status_layanan' => StatusLayanan::BUKA->value,
        ]);

        $this->assertDatabaseHas('jadwal_layanan_harian', [
            'poliklinik_id' => $poli->id,
        ]);
        $this->assertEquals('2026-05-28', $jadwal->tanggal->format('Y-m-d'));
    }

    public function test_jadwal_harian_scoped_to_rumah_sakit(): void
    {
        $rs1  = RumahSakit::factory()->create();
        $rs2  = RumahSakit::factory()->create();
        $ul1  = UnitLayanan::factory()->create(['rumah_sakit_id' => $rs1->id]);
        $ul2  = UnitLayanan::factory()->create(['rumah_sakit_id' => $rs2->id]);
        $pol1 = PoliKlinik::factory()->create(['unit_layanan_id' => $ul1->id]);
        $pol2 = PoliKlinik::factory()->create(['unit_layanan_id' => $ul2->id]);

        JadwalLayananHarian::factory()->count(4)->create(['poliklinik_id' => $pol1->id, 'tanggal' => today()]);
        JadwalLayananHarian::factory()->count(2)->create(['poliklinik_id' => $pol2->id, 'tanggal' => today()]);

        $count = JadwalLayananHarian::whereHas(
            'poliklinik.unitLayanan',
            fn ($q) => $q->where('rumah_sakit_id', $rs1->id)
        )->whereDate('tanggal', today())->count();

        $this->assertEquals(4, $count);
    }

    public function test_jadwal_mingguan_grouped_by_hari(): void
    {
        $poli = PoliKlinik::factory()->create();

        foreach ([Hari::SENIN, Hari::SELASA, Hari::RABU] as $hari) {
            JadwalLayanan::factory()->create([
                'poliklinik_id' => $poli->id,
                'hari'          => $hari->value,
            ]);
        }

        $perHari = JadwalLayanan::where('poliklinik_id', $poli->id)
            ->get()
            ->groupBy(fn ($j) => $j->hari->value);

        $this->assertCount(3, $perHari);
        $this->assertArrayHasKey('SENIN', $perHari->toArray());
    }
}
