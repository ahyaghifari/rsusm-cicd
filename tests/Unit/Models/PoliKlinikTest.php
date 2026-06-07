<?php

namespace Tests\Unit\Models;

use App\Models\JadwalPraktek;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
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

    public function test_rumah_sakit_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new PoliKlinik)->rumahSakit());
    }

    public function test_jadwal_praktek_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new PoliKlinik)->jadwalPraktek());
    }

    public function test_belongs_to_rumah_sakit(): void
    {
        $rs   = RumahSakit::factory()->create();
        $poli = PoliKlinik::factory()->create(['rumah_sakit_id' => $rs->id]);

        $this->assertEquals($rs->id, $poli->rumahSakit->id);
    }

    public function test_scoped_to_rumah_sakit_via_rumah_sakit_id(): void
    {
        $rs1 = RumahSakit::factory()->create();
        $rs2 = RumahSakit::factory()->create();

        PoliKlinik::factory()->count(3)->create(['rumah_sakit_id' => $rs1->id]);
        PoliKlinik::factory()->count(2)->create(['rumah_sakit_id' => $rs2->id]);

        $count = PoliKlinik::where('rumah_sakit_id', $rs1->id)->count();
        $this->assertEquals(3, $count);
    }

    public function test_aktif_cast_to_boolean(): void
    {
        $poli = PoliKlinik::factory()->create(['aktif' => 1]);
        $this->assertIsBool($poli->aktif);
        $this->assertTrue($poli->aktif);
    }
}
