<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\JadwalPraktek as JadwalPraktekModel;
use App\Models\Spesialis;

class JadwalPraktek extends RsPortalComponent
{
    public string $activeHari;
    public string $spesialisId = '';

    private const HARI_MAP = [
        1 => 'SENIN',
        2 => 'SELASA',
        3 => 'RABU',
        4 => 'KAMIS',
        5 => 'JUMAT',
        6 => 'SABTU',
        7 => 'MINGGU',
    ];

    public function mount(): void
    {
        $this->activeHari = self::HARI_MAP[now()->dayOfWeekIso];
        $this->seo('Jadwal Praktek Dokter', 'Jadwal praktek dokter spesialis di ' . $this->rs->nama . '.');
    }

    public function setHari(string $hari): void
    {
        $this->activeHari = $hari;
    }

    public function render()
    {
        $rs = $this->rs;

        $jadwal = JadwalPraktekModel::where('libur', false)
            ->where('hari', $this->activeHari)
            ->whereHas('dokter', function ($q) use ($rs) {
                $q->where('rumah_sakit_id', $rs->id)->where('aktif', true);
                if ($this->spesialisId) {
                    $q->where('spesialis_id', $this->spesialisId);
                }
            })
            ->with(['dokter.spesialis'])
            ->orderBy('waktu_mulai')
            ->get();

        $spesialisList = Spesialis::whereHas('dokter', function ($q) use ($rs) {
                $q->where('rumah_sakit_id', $rs->id)
                  ->where('aktif', true)
                  ->whereHas('jadwalPraktek', function ($jq) {
                      $jq->where('hari', $this->activeHari)->where('libur', false);
                  });
            })
            ->orderBy('nama')
            ->get();

        return view('rumah_sakit.pages.jadwal-praktek', [
            'jadwal'        => $jadwal,
            'hariList'      => JadwalPraktekModel::$hari,
            'hariHariIni'   => self::HARI_MAP[now()->dayOfWeekIso],
            'spesialisList' => $spesialisList,
            'rsSlug'        => $rs->slug,
        ]);
    }
}
