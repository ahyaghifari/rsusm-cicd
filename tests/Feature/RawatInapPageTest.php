<?php

namespace Tests\Feature;

use App\Livewire\Pages\RawatInap as RawatInapPage;
use App\Models\Gedung;
use App\Models\KelasRawatInap;
use App\Models\RawatInap;
use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RawatInapPageTest extends TestCase
{
    use RefreshDatabase;

    private RumahSakit $rs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rs = RumahSakit::create([
            'nama'   => 'RS Test',
            'slug'   => 'rs-test',
            'lokasi' => 'Test Lokasi',
            'alamat' => 'Test Alamat',
            'aktif'  => true,
        ]);

        app()->instance('currentRumahSakit', $this->rs);
    }

    public function test_halaman_tampil_tanpa_filter(): void
    {
        $gedung = Gedung::create([
            'rumah_sakit_id' => $this->rs->id,
            'nama'           => 'Gedung A',
            'alias'          => 'GA',
        ]);

        RawatInap::create([
            'rumah_sakit_id' => $this->rs->id,
            'gedung_id'      => $gedung->id,
            'nama'           => 'Kamar VIP Mawar',
            'harga'          => 500000,
            'kapasitas'      => 1,
            'sort_order'     => 1,
            'aktif'          => true,
        ]);

        Livewire::test(RawatInapPage::class)
            ->assertSee('Kamar VIP Mawar');
    }

    public function test_filter_kelas_menyaring_kamar(): void
    {
        $kelasVip = KelasRawatInap::create([
            'rumah_sakit_id' => $this->rs->id,
            'nama'           => 'VIP',
        ]);
        $kelasReguler = KelasRawatInap::create([
            'rumah_sakit_id' => $this->rs->id,
            'nama'           => 'Reguler',
        ]);

        RawatInap::create([
            'rumah_sakit_id'      => $this->rs->id,
            'kelas_rawat_inap_id' => $kelasVip->id,
            'nama'                => 'Kamar VIP Mawar',
            'harga'               => 500000,
            'kapasitas'           => 1,
            'sort_order'          => 1,
            'aktif'               => true,
        ]);
        RawatInap::create([
            'rumah_sakit_id'      => $this->rs->id,
            'kelas_rawat_inap_id' => $kelasReguler->id,
            'nama'                => 'Kamar Reguler Melati',
            'harga'               => 200000,
            'kapasitas'           => 2,
            'sort_order'          => 2,
            'aktif'               => true,
        ]);

        Livewire::test(RawatInapPage::class)
            ->set('kelasFilter', $kelasVip->id)
            ->assertSee('Kamar VIP Mawar')
            ->assertDontSee('Kamar Reguler Melati');
    }

    public function test_filter_tanpa_hasil_menampilkan_pesan_kosong(): void
    {
        $kelasVip = KelasRawatInap::create([
            'rumah_sakit_id' => $this->rs->id,
            'nama'           => 'VIP',
        ]);

        RawatInap::create([
            'rumah_sakit_id'      => $this->rs->id,
            'kelas_rawat_inap_id' => $kelasVip->id,
            'nama'                => 'Kamar VIP Mawar',
            'harga'               => 500000,
            'kapasitas'           => 1,
            'sort_order'          => 1,
            'aktif'               => true,
        ]);

        $kelasLain = KelasRawatInap::create([
            'rumah_sakit_id' => $this->rs->id,
            'nama'           => 'ICU',
        ]);

        Livewire::test(RawatInapPage::class)
            ->set('kelasFilter', $kelasLain->id)
            ->assertSee('Tidak ada kamar yang sesuai kelas ini')
            ->assertDontSee('Kamar VIP Mawar');
    }

    public function test_render_ulang_tidak_error_walau_binding_currentrumahsakit_hilang(): void
    {
        // Sama seperti regresi yang sudah diperbaiki di KetersediaanRawatInap — filter
        // kelas men-trigger request AJAX ke /livewire/update yang tidak lewat
        // RumahSakitMiddleware, jadi binding 'currentRumahSakit' bisa hilang di request
        // kedua. boot() harus re-bind manual dari $rumah_sakit_id, bukan error.
        $kelasVip = KelasRawatInap::create([
            'rumah_sakit_id' => $this->rs->id,
            'nama'           => 'VIP',
        ]);

        RawatInap::create([
            'rumah_sakit_id'      => $this->rs->id,
            'kelas_rawat_inap_id' => $kelasVip->id,
            'nama'                => 'Kamar VIP Mawar',
            'harga'               => 500000,
            'kapasitas'           => 1,
            'sort_order'          => 1,
            'aktif'               => true,
        ]);

        $component = Livewire::test(RawatInapPage::class);

        app()->forgetInstance('currentRumahSakit');
        $this->assertFalse(app()->bound('currentRumahSakit'));

        $component->set('kelasFilter', $kelasVip->id)->assertSee('Kamar VIP Mawar');

        $this->assertTrue(app()->bound('currentRumahSakit'));
    }
}
