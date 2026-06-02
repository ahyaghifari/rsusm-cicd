<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\PenunjangMedis as ModelsPenunjangMedis;

class PenunjangMedis extends RsPortalComponent
{
    public function mount(): void
    {
        $this->seo(
            'Penunjang Medis',
            'Layanan penunjang medis di ' . $this->rs->nama . ', meliputi laboratorium, radiologi, dan farmasi.'
        );
    }

    public function render()
    {
        $penunjang_medis = ModelsPenunjangMedis::where('rumah_sakit_id', $this->rs->id)->get();

        return view('rumah_sakit.pages.penunjang-medis', ['data' => $penunjang_medis]);
    }
}
