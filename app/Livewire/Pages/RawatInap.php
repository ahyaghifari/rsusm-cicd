<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\Gedung;
use App\Models\RawatInap as ModelsRawatInap;

class RawatInap extends RsPortalComponent
{
    public function mount(): void
    {
        $this->seo('Rawat Inap', 'Informasi kelas rawat inap dan fasilitas kamar di ' . $this->rs->nama . '.');
    }

    public function render()
    {
        $gedungs = Gedung::query()
            ->where('rumah_sakit_id', $this->rs->id)
            ->orderBy('sort_order')
            ->with(['rawatInap' => function ($query) {
                $query->orderBy('sort_order')
                    ->with(['fasilitasRawatInap', 'gambar' => fn ($q) => $q->where('aktif', true)->orderBy('sort_order')])
                    ->where('aktif', true);
            }])
            ->get();

        $hasGedung = $gedungs->count() > 0;

        $rawatInap = collect();

        if (! $hasGedung) {
            $rawatInap = ModelsRawatInap::query()
                ->where('rumah_sakit_id', $this->rs->id)
                ->whereNull('gedung_id')
                ->where('aktif', true)
                ->orderBy('sort_order')
                ->with(['fasilitasRawatInap', 'gambar' => fn ($q) => $q->where('aktif', true)->orderBy('sort_order')])
                ->get();
        }

        return view('rumah_sakit.pages.rawat-inap', [
            'hasGedung' => $hasGedung,
            'gedungs'   => $gedungs,
            'rawatInap' => $rawatInap,
        ]);
    }
}
