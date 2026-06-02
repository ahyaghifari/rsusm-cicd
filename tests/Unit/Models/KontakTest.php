<?php

namespace Tests\Unit\Models;

use App\Models\Kontak;
use App\Models\RumahSakit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KontakTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('kontak', (new Kontak)->getTable());
    }

    public function test_rumah_sakit_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Kontak)->rumahSakit());
    }

    public function test_can_create_kontak_with_pendaftaran_kategori(): void
    {
        $rs = RumahSakit::factory()->create();

        $kontak = Kontak::create([
            'rumah_sakit_id' => $rs->id,
            'label'          => 'Pendaftaran',
            'value'          => '0511-5910889',
            'link'           => 'tel:051159108889',
            'kategori'       => 'PENDAFTARAN',
            'aktif'          => true,
        ]);

        $this->assertDatabaseHas('kontak', [
            'kategori' => 'PENDAFTARAN',
            'label'    => 'Pendaftaran',
        ]);
        $this->assertEquals('PENDAFTARAN', $kontak->kategori);
    }

    public function test_pendaftaran_kontak_filter_bekerja(): void
    {
        $rs = RumahSakit::factory()->create();

        Kontak::create(['rumah_sakit_id' => $rs->id, 'label' => 'Instagram', 'value' => '@rs', 'link' => 'https://instagram.com/rs', 'kategori' => 'SOSIAL MEDIA', 'aktif' => true]);
        Kontak::create(['rumah_sakit_id' => $rs->id, 'label' => 'Hotline', 'value' => '0511-111', 'link' => 'tel:0511111', 'kategori' => 'OPERASIONAL', 'aktif' => true]);
        Kontak::create(['rumah_sakit_id' => $rs->id, 'label' => 'Pendaftaran', 'value' => '0511-222', 'link' => 'tel:0511222', 'kategori' => 'PENDAFTARAN', 'aktif' => true]);
        Kontak::create(['rumah_sakit_id' => $rs->id, 'label' => 'WhatsApp', 'value' => '0821-xxx', 'link' => 'https://wa.me/6282', 'kategori' => 'PENDAFTARAN', 'aktif' => true]);

        $pendaftaran = Kontak::where('rumah_sakit_id', $rs->id)
            ->where('kategori', 'PENDAFTARAN')
            ->where('aktif', true)
            ->get();

        $this->assertCount(2, $pendaftaran);
        $pendaftaran->each(fn ($k) => $this->assertEquals('PENDAFTARAN', $k->kategori));
    }

    public function test_sosial_media_tidak_masuk_filter_pendaftaran(): void
    {
        $rs = RumahSakit::factory()->create();

        Kontak::create(['rumah_sakit_id' => $rs->id, 'label' => 'Instagram', 'value' => '@rs', 'link' => null, 'kategori' => 'SOSIAL MEDIA', 'aktif' => true]);

        $pendaftaran = Kontak::where('rumah_sakit_id', $rs->id)
            ->where('kategori', 'PENDAFTARAN')
            ->get();

        $this->assertCount(0, $pendaftaran);
    }

    public function test_kontak_tidak_aktif_tidak_dibagikan(): void
    {
        $rs = RumahSakit::factory()->create();

        Kontak::create(['rumah_sakit_id' => $rs->id, 'label' => 'Old', 'value' => '000', 'link' => null, 'kategori' => 'PENDAFTARAN', 'aktif' => false]);

        $aktif = Kontak::where('rumah_sakit_id', $rs->id)->where('aktif', true)->get();
        $this->assertCount(0, $aktif);
    }
}
