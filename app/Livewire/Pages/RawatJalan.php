<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\PoliKlinik;
use App\Models\UnitLayanan;

class RawatJalan extends RsPortalComponent
{
    public ?int $activeUnitId = null;

    public function mount(): void
    {
        $this->seo('Rawat Jalan', 'Layanan rawat jalan dan jadwal poliklinik di ' . $this->rs->nama . '.');

        $firstUnit = UnitLayanan::where('rumah_sakit_id', $this->rs->id)
            ->where('aktif', true)
            ->first();

        if ($firstUnit) {
            $this->activeUnitId = $firstUnit->id;
        }
    }

    public function setUnit(int $id): void
    {
        $this->activeUnitId = $id;
    }

    public function render()
    {
        $units = UnitLayanan::where('rumah_sakit_id', $this->rs->id)
            ->where('aktif', true)
            ->get();

        $poliklinik = collect();

        if ($this->activeUnitId) {
            $poliklinik = PoliKlinik::where('unit_layanan_id', $this->activeUnitId)
                ->where('aktif', true)
                ->get();
        }

        $activeUnit = $units->firstWhere('id', $this->activeUnitId);

        return view('rumah_sakit.pages.rawat-jalan', [
            'units'      => $units,
            'poliklinik' => $poliklinik,
            'rsSlug'     => $this->rs->slug,
            'activeUnit' => $activeUnit,
        ]);
    }
}
