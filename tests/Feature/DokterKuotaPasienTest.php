<?php

namespace Tests\Feature;

use App\Livewire\Dokter\Show;
use App\Models\Dokter;
use App\Models\RumahSakit;
use App\Models\Spesialis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class DokterKuotaPasienTest extends TestCase
{
    use RefreshDatabase;

    private RumahSakit $rs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rs = RumahSakit::create([
            'nama' => 'RS Test',
            'slug' => 'rs-test',
            'lokasi' => 'Test Lokasi',
            'alamat' => 'Test Alamat',
            'aktif' => true,
        ]);

        app()->instance('currentRumahSakit', $this->rs);
    }

    private function makeDokter(array $attrs = []): Dokter
    {
        $spesialis = Spesialis::create([
            'rumah_sakit_id' => $this->rs->id,
            'nama' => 'Spesialis Test',
            'slug' => 'spesialis-test',
        ]);

        return Dokter::create(array_merge([
            'rumah_sakit_id' => $this->rs->id,
            'spesialis_id' => $spesialis->id,
            'nama' => 'dr. Test',
            'slug' => 'dr-test',
            'aktif' => true,
        ], $attrs));
    }

    public function test_kuota_pasien_tampil_di_profil_dokter(): void
    {
        $dokter = $this->makeDokter([
            'kuota_pasien' => 'Maks. 20 pasien/hari, datang sebelum jam 10.00',
        ]);

        Livewire::test(Show::class, ['dokter' => $dokter])
            ->assertSee('Maks. 20 pasien/hari, datang sebelum jam 10.00');
    }

    public function test_tidak_ada_blok_kuota_jika_kosong(): void
    {
        $dokter = $this->makeDokter(['kuota_pasien' => null]);

        // Ikon "groups" cuma dipakai di blok kuota — kalau tidak muncul, berarti
        // blok @if($dokter->kuota_pasien) memang tidak ikut dirender.
        Livewire::test(Show::class, ['dokter' => $dokter])
            ->assertDontSee('groups');
    }

    public function test_status_antrian_live_tampil_jika_nomor_poli_terisi_dan_api_berhasil(): void
    {
        $this->rs->update(['link_antrian' => 'https://antrian.example.com']);
        Http::fake([
            'antrian.example.com/api/public/poli/5' => Http::response([
                'id' => '12',
                'nama_poli' => 'Poli Umum',
                'nama_dokter' => 'dr. Test',
                'status' => 'BERLANGSUNG',
            ]),
        ]);

        $dokter = $this->makeDokter(['nomor_poli_antrian' => 5]);

        Livewire::test(Show::class, ['dokter' => $dokter])
            ->assertSee('Antrian Poli Umum')
            ->assertSee('12')
            ->assertSee('BERLANGSUNG');
    }

    public function test_status_antrian_tidak_tampil_jika_nomor_poli_kosong(): void
    {
        $this->rs->update(['link_antrian' => 'https://antrian.example.com']);
        Http::fake();

        $dokter = $this->makeDokter(['nomor_poli_antrian' => null]);

        Livewire::test(Show::class, ['dokter' => $dokter])
            ->assertDontSee('confirmation_number');

        Http::assertNothingSent();
    }

    public function test_status_antrian_tidak_tampil_jika_rs_belum_punya_link_antrian(): void
    {
        $this->rs->update(['link_antrian' => null]);
        Http::fake();

        $dokter = $this->makeDokter(['nomor_poli_antrian' => 5]);

        Livewire::test(Show::class, ['dokter' => $dokter])
            ->assertDontSee('confirmation_number');

        Http::assertNothingSent();
    }

    public function test_status_antrian_tidak_tampil_jika_api_gagal(): void
    {
        $this->rs->update(['link_antrian' => 'https://antrian.example.com']);
        Http::fake([
            'antrian.example.com/*' => Http::response(null, 500),
        ]);

        $dokter = $this->makeDokter(['nomor_poli_antrian' => 5]);

        Livewire::test(Show::class, ['dokter' => $dokter])
            ->assertDontSee('confirmation_number');
    }
}
