<?php

namespace App\Livewire\Pages;

use App\Models\PoliKlinik;
use App\Models\UnitLayanan;
use App\Models\RumahSakit;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RawatJalan extends Component
{
    #[Locked]
    public ?RumahSakit $rs = null;
    public ?int $activeUnitId = null;

    public function mount(): void
    {
        $this->rs = current_rumahsakit();

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

        return view('rumah_sakit.pages.rawat-jalan', [
            'units'      => $units,
            'poliklinik' => $poliklinik,
        ]);
    }
}
