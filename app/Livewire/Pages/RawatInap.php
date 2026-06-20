<?php

namespace App\Livewire\Pages;

use App\Models\Gedung;
use App\Models\KelasRawatInap;
use App\Models\RawatInap as RawatInapModel;
use App\Models\RumahSakit;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RawatInap extends Component
{
    #[Locked]
    public int $rumah_sakit_id;

    public ?int $kelasFilter = null;

    public function boot(): void
    {
        // Re-bind currentRumahSakit ke container agar tersedia bagi komponen lain
        // yang berjalan bersamaan dalam satu Livewire AJAX request, dan supaya
        // tidak error BindingResolutionException saat filter kelas dipilih
        // (request itu lewat /livewire/update, tidak lewat RumahSakitMiddleware).
        if (! empty($this->rumah_sakit_id) && ! app()->bound('currentRumahSakit')) {
            $rs = RumahSakit::find($this->rumah_sakit_id);
            if ($rs) {
                app()->instance('currentRumahSakit', $rs);
            }
        }
    }

    public function mount(): void
    {
        $rs = current_rumahsakit();
        $this->rumah_sakit_id = $rs->id;

        $fullTitle = 'Rawat Inap - ' . $rs->nama;
        $desc      = 'Informasi kelas rawat inap dan fasilitas kamar di ' . $rs->nama . '.';
        SEOMeta::setTitle($fullTitle);
        SEOMeta::setDescription($desc);
        OpenGraph::setTitle($fullTitle);
        OpenGraph::setDescription($desc);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('site_name', $rs->nama);
    }

    public function render()
    {
        $gedungs = Gedung::query()
            ->where('rumah_sakit_id', $this->rumah_sakit_id)
            ->orderBy('sort_order')
            ->with(['rawatInap' => function ($query) {
                $query->orderBy('sort_order')
                    ->when($this->kelasFilter, fn ($q) => $q->where('kelas_rawat_inap_id', $this->kelasFilter))
                    ->with(['fasilitasRawatInap', 'gambar' => fn ($q) => $q->where('aktif', true)->orderBy('sort_order')])
                    ->where('aktif', true);
            }])
            ->get();

        $hasGedung = $gedungs->count() > 0;

        $rawatInap = collect();

        if (! $hasGedung) {
            $rawatInap = RawatInapModel::query()
                ->where('rumah_sakit_id', $this->rumah_sakit_id)
                ->whereNull('gedung_id')
                ->where('aktif', true)
                ->when($this->kelasFilter, fn ($q) => $q->where('kelas_rawat_inap_id', $this->kelasFilter))
                ->orderBy('sort_order')
                ->with(['fasilitasRawatInap', 'gambar' => fn ($q) => $q->where('aktif', true)->orderBy('sort_order')])
                ->get();
        }

        $totalRooms = $hasGedung
            ? $gedungs->sum(fn ($g) => $g->rawatInap->count())
            : $rawatInap->count();

        return view('rumah_sakit.pages.rawat-inap', [
            'hasGedung'    => $hasGedung,
            'gedungs'      => $gedungs,
            'rawatInap'    => $rawatInap,
            'totalRooms'   => $totalRooms,
            'kelasOptions' => KelasRawatInap::where('rumah_sakit_id', $this->rumah_sakit_id)->orderBy('nama')->get(),
        ]);
    }
}
