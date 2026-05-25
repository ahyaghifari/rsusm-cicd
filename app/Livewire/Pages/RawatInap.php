<?php

namespace App\Livewire\Pages;

use App\Models\Gedung;
use App\Models\RawatInap as ModelsRawatInap;
use App\Models\RumahSakit;
use Livewire\Component;

class RawatInap extends Component
{
    public ?RumahSakit $rumah_sakit = null;

    public function mount()
    {
        $this->rumah_sakit = current_rumahsakit();
    }

    public function render()
    {   
        $gedungs = Gedung::query()
        ->where('rumah_sakit_id', $this->rumah_sakit->id)
        ->with(['rawatInap' => function ($query) {
            $query->orderBy('sort_order')->with('fasilitasRawatInap')
                ->where('aktif', true);
        }])
        ->get();

        // cek apakah rumah sakit punya gedung
        $hasGedung = $gedungs->count() > 0;

        $rawatInap = collect();

        if (! $hasGedung) {
            $rawatInap = ModelsRawatInap::query()
                ->where('rumah_sakit_id', $this->rumah_sakit->id)
                ->whereNull('gedung_id')
                ->where('aktif', true)
                ->orderBy('sort_order')
                ->get();
        }

        return view('rumah_sakit.pages.rawat-inap', [
            'hasGedung' => $hasGedung,
            'gedungs' => $gedungs,
            'rawatInap' => $rawatInap,
        ]);
    }
}
