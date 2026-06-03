<?php

namespace App\Livewire\Dokter;

use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Dokter;
use App\Models\Spesialis;
use App\Models\RumahSakit;

class Find extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $spesialis = '';

    #[Locked]
    public int $rumah_sakit_id;

    protected $queryString = [
        'search' => ['except' => ''],
        'spesialis' => ['except' => ''],
    ];

    public function mount()
    {
        // 2. Ambil ID-nya saja saat pertama kali dimuat
        $rs = current_rumahsakit();
        $this->rumah_sakit_id = $rs->id;

        $desc      = 'Temukan dokter spesialis terpercaya di ' . $rs->nama . '.';
        $fullTitle = 'Dokter Kami - ' . $rs->nama;
        SEOMeta::setTitle($fullTitle);
        SEOMeta::setDescription($desc);
        OpenGraph::setTitle($fullTitle);
        OpenGraph::setDescription($desc);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('site_name', $rs->nama);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSpesialis()
    {
        $this->resetPage();
    }

    public function render()
    {
        // 3. Ambil data Rumah Sakit di dalam render() agar tidak ikut bolak-balik di-hydrate oleh Livewire
        $rumah_sakit = RumahSakit::find($this->rumah_sakit_id);

        $dokter = Dokter::query()
            ->where('rumah_sakit_id', $this->rumah_sakit_id) // Gunakan ID langsung

            // cari nama dokter
            ->when($this->search, function ($query) {
                $query->where('nama', 'like', '%' . $this->search . '%');
            })

            // filter spesialis by slug
            ->when($this->spesialis != '', function ($query) {
                $query->whereHas('spesialis', fn ($q) => $q->where('slug', $this->spesialis));
            })

            ->with('spesialis')
            ->orderBy('nama')
            ->paginate(10);

        $data_spesialis = Spesialis::where('rumah_sakit_id', $this->rumah_sakit_id) // Gunakan ID langsung
            ->whereHas('dokter')
            ->get();

        return view('rumah_sakit.dokter.find', [
            'dokter' => $dokter,
            'data_spesialis' => $data_spesialis,
            'rumahsakit' => $rumah_sakit,
        ]);
    }
}