<?php

namespace Tests\Unit\Models;

use App\Enums\Hari;
use App\Enums\StatusLayanan;
use App\Models\JadwalLayanan;
use App\Models\JadwalLayananHarian;
use App\Models\PoliKlinik;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalLayananTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('jadwal_layanan', (new JadwalLayanan)->getTable());
    }

    public function test_poliklinik_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new JadwalLayanan)->poliklinik());
    }

    public function test_dokter_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new JadwalLayanan)->dokter());
    }

    public function test_jadwal_harian_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new JadwalLayanan)->jadwalHarian());
    }

    public function test_hari_cast_to_enum(): void
    {
        $jadwal = JadwalLayanan::factory()->create(['hari' => 'SENIN']);
        $this->assertInstanceOf(Hari::class, $jadwal->hari);
        $this->assertEquals(Hari::SENIN, $jadwal->hari);
    }

    public function test_status_layanan_cast_to_enum(): void
    {
        $jadwal = JadwalLayanan::factory()->create(['status_layanan' => 'BUKA']);
        $this->assertInstanceOf(StatusLayanan::class, $jadwal->status_layanan);
        $this->assertEquals(StatusLayanan::BUKA, $jadwal->status_layanan);
    }

    public function test_jam_mulai_cast_to_carbon(): void
    {
        $jadwal = JadwalLayanan::factory()->create(['jam_mulai' => '08:30']);
        $this->assertNotNull($jadwal->jam_mulai);
        $this->assertEquals('08:30', $jadwal->jam_mulai->format('H:i'));
    }

    public function test_jam_selesai_nullable(): void
    {
        $jadwal = JadwalLayanan::factory()->create(['jam_selesai' => null]);
        $this->assertNull($jadwal->jam_selesai);
    }

    public function test_belongs_to_poliklinik(): void
    {
        $poli   = PoliKlinik::factory()->create();
        $jadwal = JadwalLayanan::factory()->create(['poliklinik_id' => $poli->id]);

        $this->assertEquals($poli->id, $jadwal->poliklinik->id);
    }

    public function test_libur_state(): void
    {
        $jadwal = JadwalLayanan::factory()->libur()->create();
        $this->assertEquals(StatusLayanan::LIBUR, $jadwal->status_layanan);
    }
}
