<?php

namespace Tests\Unit\Models;

use App\Models\Artikel;
use App\Models\KategoriArtikel;
use App\Models\RumahSakit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriArtikelTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('kategori_artikel', (new KategoriArtikel)->getTable());
    }

    public function test_rumah_sakit_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new KategoriArtikel)->rumahSakit());
    }

    public function test_artikel_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new KategoriArtikel)->artikel());
    }

    public function test_belongs_to_rumah_sakit(): void
    {
        $rs       = RumahSakit::factory()->create();
        $kategori = KategoriArtikel::factory()->create(['rumah_sakit_id' => $rs->id]);

        $this->assertEquals($rs->id, $kategori->rumahSakit->id);
    }

    public function test_scoped_to_rumah_sakit(): void
    {
        $rs1 = RumahSakit::factory()->create();
        $rs2 = RumahSakit::factory()->create();

        KategoriArtikel::factory()->count(3)->create(['rumah_sakit_id' => $rs1->id]);
        KategoriArtikel::factory()->count(2)->create(['rumah_sakit_id' => $rs2->id]);

        $this->assertCount(3, KategoriArtikel::where('rumah_sakit_id', $rs1->id)->get());
        $this->assertCount(2, KategoriArtikel::where('rumah_sakit_id', $rs2->id)->get());
    }

    public function test_artikel_relation_returns_correct_count(): void
    {
        $kategori = KategoriArtikel::factory()->create();

        Artikel::factory()->count(3)->create([
            'rumah_sakit_id'      => $kategori->rumah_sakit_id,
            'kategori_artikel_id' => $kategori->id,
        ]);

        $this->assertEquals(3, $kategori->artikel()->count());
    }

    public function test_slug_unik_per_rumah_sakit_boleh_sama_di_rs_lain(): void
    {
        $rs1 = RumahSakit::factory()->create();
        $rs2 = RumahSakit::factory()->create();

        KategoriArtikel::factory()->create(['rumah_sakit_id' => $rs1->id, 'slug' => 'tips-kesehatan']);
        $kategoriRs2 = KategoriArtikel::factory()->create(['rumah_sakit_id' => $rs2->id, 'slug' => 'tips-kesehatan']);

        $this->assertDatabaseHas('kategori_artikel', ['id' => $kategoriRs2->id, 'slug' => 'tips-kesehatan']);
    }

    public function test_slug_duplikat_di_rs_yang_sama_ditolak(): void
    {
        $rs = RumahSakit::factory()->create();
        KategoriArtikel::factory()->create(['rumah_sakit_id' => $rs->id, 'slug' => 'tips-kesehatan']);

        $this->expectException(QueryException::class);
        KategoriArtikel::factory()->create(['rumah_sakit_id' => $rs->id, 'slug' => 'tips-kesehatan']);
    }

    public function test_deleting_rumah_sakit_cascades_to_kategori_artikel(): void
    {
        $rs       = RumahSakit::factory()->create();
        $kategori = KategoriArtikel::factory()->create(['rumah_sakit_id' => $rs->id]);

        $rs->delete();

        $this->assertDatabaseMissing('kategori_artikel', ['id' => $kategori->id]);
    }
}
