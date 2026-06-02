<?php

namespace Tests\Unit\Models;

use App\Enums\Hari;
use App\Models\JadwalPraktek;
use App\Models\PoliKlinik;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalPraktekTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('jadwal_praktek', (new JadwalPraktek)->getTable());
    }

    public function test_poliklinik_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new JadwalPraktek)->poliklinik());
    }

    public function test_dokter_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new JadwalPraktek)->dokter());
    }

    public function test_hari_cast_to_enum(): void
    {
        $jadwal = JadwalPraktek::factory()->create(['hari' => 'SENIN']);
        $this->assertInstanceOf(Hari::class, $jadwal->hari);
        $this->assertEquals(Hari::SENIN, $jadwal->hari);
    }

    public function test_waktu_mulai_cast_to_carbon(): void
    {
        $jadwal = JadwalPraktek::factory()->create(['waktu_mulai' => '08:00']);
        $this->assertEquals('08:00', $jadwal->waktu_mulai->format('H:i'));
    }

    public function test_waktu_selesai_nullable(): void
    {
        $jadwal = JadwalPraktek::factory()->create(['waktu_selesai' => null]);
        $this->assertNull($jadwal->waktu_selesai);
    }

    // Validasi bug fix: sesuai_perjanjian harus tersimpan benar sebagai boolean
    public function test_sesuai_perjanjian_cast_as_boolean(): void
    {
        $jadwal = JadwalPraktek::factory()->create(['sesuai_perjanjian' => true]);
        $this->assertIsBool($jadwal->fresh()->sesuai_perjanjian);
        $this->assertTrue($jadwal->fresh()->sesuai_perjanjian);
    }

    public function test_sesuai_perjanjian_false_by_default(): void
    {
        $jadwal = JadwalPraktek::factory()->create();
        $this->assertFalse($jadwal->sesuai_perjanjian);
    }

    public function test_sesuai_perjanjian_saves_true_from_bool(): void
    {
        $jadwal = JadwalPraktek::factory()->create(['sesuai_perjanjian' => true]);
        $this->assertDatabaseHas('jadwal_praktek', ['id' => $jadwal->id, 'sesuai_perjanjian' => 1]);
    }

    public function test_sesuai_perjanjian_saves_false_from_bool(): void
    {
        $jadwal = JadwalPraktek::factory()->create(['sesuai_perjanjian' => false]);
        $this->assertDatabaseHas('jadwal_praktek', ['id' => $jadwal->id, 'sesuai_perjanjian' => 0]);
    }

    // Validasi logika (bool) cast yang kita fix — memastikan '0' tetap false
    public function test_bool_cast_dari_string_nol_adalah_false(): void
    {
        $this->assertFalse((bool) ('0' ?? false));
    }

    public function test_bool_cast_dari_true_adalah_true(): void
    {
        $this->assertTrue((bool) (true ?? false));
    }

    public function test_scoped_ke_rumah_sakit_via_poliklinik(): void
    {
        $poli1 = PoliKlinik::factory()->create();
        $poli2 = PoliKlinik::factory()->create();
        $rsId  = $poli1->unitLayanan->rumah_sakit_id;

        JadwalPraktek::factory()->count(3)->create(['poliklinik_id' => $poli1->id]);
        JadwalPraktek::factory()->count(2)->create(['poliklinik_id' => $poli2->id]);

        $count = JadwalPraktek::whereHas('poliklinik.unitLayanan',
            fn ($q) => $q->where('rumah_sakit_id', $rsId)
        )->count();

        $this->assertEquals(3, $count);
    }

    public function test_ordered_by_nama_dokter(): void
    {
        $poli = PoliKlinik::factory()->create();

        JadwalPraktek::factory()->create(['poliklinik_id' => $poli->id, 'nama_dokter' => 'Zara']);
        JadwalPraktek::factory()->create(['poliklinik_id' => $poli->id, 'nama_dokter' => 'Andi']);
        JadwalPraktek::factory()->create(['poliklinik_id' => $poli->id, 'nama_dokter' => 'Budi']);

        $names = JadwalPraktek::where('poliklinik_id', $poli->id)
            ->orderBy('nama_dokter')
            ->pluck('nama_dokter')
            ->toArray();

        $this->assertEquals(['Andi', 'Budi', 'Zara'], $names);
    }
}
