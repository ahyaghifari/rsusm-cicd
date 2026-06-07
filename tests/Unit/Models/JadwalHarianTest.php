<?php

namespace Tests\Unit\Models;

use App\Enums\StatusLayanan;
use App\Models\JadwalHarian;
use App\Models\PoliKlinik;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalHarianTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('jadwal_harian', (new JadwalHarian)->getTable());
    }

    public function test_poliklinik_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new JadwalHarian)->poliklinik());
    }

    public function test_dokter_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new JadwalHarian)->dokter());
    }

    public function test_tanggal_cast_to_date(): void
    {
        $jadwal = JadwalHarian::factory()->create(['tanggal' => '2026-05-28']);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $jadwal->tanggal);
        $this->assertEquals('2026-05-28', $jadwal->tanggal->format('Y-m-d'));
    }

    public function test_status_layanan_cast_to_enum(): void
    {
        $jadwal = JadwalHarian::factory()->create(['status_layanan' => 'BUKA']);
        $this->assertInstanceOf(StatusLayanan::class, $jadwal->status_layanan);
    }

    public function test_jam_mulai_cast_to_carbon(): void
    {
        $jadwal = JadwalHarian::factory()->create(['jam_mulai' => '09:00']);
        $this->assertEquals('09:00', $jadwal->jam_mulai->format('H:i'));
    }

    public function test_jam_selesai_nullable(): void
    {
        $jadwal = JadwalHarian::factory()->create(['jam_selesai' => null]);
        $this->assertNull($jadwal->jam_selesai);
    }

    public function test_scoped_to_rumah_sakit_via_poliklinik(): void
    {
        $poli1 = PoliKlinik::factory()->create();
        $poli2 = PoliKlinik::factory()->create();

        $rsId = $poli1->rumah_sakit_id;

        JadwalHarian::factory()->count(3)->create([
            'poliklinik_id' => $poli1->id,
            'tanggal'       => today(),
        ]);
        JadwalHarian::factory()->count(2)->create([
            'poliklinik_id' => $poli2->id,
            'tanggal'       => today(),
        ]);

        $count = JadwalHarian::whereHas(
            'poliklinik',
            fn ($q) => $q->where('rumah_sakit_id', $rsId)
        )->whereDate('tanggal', today())->count();

        $this->assertEquals(3, $count);
    }
}
