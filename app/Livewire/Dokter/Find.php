<?php

namespace App\Livewire\Dokter;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Dokter;
use App\Models\Spesialis;
use App\Models\RumahSakit;

class Find extends Component
{
    use WithPagination;

    public $search = '';
    public $spesialis = '';
    public ?RumahSakit $rumah_sakit = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'spesialis' => ['except' => ''],
    ];

    public function mount()
    {
        $this->rumah_sakit = current_rumahsakit();
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

        $dokter = Dokter::query()
            ->where('rumah_sakit_id', $this->rumah_sakit->id)

            // cari nama dokter
            ->when($this->search, function ($query) {
                $query->where('nama', 'like', '%' . $this->search . '%');
            })

            // filter spesialis
            ->when($this->spesialis != '', function ($query) {
                $query->where('spesialis_id', $this->spesialis);
            })

            ->with('spesialis')
            ->paginate(10);

        $data_spesialis = Spesialis::where('rumah_sakit_id', $this->rumah_sakit->id)->whereHas('dokter')->get();

        return view('rumah_sakit.dokter.find', [
            'dokter' => $dokter,
            'data_spesialis' => $data_spesialis,
            'rumahsakit' => $this->rumah_sakit
        ]);
    }
}