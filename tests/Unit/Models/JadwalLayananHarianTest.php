<?php

namespace Tests\Unit\Models;

use App\Enums\StatusLayanan;
use App\Models\JadwalLayananHarian;
use App\Models\PoliKlinik;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalLayananHarianTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('jadwal_layanan_harian', (new JadwalLayananHarian)->getTable());
    }

    public function test_poliklinik_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new JadwalLayananHarian)->poliklinik());
    }

    public function test_dokter_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new JadwalLayananHarian)->dokter());
    }

    public function test_jadwal_layanan_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new JadwalLayananHarian)->jadwalLayanan());
    }

    public function test_tanggal_cast_to_date(): void
    {
        $jadwal = JadwalLayananHarian::factory()->create(['tanggal' => '2026-05-28']);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $jadwal->tanggal);
        $this->assertEquals('2026-05-28', $jadwal->tanggal->format('Y-m-d'));
    }

    public function test_status_layanan_cast_to_enum(): void
    {
        $jadwal = JadwalLayananHarian::factory()->create(['status_layanan' => 'BUKA']);
        $this->assertInstanceOf(StatusLayanan::class, $jadwal->status_layanan);
    }

    public function test_jam_mulai_cast_to_carbon(): void
    {
        $jadwal = JadwalLayananHarian::factory()->create(['jam_mulai' => '09:00']);
        $this->assertEquals('09:00', $jadwal->jam_mulai->format('H:i'));
    }

    public function test_jam_selesai_nullable(): void
    {
        $jadwal = JadwalLayananHarian::factory()->create(['jam_selesai' => null]);
        $this->assertNull($jadwal->jam_selesai);
    }

    public function test_scoped_to_rumah_sakit_via_poliklinik(): void
    {
        $poli1 = PoliKlinik::factory()->create();
        $poli2 = PoliKlinik::factory()->create();

        $rsId = $poli1->unitLayanan->rumah_sakit_id;

        JadwalLayananHarian::factory()->count(3)->create([
            'poliklinik_id' => $poli1->id,
            'tanggal'       => today(),
        ]);
        JadwalLayananHarian::factory()->count(2)->create([
            'poliklinik_id' => $poli2->id,
            'tanggal'       => today(),
        ]);

        $count = JadwalLayananHarian::whereHas(
            'poliklinik.unitLayanan',
            fn ($q) => $q->where('rumah_sakit_id', $rsId)
        )->whereDate('tanggal', today())->count();

        $this->assertEquals(3, $count);
    }
}
