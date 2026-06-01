<?php

namespace Tests\Unit\Models;

use App\Models\JadwalPraktek;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use App\Models\UnitLayanan;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoliKlinikTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('poliklinik', (new PoliKlinik)->getTable());
    }

    public function test_route_key_is_slug(): void
    {
        $this->assertEquals('slug', (new PoliKlinik)->getRouteKeyName());
    }

    public function test_unit_layanan_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new PoliKlinik)->unitLayanan());
    }

    public function test_jadwal_praktek_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new PoliKlinik)->jadwalPraktek());
    }

    public function test_belongs_to_unit_layanan(): void
    {
        $unitLayanan = UnitLayanan::factory()->create();
        $poli        = PoliKlinik::factory()->create(['unit_layanan_id' => $unitLayanan->id]);

        $this->assertEquals($unitLayanan->id, $poli->unitLayanan->id);
    }

    public function test_scoped_to_rumah_sakit_via_unit_layanan(): void
    {
        $rs1  = RumahSakit::factory()->create();
        $rs2  = RumahSakit::factory()->create();
        $ul1  = UnitLayanan::factory()->create(['rumah_sakit_id' => $rs1->id]);
        $ul2  = UnitLayanan::factory()->create(['rumah_sakit_id' => $rs2->id]);

        PoliKlinik::factory()->count(3)->create(['unit_layanan_id' => $ul1->id]);
        PoliKlinik::factory()->count(2)->create(['unit_layanan_id' => $ul2->id]);

        $count = PoliKlinik::whereHas('unitLayanan', fn ($q) => $q->where('rumah_sakit_id', $rs1->id))->count();
        $this->assertEquals(3, $count);
    }

    public function test_aktif_cast_to_boolean(): void
    {
        $poli = PoliKlinik::factory()->create(['aktif' => 1]);
        $this->assertIsBool($poli->aktif);
        $this->assertTrue($poli->aktif);
    }
}
