<?php

namespace Tests\Feature\Resources;

use App\Filament\Resources\JadwalHarianResource\Pages\JadwalHarianPage;
use App\Models\JadwalHarian;
use App\Models\JadwalHarianPerubahan;
use App\Models\JadwalPraktek;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class JadwalHarianTest extends TestCase
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

    private function livewire()
    {
        return Livewire::actingAs($this->superAdmin())->test(JadwalHarianPage::class);
    }

    private function makePage(): JadwalHarianPage
    {
        $sa = $this->superAdmin();
        Auth::login($sa);

        $page = new JadwalHarianPage;
        $page->selectedRumahSakitId = $this->rs->id;
        $page->activeTanggal        = today()->format('Y-m-d');
        $page->rows                 = [];
        $page->rowsCache            = [];
        return $page;
    }

    private function makeJadwalHarian(array $attrs = []): JadwalHarian
    {
        return JadwalHarian::create(array_merge([
            'poliklinik_id'  => $this->poli->id,
            'tanggal'        => today()->format('Y-m-d'),
            'nama_dokter'    => 'dr. Test',
            'jam_mulai'      => '08:00',
            'status_layanan' => 'BUKA',
            'sumber'         => 'GENERATE',
        ], $attrs));
    }

    private function makeRow(array $attrs = []): array
    {
        return array_merge([
            'id'             => null,
            'poliklinik_id'  => $this->poli->id,
            'dokter_id'      => null,
            'nama_dokter'    => null,
            'jam_mulai'      => '08:00',
            'jam_selesai'    => null,
            'status_layanan' => 'BUKA',
            'catatan'        => null,
            'sumber'         => 'MANUAL',
            'is_executive'   => false,
        ], $attrs);
    }

    // ── Access ───────────────────────────────────────────────────────────────

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get($this->adminUrl('jadwal-harians'))->assertRedirect($this->adminUrl('login'));
    }

    public function test_superadmin_dapat_akses_halaman(): void
    {
        $this->actingAs($this->superAdmin())
            ->get($this->adminUrl('jadwal-harians'))
            ->assertOk();
    }

    // ── mustPickUnit ──────────────────────────────────────────────────────────

    public function test_must_pick_unit_selalu_false(): void
    {
        $page = $this->makePage();
        $this->assertFalse($page->mustPickUnit());
    }

    // ── loadRows (unit test PHP langsung) ────────────────────────────────────

    public function test_load_rows_kosong_jika_tidak_ada_jadwal(): void
    {
        $page = $this->makePage();
        $page->loadRows();
        $this->assertEmpty($page->rows);
    }

    public function test_load_rows_tampilkan_jadwal_hari_ini(): void
    {
        $this->makeJadwalHarian(['nama_dokter' => 'dr. Test', 'sumber' => 'GENERATE']);
        $page = $this->makePage();
        $page->loadRows();

        $this->assertCount(1, $page->rows);
        $first = array_values($page->rows)[0];
        $this->assertEquals('dr. Test', $first['nama_dokter']);
        $this->assertEquals('GENERATE', $first['sumber']);
    }

    public function test_load_rows_tidak_tampilkan_jadwal_tanggal_lain(): void
    {
        $this->makeJadwalHarian(['tanggal' => today()->subDay()->format('Y-m-d')]);
        $page = $this->makePage();
        $page->loadRows();
        $this->assertEmpty($page->rows);
    }

    public function test_load_rows_tidak_tampilkan_jadwal_rs_lain(): void
    {
        $rs2   = RumahSakit::factory()->create();
        $poli2 = PoliKlinik::factory()->create(['rumah_sakit_id' => $rs2->id]);

        JadwalHarian::create([
            'poliklinik_id'  => $poli2->id,
            'tanggal'        => today()->format('Y-m-d'),
            'nama_dokter'    => 'dr. RS Lain',
            'jam_mulai'      => '08:00',
            'status_layanan' => 'BUKA',
            'sumber'         => 'GENERATE',
        ]);

        $page = $this->makePage();
        $page->loadRows();
        $this->assertEmpty($page->rows);
    }

    // ── addRow / removeRow / resetJadwal ──────────────────────────────────────

    public function test_add_row_default_sumber_manual(): void
    {
        $page = $this->makePage();
        $page->addRow();
        $this->assertCount(1, $page->rows);
        $first = array_values($page->rows)[0];
        $this->assertEquals('MANUAL', $first['sumber']);
        $this->assertEquals('BUKA', $first['status_layanan']);
        $this->assertNull($first['id']);
    }

    public function test_remove_row_hapus_dari_array(): void
    {
        $page = $this->makePage();
        $page->addRow();
        $page->addRow();
        $page->removeRow(array_key_first($page->rows));
        $this->assertCount(1, $page->rows);
    }

    public function test_reset_jadwal_kosongkan_semua_rows(): void
    {
        $page = $this->makePage();
        $page->addRow();
        $page->addRow();
        $page->resetJadwal();
        $this->assertEmpty($page->rows);
    }

    // ── saveJadwal ────────────────────────────────────────────────────────────

    public function test_save_gagal_jika_poliklinik_kosong(): void
    {
        $page = $this->makePage();
        $page->rows = [$this->makeRow(['poliklinik_id' => null])];
        $page->saveJadwal();
        $this->assertDatabaseMissing('jadwal_harian', ['tanggal' => today()->format('Y-m-d')]);
    }

    public function test_save_gagal_jika_jam_mulai_kosong(): void
    {
        $page = $this->makePage();
        $page->rows = [$this->makeRow(['jam_mulai' => null])];
        $page->saveJadwal();
        $this->assertDatabaseMissing('jadwal_harian', ['tanggal' => today()->format('Y-m-d')]);
    }

    public function test_save_berhasil_simpan_baris_manual(): void
    {
        $page = $this->makePage();
        $page->rows = [$this->makeRow(['nama_dokter' => 'dr. Baru', 'jam_selesai' => '12:00'])];
        $page->saveJadwal();

        $this->assertDatabaseHas('jadwal_harian', [
            'poliklinik_id' => $this->poli->id,
            'nama_dokter'   => 'dr. Baru',
            'sumber'        => 'MANUAL',
        ]);
    }

    public function test_save_replace_all_hapus_baris_lama(): void
    {
        $this->makeJadwalHarian(['nama_dokter' => 'dr. Lama']);

        $page = $this->makePage();
        $page->rows = [$this->makeRow([
            'nama_dokter'    => 'dr. Baru',
            'jam_mulai'      => '09:00',
            'sumber'         => 'GENERATE',
        ])];
        $page->saveJadwal();

        $this->assertDatabaseMissing('jadwal_harian', ['nama_dokter' => 'dr. Lama']);
        $this->assertDatabaseHas('jadwal_harian', ['nama_dokter' => 'dr. Baru']);
    }

    // ── Tracking Perubahan ────────────────────────────────────────────────────

    public function test_generate_libur_membuat_record_perubahan(): void
    {
        $tanggal = today()->format('Y-m-d');
        $page    = $this->makePage();
        $page->rows = [$this->makeRow([
            'nama_dokter'    => 'dr. Test',
            'status_layanan' => 'LIBUR',
            'catatan'        => 'dokter sakit',
            'sumber'         => 'GENERATE',
        ])];
        $page->saveJadwal();

        $jh = JadwalHarian::whereDate('tanggal', $tanggal)->first();
        $this->assertNotNull($jh);
        $this->assertDatabaseHas('jadwal_harian_perubahan', [
            'jadwal_harian_id' => $jh->id,
            'status_layanan'   => 'LIBUR',
        ]);
    }

    public function test_generate_buka_tidak_membuat_record_perubahan(): void
    {
        $tanggal = today()->format('Y-m-d');
        $page    = $this->makePage();
        $page->rows = [$this->makeRow([
            'nama_dokter' => 'dr. Normal',
            'sumber'      => 'GENERATE',
        ])];
        $page->saveJadwal();

        $jh = JadwalHarian::whereDate('tanggal', $tanggal)->first();
        $this->assertNotNull($jh);
        $this->assertDatabaseCount('jadwal_harian_perubahan', 0);
    }

    public function test_manual_tidak_membuat_record_perubahan(): void
    {
        $tanggal = today()->format('Y-m-d');
        $page    = $this->makePage();
        $page->rows = [$this->makeRow([
            'nama_dokter' => 'dr. Manual',
            'jam_mulai'   => '14:00',
            'sumber'      => 'MANUAL',
        ])];
        $page->saveJadwal();

        $jh = JadwalHarian::whereDate('tanggal', $tanggal)->where('sumber', 'MANUAL')->first();
        $this->assertNotNull($jh);
        $this->assertDatabaseCount('jadwal_harian_perubahan', 0);
    }

    // ── Nilai Asli (deteksi "balik ke semula" tanpa JadwalPraktek) ────────────

    public function test_perubahan_pertama_kali_capture_nilai_asli(): void
    {
        $jh = $this->makeJadwalHarian([
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '12:00',
            'status_layanan' => 'BUKA',
            'sumber'         => 'GENERATE',
        ]);

        $page = $this->makePage();
        $page->rows = [$this->makeRow([
            'id'             => $jh->id,
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '12:00',
            'status_layanan' => 'LIBUR',
            'catatan'        => 'dokter cuti',
            'sumber'         => 'GENERATE',
        ])];
        $page->saveJadwal();

        $perubahan = JadwalHarianPerubahan::first();
        $this->assertNotNull($perubahan);
        $this->assertEquals('LIBUR', $perubahan->status_layanan);
        $this->assertEquals('08:00', $perubahan->jam_mulai_asli->format('H:i'));
        $this->assertEquals('12:00', $perubahan->jam_selesai_asli->format('H:i'));
        $this->assertEquals('BUKA', $perubahan->status_layanan_asli);
    }

    public function test_nilai_asli_tidak_tertimpa_pada_perubahan_berikutnya(): void
    {
        $jh = $this->makeJadwalHarian([
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '12:00',
            'status_layanan' => 'BUKA',
            'sumber'         => 'GENERATE',
        ]);

        // Perubahan pertama: BUKA → LIBUR (asli ter-capture: 08:00–12:00, BUKA)
        $page = $this->makePage();
        $page->rows = [$this->makeRow([
            'id'             => $jh->id,
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '12:00',
            'status_layanan' => 'LIBUR',
            'sumber'         => 'GENERATE',
        ])];
        $page->saveJadwal();

        $jhSetelahPertama = JadwalHarian::whereDate('tanggal', today()->format('Y-m-d'))->first();

        // Perubahan kedua: ubah jam (masih LIBUR) pada baris yang sama
        $page2 = $this->makePage();
        $page2->rows = [$this->makeRow([
            'id'             => $jhSetelahPertama->id,
            'jam_mulai'      => '10:00',
            'jam_selesai'    => '12:00',
            'status_layanan' => 'LIBUR',
            'catatan'        => 'diundur',
            'sumber'         => 'GENERATE',
        ])];
        $page2->saveJadwal();

        $perubahanAkhir = JadwalHarianPerubahan::first();
        $this->assertEquals('10:00', $perubahanAkhir->jam_mulai->format('H:i'));
        $this->assertEquals('08:00', $perubahanAkhir->jam_mulai_asli->format('H:i'));
        $this->assertEquals('12:00', $perubahanAkhir->jam_selesai_asli->format('H:i'));
        $this->assertEquals('BUKA', $perubahanAkhir->status_layanan_asli);
    }

    public function test_balik_ke_semula_menghapus_record_perubahan(): void
    {
        $jh = $this->makeJadwalHarian([
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '12:00',
            'status_layanan' => 'BUKA',
            'sumber'         => 'GENERATE',
        ]);

        // Ubah jadi LIBUR — record perubahan tercipta
        $page = $this->makePage();
        $page->rows = [$this->makeRow([
            'id'             => $jh->id,
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '12:00',
            'status_layanan' => 'LIBUR',
            'sumber'         => 'GENERATE',
        ])];
        $page->saveJadwal();

        $this->assertDatabaseCount('jadwal_harian_perubahan', 1);
        $jhLibur = JadwalHarian::whereDate('tanggal', today()->format('Y-m-d'))->first();

        // Kembalikan persis ke kondisi asli (BUKA, jam sama)
        $page2 = $this->makePage();
        $page2->rows = [$this->makeRow([
            'id'             => $jhLibur->id,
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '12:00',
            'status_layanan' => 'BUKA',
            'sumber'         => 'GENERATE',
        ])];
        $page2->saveJadwal();

        $this->assertDatabaseCount('jadwal_harian_perubahan', 0);
        $this->assertDatabaseHas('jadwal_harian', [
            'poliklinik_id'  => $this->poli->id,
            'status_layanan' => 'BUKA',
            'sumber'         => 'GENERATE',
        ]);
    }

    public function test_baris_baru_dari_template_yang_jadi_libur_capture_asli_dari_jam_saat_ini(): void
    {
        $page = $this->makePage();
        $page->rows = [$this->makeRow([
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '11:00',
            'status_layanan' => 'LIBUR',
            'sumber'         => 'GENERATE', // simulasi baris hasil "muat dari jadwal mingguan"
        ])];
        $page->saveJadwal();

        $perubahan = JadwalHarianPerubahan::first();
        $this->assertNotNull($perubahan);
        $this->assertEquals('08:00', $perubahan->jam_mulai_asli->format('H:i'));
        $this->assertEquals('11:00', $perubahan->jam_selesai_asli->format('H:i'));
        $this->assertEquals('BUKA', $perubahan->status_layanan_asli);
    }

    // ── muatDariJadwalMingguan ────────────────────────────────────────────────

    public function test_muat_dari_jadwal_mingguan_isi_rows(): void
    {
        $hari = ['MINGGU','SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU'][today()->dayOfWeek];

        JadwalPraktek::create([
            'poliklinik_id'     => $this->poli->id,
            'hari'              => $hari,
            'nama_dokter'       => 'dr. Mingguan',
            'waktu_mulai'       => '08:00',
            'waktu_selesai'     => '12:00',
            'sesuai_perjanjian' => false,
            'is_executive'      => false,
        ]);

        $page = $this->makePage();
        $page->muatDariJadwalMingguan();

        $this->assertNotEmpty($page->rows);
        $first = array_values($page->rows)[0];
        $this->assertEquals('dr. Mingguan', $first['nama_dokter']);
        $this->assertEquals('BUKA', $first['status_layanan']);
        $this->assertEquals('GENERATE', $first['sumber']);
    }

    public function test_muat_dari_jadwal_mingguan_kosong_jika_tidak_ada(): void
    {
        $page = $this->makePage();
        $page->muatDariJadwalMingguan();
        $this->assertEmpty($page->rows);
    }

    // ── Navigasi Tanggal ──────────────────────────────────────────────────────

    public function test_prev_day_mundur_satu_hari(): void
    {
        $page = $this->makePage();
        $page->prevDay();
        $this->assertEquals(today()->subDay()->format('Y-m-d'), $page->activeTanggal);
    }

    public function test_next_day_maju_satu_hari(): void
    {
        $page = $this->makePage();
        $page->nextDay();
        $this->assertEquals(today()->addDay()->format('Y-m-d'), $page->activeTanggal);
    }

    public function test_navigasi_cache_baris_sebelumnya(): void
    {
        $page = $this->makePage();
        $page->rows = [['nama_dokter' => 'cached']];
        $tanggal    = $page->activeTanggal;

        $page->prevDay();
        $this->assertEmpty($page->rows);

        $this->assertEquals([['nama_dokter' => 'cached']], $page->rowsCache[$tanggal]);
    }

    // ── sumber & scope ────────────────────────────────────────────────────────

    public function test_jadwal_harian_tersimpan_dengan_sumber_yang_benar(): void
    {
        $page = $this->makePage();
        $page->rows = [$this->makeRow(['sumber' => 'MANUAL'])];
        $page->saveJadwal();

        $this->assertDatabaseHas('jadwal_harian', ['poliklinik_id' => $this->poli->id, 'sumber' => 'MANUAL']);
    }

    public function test_get_hari_dari_tanggal_senin(): void
    {
        $page = $this->makePage();
        $senin = today()->startOfWeek()->format('Y-m-d');
        $page->activeTanggal = $senin;

        $hari = (new \ReflectionMethod($page, 'getHariDariTanggal'))->invoke($page);
        $this->assertEquals('SENIN', $hari);
    }
}
