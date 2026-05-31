<?php

namespace App\Filament\Resources\JadwalLayananResource\Pages;

use App\Enums\Hari;
use App\Models\JadwalLayanan;
use App\Models\PoliKlinik;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class JadwalLayananExcel extends JadwalLayananPage
{
    protected static string $view = 'filament.resources.jadwal-layanan-resource.pages.jadwal-layanan-excel';

    protected static ?string $title = 'Jadwal Mingguan (Excel)';

    protected static ?string $navigationLabel = 'Jadwal Mingguan · Excel';

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 3;

    // =========================================================================
    // OVERRIDE NAVIGASI — dispatch gridRows event setelah rows diperbarui
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
    // SAVE — menerima rows dari AG Grid (bukan dari $this->rows)
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
            if (empty($row['jam_mulai'])) {
                Notification::make()->title("Baris ke-{$nomor}: Jam Mulai wajib diisi")->danger()->send();
                return;
            }
            if (empty($row['status_layanan'])) {
                Notification::make()->title("Baris ke-{$nomor}: Status wajib diisi")->danger()->send();
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
            JadwalLayanan::where('hari', $this->activeHari)
                ->whereIn('poliklinik_id', $poliIds)
                ->delete();

            foreach ($rows as $row) {
                JadwalLayanan::create([
                    'poliklinik_id'  => $row['poliklinik_id'],
                    'hari'           => $this->activeHari,
                    'dokter_id'      => $row['dokter_id'] ?: null,
                    'nama_dokter'    => $row['nama_dokter'] ?: null,
                    'jam_mulai'      => $row['jam_mulai'],
                    'jam_selesai'    => $row['jam_selesai'] ?: null,
                    'status_layanan' => $row['status_layanan'],
                    'catatan'        => $row['catatan'] ?: null,
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
