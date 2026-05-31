<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\Promo as PromoModel;
use Artesaos\SEOTools\Facades\OpenGraph;

class PromoDetail extends RsPortalComponent
{
    public PromoModel $promo;

    public function mount(PromoModel $promo): void
    {
        abort_if(
            $promo->rumah_sakit_id !== $this->rs->id || ! $promo->aktif,
            404
        );

        $this->promo = $promo;

        $this->seo($promo->judul, $promo->deskripsi ?? '');

        if ($promo->gambar) {
            OpenGraph::addImage(asset('storage/' . $promo->gambar));
        }
    }

    public function render()
    {
        $promoLainnya = PromoModel::where('rumah_sakit_id', $this->rs->id)
            ->aktif()
            ->where('id', '!=', $this->promo->id)
            ->orderByDesc('popup')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        return view('rumah_sakit.pages.promo-detail', [
            'promoLainnya' => $promoLainnya,
        ]);
    }
}
