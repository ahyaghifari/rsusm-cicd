<?php

namespace App\Livewire\Pages;

use App\Models\JadwalPraktek as JadwalPraktekModel;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Attributes\Locked;
use Livewire\Component;

class JadwalPraktek extends Component
{
    #[Locked]
    public int $rumah_sakit_id;

    public string $viewMode    = 'hari'; // 'hari' | 'poli'
    public string $activeHari;
    public string $poliklinikId = '';

    private static array $hariList = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU'];

    private static array $hariMap = [
        0 => 'MINGGU', 1 => 'SENIN', 2 => 'SELASA', 3 => 'RABU',
        4 => 'KAMIS',  5 => 'JUMAT', 6 => 'SABTU',
    ];

    public function boot(): void
    {
        // RumahSakitMiddleware tidak jalan di /livewire/update (dipakai saat ganti filter
        // hari/poli), jadi current_rumahsakit() akan error BindingResolutionException kalau
        // dipanggil komponen lain (mis. GlobalSearch) dalam request yang sama. Bind manual di sini.
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
        $this->activeHari     = self::$hariMap[now()->dayOfWeek];

        $fullTitle = 'Jadwal Praktek - ' . $rs->nama;
        $desc      = 'Jadwal praktek dokter di poliklinik ' . $rs->nama . '.';
        SEOMeta::setTitle($fullTitle);
        SEOMeta::setDescription($desc);
        OpenGraph::setTitle($fullTitle);
        OpenGraph::setDescription($desc);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('site_name', $rs->nama);
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode     = $mode;
        $this->poliklinikId = '';
    }

    public function setHari(string $hari): void
    {
        $this->activeHari = $hari;
    }

    public function render()
    {
        $rs = RumahSakit::find($this->rumah_sakit_id);

        $poliklinikList = PoliKlinik::where('rumah_sakit_id', $rs->id)
            ->where('aktif', true)
            ->orderBy('nama')
            ->get();

        // ── Mode Per Hari ──────────────────────────────────────────────────────
        $jadwalPerPoli = collect();

        if ($this->viewMode === 'hari') {
            $jadwalPerPoli = JadwalPraktekModel::with(['poliklinik', 'dokter'])
                ->where('hari', $this->activeHari)
                ->whereHas('poliklinik', fn ($q) =>
                    $q->where('aktif', true)->where('rumah_sakit_id', $rs->id)
                )
                ->when($this->poliklinikId, fn ($q) => $q->where('poliklinik_id', $this->poliklinikId))
                ->orderBy('is_executive')
                ->orderBy('waktu_mulai')
                ->get()
                ->groupBy('poliklinik_id');
        }

        // ── Mode Per Poli ──────────────────────────────────────────────────────
        $jadwalPerHari = collect();

        if ($this->viewMode === 'poli' && $this->poliklinikId) {
            $rawJadwal = JadwalPraktekModel::with('dokter')
                ->where('poliklinik_id', $this->poliklinikId)
                ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rs->id))
                ->orderBy('is_executive')
                ->orderBy('waktu_mulai')
                ->get()
                ->groupBy(fn ($j) => $j->hari->value);

            foreach (self::$hariList as $hari) {
                $jadwalPerHari[$hari] = $rawJadwal->get($hari, collect());
            }
        }

        $selectedPoli = $this->poliklinikId
            ? $poliklinikList->firstWhere('id', $this->poliklinikId)
            : null;

        return view('rumah_sakit.pages.jadwal-praktek', [
            'rs'             => $rs,
            'jadwalPerPoli'  => $jadwalPerPoli,
            'jadwalPerHari'  => $jadwalPerHari,
            'hariList'       => self::$hariList,
            'hariIni'        => self::$hariMap[now()->dayOfWeek],
            'poliklinikList' => $poliklinikList,
            'selectedPoli'   => $selectedPoli,
        ]);
    }
}
