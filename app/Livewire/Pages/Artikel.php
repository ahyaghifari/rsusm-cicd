<?php

namespace App\Livewire\Pages;

use App\Models\Artikel as ArtikelModel;
use App\Models\KategoriArtikel;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Artikel extends Component
{
    use WithPagination;

    #[Locked]
    public int $rumah_sakit_id;

    public string $search   = '';
    public string $kategori = '';

    protected $queryString = [
        'search'   => ['except' => '', 'as' => 'q'],
        'kategori' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingKategori(): void
    {
        $this->resetPage();
    }

    public function boot(): void
    {
        // Re-bind currentRumahSakit ke container agar tersedia bagi komponen lain
        // (mis. GlobalSearch) yang berjalan bersamaan dalam satu Livewire AJAX request.
        // RumahSakitMiddleware tidak jalan di /livewire/update, jadi kita bind manual.
        if (! empty($this->rumah_sakit_id) && ! app()->bound('currentRumahSakit')) {
            $rs = \App\Models\RumahSakit::find($this->rumah_sakit_id);
            if ($rs) {
                app()->instance('currentRumahSakit', $rs);
            }
        }
    }

    public function mount(): void
    {
        $rs = current_rumahsakit();
        $this->rumah_sakit_id = $rs->id;

        $fullTitle = 'Artikel & Berita - ' . $rs->nama;
        $desc      = 'Artikel dan berita terbaru dari ' . $rs->nama . '.';
        SEOMeta::setTitle($fullTitle);
        SEOMeta::setDescription($desc);
        OpenGraph::setTitle($fullTitle);
        OpenGraph::setDescription($desc);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('site_name', $rs->nama);
    }

    public function render()
    {
        $data_kategori = KategoriArtikel::where('rumah_sakit_id', $this->rumah_sakit_id)
            ->orderBy('nama')
            ->get();

        $hasFilter = $this->search !== '' || $this->kategori !== '';

        $artikelUnggulan = null;
        if (! $hasFilter) {
            $artikelUnggulan = ArtikelModel::with('kategori')
                ->where('rumah_sakit_id', $this->rumah_sakit_id)
                ->aktif()
                ->where('unggulan', true)
                ->orderByDesc('tanggal_publish')
                ->first();
        }

        $artikelList = ArtikelModel::with('kategori')
            ->where('rumah_sakit_id', $this->rumah_sakit_id)
            ->aktif()
            ->when($artikelUnggulan, fn ($q) => $q->where('id', '!=', $artikelUnggulan->id))
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('judul', 'like', '%' . $this->search . '%')
                   ->orWhere('ringkasan', 'like', '%' . $this->search . '%');
            }))
            ->when($this->kategori, fn ($q) => $q->whereHas('kategori', fn ($q2) => $q2->where('slug', $this->kategori)))
            ->orderByDesc('tanggal_publish')
            ->paginate(9);

        return view('rumah_sakit.pages.artikel', [
            'artikelUnggulan' => $artikelUnggulan,
            'artikelList'     => $artikelList,
            'data_kategori'   => $data_kategori,
        ]);
    }
}

