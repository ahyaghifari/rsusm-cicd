<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\Promo as PromoModel;

class Promo extends RsPortalComponent
{
    public function mount(): void
    {
        $this->seo('Promo', 'Promo dan penawaran spesial dari ' . $this->rs->nama . '.');
    }

    public function render()
    {
        $promos = PromoModel::where('rumah_sakit_id', $this->rs->id)
            ->aktif()
            ->orderByDesc('popup')
            ->orderByDesc('created_at')
            ->get();

        return view('rumah_sakit.pages.promo', [
            'promos' => $promos,
        ]);
    }
}
