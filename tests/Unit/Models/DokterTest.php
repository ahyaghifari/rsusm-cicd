<?php

namespace Tests\Unit\Models;

use App\Models\Dokter;
use App\Models\RumahSakit;
use App\Models\Spesialis;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokterTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('dokter', (new Dokter)->getTable());
    }

    public function test_route_key_is_slug(): void
    {
        $this->assertEquals('slug', (new Dokter)->getRouteKeyName());
    }

    public function test_aktif_cast_to_boolean(): void
    {
        $dokter = Dokter::factory()->create(['aktif' => 1]);
        $this->assertIsBool($dokter->aktif);
        $this->assertTrue($dokter->aktif);
    }

    public function test_rumah_sakit_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Dokter)->rumahSakit());
    }

    public function test_spesialis_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Dokter)->spesialis());
    }

    public function test_jadwal_praktek_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Dokter)->jadwalPraktek());
    }

    public function test_belongs_to_rumah_sakit(): void
    {
        $rs     = RumahSakit::factory()->create();
        $dokter = Dokter::factory()->create(['rumah_sakit_id' => $rs->id]);

        $this->assertEquals($rs->id, $dokter->rumahSakit->id);
    }

    public function test_belongs_to_spesialis(): void
    {
        $spesialis = Spesialis::factory()->create();
        $dokter    = Dokter::factory()->create([
            'rumah_sakit_id' => $spesialis->rumah_sakit_id,
            'spesialis_id'   => $spesialis->id,
        ]);

        $this->assertEquals($spesialis->id, $dokter->spesialis->id);
    }

    public function test_scoped_to_rumah_sakit(): void
    {
        $rs1 = RumahSakit::factory()->create();
        $rs2 = RumahSakit::factory()->create();

        Dokter::factory()->count(3)->create(['rumah_sakit_id' => $rs1->id]);
        Dokter::factory()->count(2)->create(['rumah_sakit_id' => $rs2->id]);

        $this->assertCount(3, Dokter::where('rumah_sakit_id', $rs1->id)->get());
        $this->assertCount(2, Dokter::where('rumah_sakit_id', $rs2->id)->get());
    }
}
