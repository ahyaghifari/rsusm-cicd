<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\FasilitasPendukung as ModelsFasilitasPendukung;

class FasilitasPendukung extends RsPortalComponent
{
    public function mount(): void
    {
        $this->seo('Fasilitas Pendukung', 'Fasilitas pendukung lengkap di ' . $this->rs->nama . '.');
    }

    public function render()
    {
        $fasilitas = ModelsFasilitasPendukung::where('rumah_sakit_id', $this->rs->id)
            ->where('aktif', true)
            ->get();

        return view('rumah_sakit.pages.fasilitas-pendukung', ['fasilitas' => $fasilitas]);
    }
}
