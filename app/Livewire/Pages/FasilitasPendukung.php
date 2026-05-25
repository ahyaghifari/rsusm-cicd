<?php

namespace App\Livewire\Pages;

use App\Models\FasilitasPendukung as ModelsFasilitasPendukung;
use App\Models\RumahSakit;
use Livewire\Component;

class FasilitasPendukung extends Component
{
    public RumahSakit $rs;

    public function mount(){
        $this->rs = current_rumahsakit();
    }

    public function render()
    {
        $fasilitas = ModelsFasilitasPendukung::where('rumah_sakit_id', $this->rs->id)->get();
        return view('rumah_sakit.pages.fasilitas-pendukung', ['fasilitas' => $fasilitas]);
    }
}
