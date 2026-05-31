<?php

namespace App\Livewire\Pages;

use App\Enums\Hari;
use App\Livewire\RsPortalComponent;
use App\Models\JadwalLayanan;
use App\Models\UnitLayanan;

class JadwalPoliklinik extends RsPortalComponent
{
    public string $activeHari;
    public string $unitLayananId = '';

    private static array $hariMap = [
        0 => 'MINGGU', 1 => 'SENIN', 2 => 'SELASA', 3 => 'RABU',
        4 => 'KAMIS',  5 => 'JUMAT', 6 => 'SABTU',
    ];

    public function mount(): void
    {
        $this->activeHari = self::$hariMap[now()->dayOfWeek];
        $this->seo('Jadwal Poliklinik', 'Jadwal layanan poliklinik di ' . $this->rs->nama . '.');
    }

    public function setHari(string $hari): void
    {
        $this->activeHari = $hari;
    }

    public function render()
    {
        $units = UnitLayanan::where('rumah_sakit_id', $this->rs->id)
            ->where('aktif', true)
            ->get();

        $query = JadwalLayanan::with('poliklinik.unitLayanan')
            ->where('hari', $this->activeHari)
            ->where('status_layanan', 'BUKA')
            ->whereHas('poliklinik', fn ($q) => $q->where('aktif', true)
                ->whereHas('unitLayanan', fn ($q2) => $q2->where('rumah_sakit_id', $this->rs->id)
                    ->when($this->unitLayananId, fn ($q3) => $q3->where('id', $this->unitLayananId))
                )
            )
            ->orderBy('jam_mulai');

        $jadwalPerPoli = $query->get()->groupBy('poliklinik_id');

        $hariIni = self::$hariMap[now()->dayOfWeek];

        return view('rumah_sakit.pages.jadwal-poliklinik', [
            'jadwalPerPoli' => $jadwalPerPoli,
            'units'         => $units,
            'hariList'      => array_values(self::$hariMap),
            'hariIni'       => $hariIni,
        ]);
    }
}
