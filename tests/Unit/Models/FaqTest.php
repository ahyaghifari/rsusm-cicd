<?php

namespace Tests\Unit\Models;

use App\Models\Faq;
use App\Models\RumahSakit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('faq', (new Faq)->getTable());
    }

    public function test_rumah_sakit_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Faq)->rumahSakit());
    }

    public function test_aktif_cast_to_boolean(): void
    {
        $rs  = RumahSakit::factory()->create();
        $faq = Faq::create([
            'rumah_sakit_id' => $rs->id,
            'judul'          => 'Cara daftar',
            'deskripsi'      => 'Datang langsung ke loket.',
            'aktif'          => 1,
        ]);

        $this->assertIsBool($faq->fresh()->aktif);
        $this->assertTrue($faq->fresh()->aktif);
    }

    public function test_kata_kunci_nullable_field(): void
    {
        $rs  = RumahSakit::factory()->create();
        $faq = Faq::create([
            'rumah_sakit_id' => $rs->id,
            'judul'          => 'Tentang BPJS',
            'deskripsi'      => 'Info BPJS.',
            'kata_kunci'     => null,
            'aktif'          => true,
        ]);

        $this->assertNull($faq->kata_kunci);
    }

    public function test_kata_kunci_tersimpan_dengan_benar(): void
    {
        $rs  = RumahSakit::factory()->create();
        $faq = Faq::create([
            'rumah_sakit_id' => $rs->id,
            'judul'          => 'Info Biaya',
            'deskripsi'      => 'Tarif kamar.',
            'kata_kunci'     => 'biaya, tarif, harga, bpjs',
            'aktif'          => true,
        ]);

        $this->assertEquals('biaya, tarif, harga, bpjs', $faq->kata_kunci);
        $this->assertDatabaseHas('faq', ['kata_kunci' => 'biaya, tarif, harga, bpjs']);
    }

    public function test_scope_aktif_hanya_tampilkan_yang_aktif(): void
    {
        $rs = RumahSakit::factory()->create();

        Faq::create(['rumah_sakit_id' => $rs->id, 'judul' => 'A', 'deskripsi' => 'x', 'aktif' => true]);
        Faq::create(['rumah_sakit_id' => $rs->id, 'judul' => 'B', 'deskripsi' => 'x', 'aktif' => false]);
        Faq::create(['rumah_sakit_id' => $rs->id, 'judul' => 'C', 'deskripsi' => 'x', 'aktif' => true]);

        $this->assertCount(2, Faq::aktif()->where('rumah_sakit_id', $rs->id)->get());
    }

    public function test_scoped_per_rumah_sakit(): void
    {
        $rs1 = RumahSakit::factory()->create();
        $rs2 = RumahSakit::factory()->create();

        Faq::create(['rumah_sakit_id' => $rs1->id, 'judul' => 'Q1', 'deskripsi' => 'x', 'aktif' => true]);
        Faq::create(['rumah_sakit_id' => $rs1->id, 'judul' => 'Q2', 'deskripsi' => 'x', 'aktif' => true]);
        Faq::create(['rumah_sakit_id' => $rs2->id, 'judul' => 'Q3', 'deskripsi' => 'x', 'aktif' => true]);

        $this->assertCount(2, Faq::where('rumah_sakit_id', $rs1->id)->get());
        $this->assertCount(1, Faq::where('rumah_sakit_id', $rs2->id)->get());
    }

    public function test_sort_order_field_dapat_diisi(): void
    {
        $rs  = RumahSakit::factory()->create();
        $faq = Faq::create([
            'rumah_sakit_id' => $rs->id,
            'judul'          => 'Urutan 5',
            'deskripsi'      => 'x',
            'sort_order'     => 5,
            'aktif'          => true,
        ]);

        $this->assertEquals(5, $faq->sort_order);
    }
}
