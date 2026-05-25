<?php

namespace App\Livewire\Dokter;

use Livewire\Component;
use App\Models\Dokter;

class Show extends Component
{
    public Dokter $dokter;

    public function mount(Dokter $dokter)
    {
        $this->dokter = $dokter;
    }

    public function render()
    {
        return view('rumah_sakit.dokter.show', ['dokter' => $this->dokter]);
    }
}
