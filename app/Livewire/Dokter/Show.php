<?php

namespace App\Livewire\Dokter;

use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Component;
use App\Models\Dokter;

class Show extends Component
{
    public Dokter $dokter;

    public function mount(Dokter $dokter): void
    {
        $this->dokter = $dokter;

        $rs = current_rumahsakit();
        $desc = $dokter->deskripsi
            ? \Illuminate\Support\Str::limit(strip_tags($dokter->deskripsi), 155)
            : 'Dokter ' . ($dokter->spesialis?->nama ?? '') . ' di ' . $rs->nama;

        $fullTitle = $dokter->nama . ' - ' . $rs->nama;

        SEOMeta::setTitle($fullTitle);
        SEOMeta::setDescription($desc);

        OpenGraph::setTitle($fullTitle);
        OpenGraph::setDescription($desc);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('site_name', $rs->nama);
        if ($dokter->foto) {
            OpenGraph::addImage(asset('storage/' . $dokter->foto));
        }
    }

    public function render()
    {
        return view('rumah_sakit.dokter.show', ['dokter' => $this->dokter]);
    }
}
