<?php

namespace Tests\Feature\Resources;

use App\Filament\Resources\JadwalPraktekResource\Pages\JadwalPraktekPage;
use App\Models\Dokter;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class JadwalPraktekTest extends TestCase
{
    use RefreshDatabase;

    private RumahSakit $rs;
    private PoliKlinik $poli;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();

        $this->rs   = RumahSakit::factory()->create(['aktif' => true]);
        $this->poli = PoliKlinik::factory()->create(['rumah_sakit_id' => $this->rs->id, 'aktif' => true]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makePage(): JadwalPraktekPage
    {
        $sa = $this->superAdmin();
        Auth::login($sa);

        $page = new JadwalPraktekPage;
        $page->selectedRumahSakitId = $this->rs->id;
        $page->activeHari           = 'SENIN';
        $page->rows                 = [];
        $page->rowsCache            = [];
        return $page;
    }

    private function makeRow(array $attrs = []): array
    {
        return array_merge([
            'poliklinik_id'     => $this->poli->id,
            'dokter_id'         => null,
            'nama_dokter'       => 'dr. Test',
            'waktu_mulai'       => '08:00',
            'waktu_selesai'     => '12:00',
            'sesuai_perjanjian' => '0',
            'is_executive'      => '0',
            'catatan'           => null,
        ], $attrs);
    }

    private function makeDokterRow(array $attrs = []): array
    {
        return array_merge([
            'hari'              => 'SENIN',
            'poliklinik_id'     => $this->poli->id,
            'waktu_mulai'       => '08:00',
            'waktu_selesai'     => '12:00',
            'sesuai_perjanjian' => '0',
            'is_executive'      => '0',
            'catatan'           => null,
        ], $attrs);
    }

    // ── saveJadwal: validasi waktu_mulai vs sesuai_perjanjian ────────────────

    public function test_save_jadwal_gagal_jika_waktu_mulai_kosong_dan_tidak_sesuai_perjanjian(): void
    {
        $page = $this->makePage();
        $page->rows = [$this->makeRow(['waktu_mulai' => null, 'sesuai_perjanjian' => '0'])];
        $page->saveJadwal();

        $this->assertDatabaseMissing('jadwal_praktek', ['poliklinik_id' => $this->poli->id]);
    }

    public function test_save_jadwal_berhasil_jika_waktu_mulai_kosong_dan_sesuai_perjanjian(): void
    {
        $page = $this->makePage();
        $page->rows = [$this->makeRow(['waktu_mulai' => null, 'sesuai_perjanjian' => '1'])];
        $page->saveJadwal();

        $this->assertDatabaseHas('jadwal_praktek', [
            'poliklinik_id'     => $this->poli->id,
            'hari'              => 'SENIN',
            'waktu_mulai'       => null,
            'sesuai_perjanjian' => true,
        ]);
    }

    public function test_save_jadwal_berhasil_jika_waktu_mulai_diisi_walau_tidak_sesuai_perjanjian(): void
    {
        $page = $this->makePage();
        $page->rows = [$this->makeRow(['waktu_mulai' => '09:00', 'sesuai_perjanjian' => '0'])];
        $page->saveJadwal();

        $this->assertDatabaseHas('jadwal_praktek', [
            'poliklinik_id'     => $this->poli->id,
            'sesuai_perjanjian' => false,
        ]);
    }

    // ── saveDokterJadwal: validasi yang sama ─────────────────────────────────

    public function test_save_dokter_jadwal_gagal_jika_waktu_mulai_kosong_dan_tidak_sesuai_perjanjian(): void
    {
        $dokter = Dokter::factory()->create(['rumah_sakit_id' => $this->rs->id, 'aktif' => true]);

        $page = $this->makePage();
        $page->selectedDokterId = $dokter->id;
        $page->dokterRows = [$this->makeDokterRow(['waktu_mulai' => null, 'sesuai_perjanjian' => '0'])];
        $page->saveDokterJadwal();

        $this->assertDatabaseMissing('jadwal_praktek', ['dokter_id' => $dokter->id]);
    }

    public function test_save_dokter_jadwal_berhasil_jika_waktu_mulai_kosong_dan_sesuai_perjanjian(): void
    {
        $dokter = Dokter::factory()->create(['rumah_sakit_id' => $this->rs->id, 'aktif' => true]);

        $page = $this->makePage();
        $page->selectedDokterId = $dokter->id;
        $page->dokterRows = [$this->makeDokterRow(['waktu_mulai' => null, 'sesuai_perjanjian' => '1'])];
        $page->saveDokterJadwal();

        $this->assertDatabaseHas('jadwal_praktek', [
            'dokter_id'         => $dokter->id,
            'sesuai_perjanjian' => true,
            'waktu_mulai'       => null,
        ]);
    }

    public function test_save_dokter_jadwal_berhasil_jika_waktu_mulai_diisi_walau_tidak_sesuai_perjanjian(): void
    {
        $dokter = Dokter::factory()->create(['rumah_sakit_id' => $this->rs->id, 'aktif' => true]);

        $page = $this->makePage();
        $page->selectedDokterId = $dokter->id;
        $page->dokterRows = [$this->makeDokterRow(['waktu_mulai' => '09:00', 'sesuai_perjanjian' => '0'])];
        $page->saveDokterJadwal();

        $this->assertDatabaseHas('jadwal_praktek', [
            'dokter_id'         => $dokter->id,
            'sesuai_perjanjian' => false,
        ]);
    }
}
