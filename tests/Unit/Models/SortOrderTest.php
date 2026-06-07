<?php

namespace Tests\Unit\Models;

use App\Models\FasilitasPendukung;
use App\Models\LayananUnggulan;
use App\Models\LinkLayanan;
use App\Models\Magazine;
use App\Models\PenunjangMedis;
use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SortOrderTest extends TestCase
{
    use RefreshDatabase;

    private function rs(): RumahSakit
    {
        return RumahSakit::factory()->create();
    }

    // ── Kolom sort_order ada di tabel ────────────────────────────────

    public function test_layanan_unggulan_punya_kolom_sort_order(): void
    {
        $rs = $this->rs();
        $item = LayananUnggulan::create([
            'rumah_sakit_id' => $rs->id,
            'nama'           => 'Test',
            'gambar'         => 'test.jpg',
            'deskripsi'      => 'x',
            'aktif'          => true,
            'sort_order'     => 3,
        ]);
        $this->assertEquals(3, $item->sort_order);
        $this->assertDatabaseHas('layanan_unggulan', ['sort_order' => 3]);
    }

    public function test_fasilitas_pendukung_punya_kolom_sort_order(): void
    {
        $rs = $this->rs();
        $item = FasilitasPendukung::create([
            'rumah_sakit_id' => $rs->id,
            'nama'           => 'Test',
            'aktif'          => true,
            'sort_order'     => 2,
        ]);
        $this->assertEquals(2, $item->sort_order);
    }

    public function test_penunjang_medis_punya_kolom_sort_order(): void
    {
        $rs = $this->rs();
        $item = PenunjangMedis::create([
            'rumah_sakit_id' => $rs->id,
            'nama'           => 'Test',
            'aktif'          => true,
            'sort_order'     => 5,
        ]);
        $this->assertEquals(5, $item->sort_order);
    }

    public function test_magazine_punya_kolom_sort_order(): void
    {
        $rs = $this->rs();
        $slug = 'mag-' . Str::random(5);
        $item = Magazine::create([
            'rumah_sakit_id' => $rs->id,
            'judul'          => 'Majalah Test',
            'slug'           => $slug,
            'file_pdf'       => 'test.pdf',
            'aktif'          => true,
            'sort_order'     => 1,
        ]);
        $this->assertEquals(1, $item->sort_order);
    }

    public function test_link_layanan_sort_order_default_nol(): void
    {
        $rs = $this->rs();
        $item = LinkLayanan::create([
            'rumah_sakit_id' => $rs->id,
            'label'          => 'Test',
            'value'          => 'Test',
            'link'           => 'https://example.com',
            'aktif'          => true,
        ]);
        $this->assertEquals(0, $item->fresh()->sort_order);
    }

    // ── Ordering berdasarkan sort_order ───────────────────────────────

    public function test_layanan_unggulan_diurutkan_by_sort_order(): void
    {
        $rs = $this->rs();
        LayananUnggulan::create(['rumah_sakit_id' => $rs->id, 'nama' => 'C', 'gambar' => 'c.jpg', 'deskripsi' => '', 'aktif' => true, 'sort_order' => 3]);
        LayananUnggulan::create(['rumah_sakit_id' => $rs->id, 'nama' => 'A', 'gambar' => 'a.jpg', 'deskripsi' => '', 'aktif' => true, 'sort_order' => 1]);
        LayananUnggulan::create(['rumah_sakit_id' => $rs->id, 'nama' => 'B', 'gambar' => 'b.jpg', 'deskripsi' => '', 'aktif' => true, 'sort_order' => 2]);

        $names = LayananUnggulan::where('rumah_sakit_id', $rs->id)
            ->orderBy('sort_order')
            ->pluck('nama')
            ->toArray();

        $this->assertEquals(['A', 'B', 'C'], $names);
    }

    public function test_fasilitas_pendukung_diurutkan_by_sort_order(): void
    {
        $rs = $this->rs();
        FasilitasPendukung::create(['rumah_sakit_id' => $rs->id, 'nama' => 'Z', 'aktif' => true, 'sort_order' => 10]);
        FasilitasPendukung::create(['rumah_sakit_id' => $rs->id, 'nama' => 'A', 'aktif' => true, 'sort_order' => 1]);

        $first = FasilitasPendukung::where('rumah_sakit_id', $rs->id)
            ->orderBy('sort_order')
            ->first();

        $this->assertEquals('A', $first->nama);
    }
}
