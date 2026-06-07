<?php

namespace Tests\Unit\Models;

use App\Models\Halaman;
use App\Models\RumahSakit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HalamanTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('halaman', (new Halaman)->getTable());
    }

    public function test_rumah_sakit_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Halaman)->rumahSakit());
    }

    public function test_aktif_cast_to_boolean(): void
    {
        $rs     = RumahSakit::factory()->create();
        $halaman = Halaman::create([
            'rumah_sakit_id' => $rs->id,
            'slug'           => 'visi-misi',
            'judul'          => 'Visi & Misi',
            'konten'         => '<p>Isi konten</p>',
            'aktif'          => 1,
        ]);

        $this->assertIsBool($halaman->fresh()->aktif);
        $this->assertTrue($halaman->fresh()->aktif);
    }

    public function test_kata_kunci_nullable(): void
    {
        $rs     = RumahSakit::factory()->create();
        $halaman = Halaman::create([
            'rumah_sakit_id' => $rs->id,
            'slug'           => 'profil',
            'judul'          => 'Profil RS',
            'kata_kunci'     => null,
            'aktif'          => true,
        ]);

        $this->assertNull($halaman->kata_kunci);
    }

    public function test_kata_kunci_tersimpan_dengan_benar(): void
    {
        $rs     = RumahSakit::factory()->create();
        $halaman = Halaman::create([
            'rumah_sakit_id' => $rs->id,
            'slug'           => 'sejarah',
            'judul'          => 'Sejarah RS',
            'kata_kunci'     => 'sejarah, profil, tentang kami',
            'aktif'          => true,
        ]);

        $this->assertEquals('sejarah, profil, tentang kami', $halaman->kata_kunci);
        $this->assertDatabaseHas('halaman', ['kata_kunci' => 'sejarah, profil, tentang kami']);
    }

    public function test_slug_adalah_route_key(): void
    {
        $rs     = RumahSakit::factory()->create();
        $halaman = Halaman::create([
            'rumah_sakit_id' => $rs->id,
            'slug'           => 'tentang-kami',
            'judul'          => 'Tentang Kami',
            'aktif'          => true,
        ]);

        $found = Halaman::where('slug', 'tentang-kami')->first();
        $this->assertNotNull($found);
        $this->assertEquals($halaman->id, $found->id);
    }

    public function test_scoped_per_rumah_sakit(): void
    {
        $rs1 = RumahSakit::factory()->create();
        $rs2 = RumahSakit::factory()->create();

        Halaman::create(['rumah_sakit_id' => $rs1->id, 'slug' => 'h1', 'judul' => 'H1', 'aktif' => true]);
        Halaman::create(['rumah_sakit_id' => $rs1->id, 'slug' => 'h2', 'judul' => 'H2', 'aktif' => true]);
        Halaman::create(['rumah_sakit_id' => $rs2->id, 'slug' => 'h3', 'judul' => 'H3', 'aktif' => true]);

        $this->assertCount(2, Halaman::where('rumah_sakit_id', $rs1->id)->get());
    }

    public function test_hanya_yang_aktif_tersedia_di_nav(): void
    {
        $rs = RumahSakit::factory()->create();

        Halaman::create(['rumah_sakit_id' => $rs->id, 'slug' => 'a1', 'judul' => 'A', 'aktif' => true]);
        Halaman::create(['rumah_sakit_id' => $rs->id, 'slug' => 'a2', 'judul' => 'B', 'aktif' => false]);

        $nav = Halaman::where('rumah_sakit_id', $rs->id)
            ->where('aktif', true)
            ->get(['id', 'slug', 'judul']);

        $this->assertCount(1, $nav);
        $this->assertEquals('A', $nav->first()->judul);
    }
}
