<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\Artikel as ArtikelModel;
use Artesaos\SEOTools\Facades\OpenGraph;
use Livewire\Attributes\Locked;

class ArtikelDetail extends RsPortalComponent
{
    #[Locked]
    public ArtikelModel $artikel;

    public function mount(ArtikelModel $artikel): void
    {
        abort_if(
            $artikel->rumah_sakit_id !== $this->rs->id || ! $artikel->aktif,
            404
        );

        $this->artikel = $artikel;

        $this->seo($artikel->judul, $artikel->ringkasan ?? '');

        if ($artikel->gambar) {
            OpenGraph::addImage(asset('storage/' . $artikel->gambar));
        }
    }

    public function render()
    {
        $artikelLainnya = ArtikelModel::where('rumah_sakit_id', $this->rs->id)
            ->aktif()
            ->where('id', '!=', $this->artikel->id)
            ->orderByDesc('tanggal_publish')
            ->limit(3)
            ->get();

        return view('rumah_sakit.pages.artikel-detail', [
            'artikelLainnya' => $artikelLainnya,
        ]);
    }
}
