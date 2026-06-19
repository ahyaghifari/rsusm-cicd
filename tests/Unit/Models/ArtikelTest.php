<?php

namespace Tests\Unit\Models;

use App\Models\Artikel;
use App\Models\KategoriArtikel;
use App\Models\RumahSakit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtikelTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('artikel', (new Artikel)->getTable());
    }

    public function test_route_key_is_slug(): void
    {
        $this->assertEquals('slug', (new Artikel)->getRouteKeyName());
    }

    public function test_aktif_cast_to_boolean(): void
    {
        $artikel = Artikel::factory()->create(['aktif' => 1]);

        $this->assertIsBool($artikel->aktif);
        $this->assertTrue($artikel->aktif);
    }

    public function test_unggulan_cast_to_boolean(): void
    {
        $artikel = Artikel::factory()->create(['unggulan' => 1]);

        $this->assertIsBool($artikel->unggulan);
        $this->assertTrue($artikel->unggulan);
    }

    public function test_tanggal_publish_cast_to_date(): void
    {
        $artikel = Artikel::factory()->create(['tanggal_publish' => '2026-01-15']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $artikel->tanggal_publish);
    }

    public function test_rumah_sakit_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Artikel)->rumahSakit());
    }

    public function test_kategori_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Artikel)->kategori());
    }

    public function test_belongs_to_rumah_sakit(): void
    {
        $rs      = RumahSakit::factory()->create();
        $artikel = Artikel::factory()->create(['rumah_sakit_id' => $rs->id]);

        $this->assertEquals($rs->id, $artikel->rumahSakit->id);
    }

    public function test_belongs_to_kategori(): void
    {
        $kategori = KategoriArtikel::factory()->create();
        $artikel  = Artikel::factory()->create([
            'rumah_sakit_id'      => $kategori->rumah_sakit_id,
            'kategori_artikel_id' => $kategori->id,
        ]);

        $this->assertEquals($kategori->id, $artikel->kategori->id);
    }

    public function test_kategori_artikel_id_nullable(): void
    {
        $artikel = Artikel::factory()->create(['kategori_artikel_id' => null]);

        $this->assertNull($artikel->kategori_artikel_id);
        $this->assertNull($artikel->kategori);
    }

    public function test_scope_aktif_hanya_tampilkan_yang_aktif(): void
    {
        $rs = RumahSakit::factory()->create();

        Artikel::factory()->create(['rumah_sakit_id' => $rs->id, 'aktif' => true]);
        Artikel::factory()->create(['rumah_sakit_id' => $rs->id, 'aktif' => false]);
        Artikel::factory()->create(['rumah_sakit_id' => $rs->id, 'aktif' => true]);

        $this->assertCount(2, Artikel::aktif()->where('rumah_sakit_id', $rs->id)->get());
    }

    public function test_scoped_per_rumah_sakit(): void
    {
        $rs1 = RumahSakit::factory()->create();
        $rs2 = RumahSakit::factory()->create();

        Artikel::factory()->count(3)->create(['rumah_sakit_id' => $rs1->id]);
        Artikel::factory()->count(2)->create(['rumah_sakit_id' => $rs2->id]);

        $this->assertCount(3, Artikel::where('rumah_sakit_id', $rs1->id)->get());
        $this->assertCount(2, Artikel::where('rumah_sakit_id', $rs2->id)->get());
    }

    public function test_slug_unik_per_rumah_sakit_boleh_sama_di_rs_lain(): void
    {
        $rs1 = RumahSakit::factory()->create();
        $rs2 = RumahSakit::factory()->create();

        Artikel::factory()->create(['rumah_sakit_id' => $rs1->id, 'slug' => 'artikel-sama']);
        $artikelRs2 = Artikel::factory()->create(['rumah_sakit_id' => $rs2->id, 'slug' => 'artikel-sama']);

        $this->assertDatabaseHas('artikel', ['id' => $artikelRs2->id, 'slug' => 'artikel-sama']);
    }

    public function test_slug_duplikat_di_rs_yang_sama_ditolak(): void
    {
        $rs = RumahSakit::factory()->create();
        Artikel::factory()->create(['rumah_sakit_id' => $rs->id, 'slug' => 'artikel-sama']);

        $this->expectException(QueryException::class);
        Artikel::factory()->create(['rumah_sakit_id' => $rs->id, 'slug' => 'artikel-sama']);
    }

    public function test_deleting_rumah_sakit_cascades_to_artikel(): void
    {
        $rs      = RumahSakit::factory()->create();
        $artikel = Artikel::factory()->create(['rumah_sakit_id' => $rs->id]);

        $rs->delete();

        $this->assertDatabaseMissing('artikel', ['id' => $artikel->id]);
    }

    public function test_deleting_kategori_sets_kategori_artikel_id_null(): void
    {
        $kategori = KategoriArtikel::factory()->create();
        $artikel  = Artikel::factory()->create([
            'rumah_sakit_id'      => $kategori->rumah_sakit_id,
            'kategori_artikel_id' => $kategori->id,
        ]);

        $kategori->delete();

        $this->assertNull($artikel->fresh()->kategori_artikel_id);
        $this->assertDatabaseHas('artikel', ['id' => $artikel->id]);
    }

    public function test_soft_delete_tidak_menghapus_baris_dari_database(): void
    {
        $artikel = Artikel::factory()->create();

        $artikel->delete();

        $this->assertSoftDeleted('artikel', ['id' => $artikel->id]);
        $this->assertDatabaseHas('artikel', ['id' => $artikel->id]);
    }
}
