<?php

namespace App\Filament\Resources\JadwalPraktekResource\Pages;

use App\Enums\Hari;
use App\Models\JadwalPraktek;
use App\Models\PoliKlinik;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class JadwalPraktekExcel extends JadwalPraktekPage
{
    protected static string $view = 'filament.resources.jadwal-praktek-resource.pages.jadwal-praktek-excel';

    protected static ?string $title = 'Jadwal Praktek (Excel)';

    protected static ?string $navigationLabel = 'Jadwal Praktek · Excel';

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 4;

    // =========================================================================
    // OVERRIDE — dispatch gridRows event setelah rows diperbarui
    // =========================================================================

    public function setActiveHari(string $hari): void
    {
        parent::setActiveHari($hari);
        $this->dispatch('gridRows', rows: $this->rows);
    }

    public function updatedSelectedRumahSakitId(): void
    {
        parent::updatedSelectedRumahSakitId();
        $this->dispatch('gridRows', rows: $this->rows);
    }

    public function updatedSelectedUnitLayananId(): void
    {
        parent::updatedSelectedUnitLayananId();
        $this->dispatch('gridRows', rows: $this->rows);
    }

    // =========================================================================
    // SAVE — menerima rows dari AG Grid
    // =========================================================================

    public function saveFromGrid(array $rows): void
    {
        $rsId = $this->getActiveRumahSakitId();
        if (! $rsId) {
            Notification::make()->title('Rumah sakit tidak teridentifikasi')->danger()->send();
            return;
        }

        foreach ($rows as $i => $row) {
            $nomor = $i + 1;
            if (empty($row['poliklinik_id'])) {
                Notification::make()
                    ->title("Baris ke-{$nomor} belum lengkap")
                    ->body('Pilih poliklinik atau hapus baris tersebut.')
                    ->warning()->send();
                return;
            }
        }

        $poliIds = PoliKlinik::whereHas('unitLayanan', function ($q) use ($rsId) {
            $q->where('rumah_sakit_id', $rsId);
            if ($this->selectedUnitLayananId) {
                $q->where('id', $this->selectedUnitLayananId);
            }
        })->pluck('id')->toArray();

        DB::transaction(function () use ($rows, $poliIds) {
            JadwalPraktek::where('hari', $this->activeHari)
                ->whereIn('poliklinik_id', $poliIds)
                ->delete();

            foreach ($rows as $row) {
                JadwalPraktek::create([
                    'poliklinik_id'     => $row['poliklinik_id'],
                    'hari'              => $this->activeHari,
                    'dokter_id'         => $row['dokter_id'] ?: null,
                    'nama_dokter'       => $row['nama_dokter'] ?: null,
                    'waktu_mulai'       => $row['waktu_mulai'] ?: null,
                    'waktu_selesai'     => $row['waktu_selesai'] ?: null,
                    'sesuai_perjanjian' => ! empty($row['sesuai_perjanjian']),
                    'catatan'           => $row['catatan'] ?: null,
                ]);
            }
        });

        $this->rowsCache[$this->activeHari] = null;
        $this->loadRows();
        $this->dispatch('gridRows', rows: $this->rows);

        Notification::make()
            ->title('Jadwal ' . Hari::from($this->activeHari)->getLabel() . ' berhasil disimpan')
            ->success()->send();
    }
}
