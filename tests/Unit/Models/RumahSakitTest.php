<?php

namespace Tests\Unit\Models;

use App\Models\Gedung;
use App\Models\LinkLayanan;
use App\Models\RawatInap;
use App\Models\RumahSakit;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RumahSakitTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('rumah_sakit', (new RumahSakit)->getTable());
    }

    public function test_route_key_is_slug(): void
    {
        $this->assertEquals('slug', (new RumahSakit)->getRouteKeyName());
    }

    public function test_aktif_cast_to_boolean(): void
    {
        $rs = RumahSakit::factory()->create(['aktif' => 1]);
        $this->assertIsBool($rs->aktif);
        $this->assertTrue($rs->aktif);
    }

    public function test_gedung_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new RumahSakit)->gedung());
    }

    public function test_rawat_inap_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new RumahSakit)->rawatInap());
    }

    public function test_link_layanan_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new RumahSakit)->linkLayanan());
    }

    public function test_nonaktif_state(): void
    {
        $rs = RumahSakit::factory()->nonaktif()->create();
        $this->assertFalse($rs->aktif);
    }
}
