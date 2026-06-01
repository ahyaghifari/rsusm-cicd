<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\JadwalPraktek as JadwalPraktekModel;
use App\Models\PoliKlinik;

class JadwalPraktek extends RsPortalComponent
{
    public string $viewMode    = 'hari'; // 'hari' | 'poli'
    public string $activeHari;
    public string $poliklinikId = '';

    private static array $hariList = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU'];

    private static array $hariMap = [
        0 => 'MINGGU', 1 => 'SENIN', 2 => 'SELASA', 3 => 'RABU',
        4 => 'KAMIS',  5 => 'JUMAT', 6 => 'SABTU',
    ];

    public function mount(): void
    {
        $this->activeHari = self::$hariMap[now()->dayOfWeek];
        $this->seo('Jadwal Praktek', 'Jadwal praktek dokter di poliklinik ' . $this->rs->nama . '.');
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
        $rs = $this->rs;

        $poliklinikList = PoliKlinik::whereHas('unitLayanan', fn ($q) =>
                $q->where('rumah_sakit_id', $rs->id)
            )
            ->where('aktif', true)
            ->orderBy('nama')
            ->get();

        // ── Mode Per Hari ──────────────────────────────────────────────────────
        $jadwalPerPoli = collect();

        if ($this->viewMode === 'hari') {
            $jadwalPerPoli = JadwalPraktekModel::with(['poliklinik.unitLayanan', 'dokter'])
                ->where('hari', $this->activeHari)
                ->whereHas('poliklinik', fn ($q) =>
                    $q->where('aktif', true)
                      ->whereHas('unitLayanan', fn ($q2) =>
                          $q2->where('rumah_sakit_id', $rs->id)
                      )
                )
                ->when($this->poliklinikId, fn ($q) => $q->where('poliklinik_id', $this->poliklinikId))
                ->orderBy('waktu_mulai')
                ->get()
                ->groupBy('poliklinik_id');
        }

        // ── Mode Per Poli ──────────────────────────────────────────────────────
        $jadwalPerHari = collect();

        if ($this->viewMode === 'poli' && $this->poliklinikId) {
            $rawJadwal = JadwalPraktekModel::with('dokter')
                ->where('poliklinik_id', $this->poliklinikId)
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
            'jadwalPerPoli'  => $jadwalPerPoli,
            'jadwalPerHari'  => $jadwalPerHari,
            'hariList'       => self::$hariList,
            'hariIni'        => self::$hariMap[now()->dayOfWeek],
            'poliklinikList' => $poliklinikList,
            'selectedPoli'   => $selectedPoli,
        ]);
    }
}
